<?php

namespace App\Livewire;

use App\Models\Participant;
use App\Models\User;
use App\Notifications\ParticipationReceived;
use App\Services\SmsNotifier;
use App\Services\TwilioSms;
use App\Support\ContestSettings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class ContestForm extends Component
{
    use WithFileUploads;

    // Étapes : 'form' → 'verify' → 'done'
    public string $step = 'form';

    #[Validate('required|string|min:2|max:50')]
    public string $first_name = '';

    #[Validate('required|string|min:2|max:50')]
    public string $last_name = '';

    #[Validate('required|string|regex:/^(\+?\d{8,15})$/|unique:participants,phone')]
    public string $phone = '';

    #[Validate('required|string|min:2|max:100')]
    public string $city = '';

    #[Validate('nullable|email|max:150')]
    public string $email = '';

    #[Validate('required|image|mimes:jpeg,png,webp|max:5120')]
    public $photo;

    #[Validate('nullable|string|max:500')]
    public string $anecdote = '';

    public bool $submitted = false;

    #[Validate('accepted')]
    public bool $consent = false;

    public string $sms_code_input = '';
    public ?int $participant_id = null;
    public string $sms_error = '';
    public int $resend_cooldown = 0;

    protected function contestEnded(): bool
    {
        return ContestSettings::isEnded();
    }

    protected function uploadOpen(): bool
    {
        return ContestSettings::isUploadPhase();
    }

    public function mount()
    {
        if ($this->contestEnded()) {
            return redirect()->route('winners.index')
                ->with('status', 'Le concours est terminé. Découvrez les gagnants !');
        }
        if (! $this->uploadOpen()) {
            return redirect()->route('contest.gallery')
                ->with('status', "La phase d'upload est terminée. Vous pouvez continuer à voter !");
        }

        // Si user connecté : gates
        if (Auth::check()) {
            $u = Auth::user();

            // Admin n'utilise pas le formulaire concours
            if ($u->isAdmin()) {
                return redirect('/admin');
            }

            // User déjà participant → redirige dashboard
            $existing = Participant::where('user_id', $u->id)->first();
            if ($existing) {
                return redirect()->route('participant.dashboard', $existing->dashboard_token)
                    ->with('status', 'Vous avez déjà soumis une photo.');
            }

            // Pré-remplit avec données user (voter peut devenir participant)
            $names = explode(' ', $u->name, 2);
            $this->first_name = $names[0] ?? '';
            $this->last_name = $names[1] ?? '';
            $this->email = $u->email;
        }
    }

    public function submit(): void
    {
        if ($this->contestEnded()) {
            throw ValidationException::withMessages([
                'photo' => 'Le concours est terminé. Les participations sont clôturées.',
            ]);
        }

        if (! $this->uploadOpen()) {
            throw ValidationException::withMessages([
                'photo' => "La phase d'upload est terminée.",
            ]);
        }

        $this->ensureSubmissionIsAllowed();
        $this->validate();

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $participant = DB::transaction(function () use ($code) {
            $participant = Participant::create([
                'user_id'             => Auth::id(),
                'first_name'          => trim($this->first_name),
                'last_name'           => trim($this->last_name),
                'phone'               => trim($this->phone),
                'city'                => trim($this->city),
                'email'               => $this->email ? strtolower(trim($this->email)) : (Auth::user()?->email),
                'anecdote'            => $this->anecdote ? trim($this->anecdote) : null,
                'status'              => Participant::STATUS_PENDING,
                'sms_code'            => $code,
                'sms_code_expires_at' => now()->addMinutes(10),
            ]);

            // Si voter → upgrade en participant
            if (Auth::check() && Auth::user()->isVoter()) {
                Auth::user()->update(['role' => User::ROLE_PARTICIPANT]);
            }

            $participant->addMedia($this->photo->getRealPath())
                ->usingFileName(Str::uuid() . '.' . $this->safePhotoExtension())
                ->toMediaCollection('photo');

            return $participant;
        });

        $this->sendSmsCode($participant, $code);

        $this->participant_id = $participant->id;
        $this->step = 'verify';
        $this->reset(['first_name', 'last_name', 'phone', 'city', 'email', 'photo', 'anecdote', 'consent']);
    }

    public function verifyCode(): void
    {
        $this->sms_error = '';

        if (! $this->participant_id) {
            $this->sms_error = 'Session expirée. Recommencez.';
            return;
        }

        $participant = Participant::find($this->participant_id);

        if (! $participant) {
            $this->sms_error = 'Participation introuvable. Recommencez.';
            return;
        }

        if ($participant->phone_verified_at) {
            $this->finalizeParticipation($participant);
            return;
        }

        if (! $participant->sms_code_expires_at || now()->greaterThanOrEqualTo($participant->sms_code_expires_at)) {
            $this->sms_error = 'Code expiré. Cliquez sur "Renvoyer le code".';
            return;
        }

        if ($this->sms_code_input !== $participant->sms_code) {
            $this->sms_error = 'Code incorrect. Vérifiez votre SMS.';
            return;
        }

        $participant->update([
            'phone_verified_at'    => now(),
            'sms_code'             => null,
            'sms_code_expires_at'  => null,
        ]);

        $this->finalizeParticipation($participant);
    }

    public function resendCode(): void
    {
        if (! $this->participant_id) return;

        $rateLimitKey = 'sms-resend:' . $this->participant_id;
        if (RateLimiter::tooManyAttempts($rateLimitKey, 2)) {
            $this->sms_error = 'Trop de tentatives. Attendez quelques minutes.';
            return;
        }
        RateLimiter::hit($rateLimitKey, 300);

        $participant = Participant::find($this->participant_id);
        if (! $participant || $participant->phone_verified_at) return;

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $participant->update([
            'sms_code'            => $code,
            'sms_code_expires_at' => now()->addMinutes(10),
        ]);

        $this->sendSmsCode($participant, $code);
        $this->sms_error = '';
        $this->dispatch('toast', type: 'success', message: 'Code renvoyé par SMS.');
    }

    protected function sendSmsCode(Participant $participant, string $code): void
    {
        $message = "Votre code de validation : {$code}\nValable 10 minutes.\nConcours DINOR.";

        try {
            app(TwilioSms::class)->send($participant->phone, $message);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Twilio SMS failed', [
                'phone' => $participant->phone,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function finalizeParticipation(Participant $participant): void
    {
        if ($participant->email) {
            Notification::route('mail', $participant->email)
                ->notify(new ParticipationReceived($participant));
        }

        app(SmsNotifier::class)->send(
            $participant->phone,
            "Bonjour {$participant->first_name}, votre participation a bien ete recue et est en attente de validation."
        );

        session(['participant_token' => $participant->dashboard_token]);

        $this->step = 'done';
        $this->submitted = true;
    }

    public function render()
    {
        return view('livewire.contest-form');
    }

    protected function ensureSubmissionIsAllowed(): void
    {
        $ipKey    = 'contest-submit:ip:' . request()->ip();
        $phoneKey = 'contest-submit:phone:' . hash('sha256', trim($this->phone));

        if (RateLimiter::tooManyAttempts($ipKey, 5) || RateLimiter::tooManyAttempts($phoneKey, 3)) {
            throw ValidationException::withMessages([
                'phone' => 'Trop de tentatives. Veuillez reessayer plus tard.',
            ]);
        }

        RateLimiter::hit($ipKey, 60);
        RateLimiter::hit($phoneKey, 3600);
    }

    protected function safePhotoExtension(): string
    {
        $extension = strtolower((string) $this->photo->extension());
        return in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true) ? $extension : 'jpg';
    }
}
