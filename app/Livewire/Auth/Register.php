<?php

namespace App\Livewire\Auth;

use App\Models\Participant;
use App\Models\User;
use App\Notifications\ParticipationReceived;
use App\Services\SmsNotifier;
use App\Services\TwilioSms;
use App\Support\ContestSettings;
use App\Support\ImageSanitizer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class Register extends Component
{
    use WithFileUploads;

    public string $step = 'form';

    public string $role = User::ROLE_VOTER;

    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $consent = false;

    public string $name = '';

    public string $first_name = '';
    public string $last_name = '';
    public string $phone = '';
    public string $city = '';
    public $photo;
    public string $anecdote = '';

    public string $sms_code_input = '';
    public ?int $participant_id = null;
    public string $sms_error = '';

    public function mount()
    {
        if (request()->query('role') === User::ROLE_PARTICIPANT) {
            $this->role = User::ROLE_PARTICIPANT;
        }

        if (Auth::check()) {
            $u = Auth::user();

            if ($u->isAdmin()) {
                return redirect('/admin');
            }

            $existing = Participant::where('user_id', $u->id)->first();
            if ($existing) {
                return redirect()->route('participant.dashboard', $existing->dashboard_token);
            }

            $this->role = User::ROLE_PARTICIPANT;
            $this->email = $u->email;
            $names = explode(' ', $u->name, 2);
            $this->first_name = $names[0] ?? '';
            $this->last_name = $names[1] ?? '';
        }
    }

    protected function rules(): array
    {
        $shared = [
            'role'    => ['required', Rule::in([User::ROLE_VOTER, User::ROLE_PARTICIPANT])],
            'consent' => ['accepted'],
        ];

        if (! Auth::check()) {
            $shared['email']    = ['required', 'email', 'max:150', 'unique:users,email'];
            $shared['password'] = ['required', 'string', 'min:8', 'max:100', 'confirmed'];
        }

        if ($this->role === User::ROLE_VOTER) {
            return array_merge($shared, [
                'name' => ['required', 'string', 'min:2', 'max:100'],
            ]);
        }

        return array_merge($shared, [
            'first_name' => ['required', 'string', 'min:2', 'max:50'],
            'last_name'  => ['required', 'string', 'min:2', 'max:50'],
            'phone'      => ['required', 'string', 'regex:/^(\+?\d{8,15})$/', 'unique:participants,phone'],
            'city'       => ['required', 'string', 'min:2', 'max:100'],
            'photo'      => ['required', 'image', 'mimes:jpeg,jpg,png', 'mimetypes:image/jpeg,image/png', 'max:4096'],
            'anecdote'   => ['nullable', 'string', 'max:500'],
        ]);
    }

    public function submit()
    {
        $this->validate();

        if ($this->role === User::ROLE_VOTER) {
            return $this->submitVoter();
        }

        return $this->submitParticipant();
    }

    protected function submitVoter()
    {
        $user = User::create([
            'name'     => trim($this->name),
            'email'    => strtolower(trim($this->email)),
            'password' => Hash::make($this->password),
            'role'     => User::ROLE_VOTER,
        ]);

        Auth::login($user, true);

        return redirect()->route('contest.gallery')
            ->with('status', 'Bienvenue ! Vous pouvez voter pour vos favoris.');
    }

    protected function submitParticipant(): void
    {
        if (ContestSettings::isEnded()) {
            throw ValidationException::withMessages([
                'photo' => 'Le concours est terminé. Les participations sont clôturées.',
            ]);
        }

        if (! ContestSettings::isUploadPhase()) {
            throw ValidationException::withMessages([
                'photo' => "La phase d'upload est terminée.",
            ]);
        }

        $this->ensureSubmissionIsAllowed();

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $participant = DB::transaction(function () use ($code) {
            $user = Auth::user();

            if (! $user) {
                $user = User::create([
                    'name'     => trim($this->first_name . ' ' . $this->last_name),
                    'email'    => strtolower(trim($this->email)),
                    'password' => Hash::make($this->password),
                    'role'     => User::ROLE_PARTICIPANT,
                ]);
                Auth::login($user, true);
            } elseif ($user->isVoter()) {
                $user->update(['role' => User::ROLE_PARTICIPANT]);
            }

            $participant = Participant::create([
                'user_id'             => $user->id,
                'first_name'          => trim($this->first_name),
                'last_name'           => trim($this->last_name),
                'phone'               => trim($this->phone),
                'city'                => trim($this->city),
                'email'               => $user->email,
                'anecdote'            => $this->anecdote ? trim($this->anecdote) : null,
                'status'              => Participant::STATUS_PENDING,
                'sms_code'            => $code,
                'sms_code_expires_at' => now()->addMinutes(10),
            ]);

            $extension = ImageSanitizer::sanitize($this->photo->getRealPath());
            $participant->addMedia($this->photo->getRealPath())
                ->usingFileName(Str::uuid() . '.' . $extension)
                ->toMediaCollection('photo');

            return $participant;
        });

        $this->sendSmsCode($participant, $code);

        $this->participant_id = $participant->id;
        $this->step = 'verify';
        $this->reset(['photo', 'anecdote']);
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
            'phone_verified_at'   => now(),
            'sms_code'            => null,
            'sms_code_expires_at' => null,
        ]);

        $this->finalizeParticipation($participant);
    }

    public function resendCode(): void
    {
        if (! $this->participant_id) {
            return;
        }

        $key = 'sms-resend:' . $this->participant_id;
        if (RateLimiter::tooManyAttempts($key, 2)) {
            $this->sms_error = 'Trop de tentatives. Attendez quelques minutes.';
            return;
        }
        RateLimiter::hit($key, 300);

        $participant = Participant::find($this->participant_id);
        if (! $participant || $participant->phone_verified_at) {
            return;
        }

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

    public function render()
    {
        return view('livewire.auth.register', [
            'uploadOpen'   => ContestSettings::isUploadPhase(),
            'contestEnded' => ContestSettings::isEnded(),
            'isAuthed'     => Auth::check(),
        ]);
    }
}
