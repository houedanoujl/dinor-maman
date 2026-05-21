<?php

namespace App\Livewire\Auth;

use App\Models\Participant;
use App\Models\User;
use App\Models\SmsLog;
use App\Notifications\ParticipationReceived;
use App\Services\SmsDispatcher;
use App\Support\ContestSettings;
use App\Support\ImageSanitizer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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

    public bool $consent = false;

    public string $name = '';

    public string $first_name = '';
    public string $last_name = '';
    public string $phone = '';
    public string $commune = '';
    public string $quartier = '';
    public string $city = '';
    public $photo;
    public string $anecdote = '';

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
            $this->phone = $u->phone ?? '';
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
            $shared['phone'] = ['required', 'string', 'max:32'];
        }

        if ($this->role === User::ROLE_VOTER) {
            return array_merge($shared, [
                'name' => ['required', 'string', 'min:2', 'max:100'],
            ]);
        }

        return array_merge($shared, [
            'first_name' => ['required', 'string', 'min:2', 'max:50'],
            'last_name'  => ['required', 'string', 'min:2', 'max:50'],
            'commune'    => ['required', 'string', Rule::in(\App\Support\Abidjan::communes())],
            'quartier'   => ['required', 'string', 'max:100'],
            'photo'      => ['required', 'image', 'mimes:jpeg,jpg,png', 'mimetypes:image/jpeg,image/png', 'max:4096'],
            'anecdote'   => ['nullable', 'string', 'max:500'],
        ]);
    }

    public function submit()
    {
        if ($this->role === User::ROLE_PARTICIPANT) {
            $allowed = \App\Support\Abidjan::quartiers($this->commune);
            if (! in_array($this->quartier, $allowed, true)) {
                throw ValidationException::withMessages([
                    'quartier' => 'Sélectionnez un quartier valide pour cette commune.',
                ]);
            }
            $this->city = "{$this->commune} - {$this->quartier}";
        }

        $this->validate();

        if (! Auth::check()) {
            if (! User::isValidCiPhone($this->phone)) {
                throw ValidationException::withMessages([
                    'phone' => 'Numéro invalide. 10 chiffres requis (ex: 07 08 09 10 11).',
                ]);
            }

            $normalized = User::normalizePhone($this->phone);

            $existing = User::where('phone', $normalized)->first();
            if ($existing) {
                throw ValidationException::withMessages([
                    'phone' => 'Ce numéro est déjà inscrit. Connectez-vous.',
                ]);
            }

            $this->phone = $normalized;
        }

        if ($this->role === User::ROLE_VOTER) {
            return $this->submitVoter();
        }

        return $this->submitParticipant();
    }

    protected function generatePassword(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    protected function submitVoter()
    {
        $password = $this->generatePassword();

        $user = User::create([
            'name'     => trim($this->name),
            'phone'    => $this->phone,
            'email'    => null,
            'password' => Hash::make($password),
            'role'     => User::ROLE_VOTER,
        ]);

        $this->sendCredentialsSms($user, $password);

        Auth::login($user, true);

        return redirect()->route('contest.gallery')
            ->with('status', 'Bienvenue ! Votre mot de passe a été envoyé par SMS.');
    }

    protected function submitParticipant()
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

        $password = $this->generatePassword();

        $result = DB::transaction(function () use ($password) {
            $user = Auth::user();
            $generatedPwd = null;

            if (! $user) {
                $user = User::create([
                    'name'     => trim($this->first_name . ' ' . $this->last_name),
                    'phone'    => $this->phone,
                    'email'    => null,
                    'password' => Hash::make($password),
                    'role'     => User::ROLE_PARTICIPANT,
                ]);
                $generatedPwd = $password;
                Auth::login($user, true);
            } elseif ($user->isVoter()) {
                $user->update(['role' => User::ROLE_PARTICIPANT]);
            }

            $participant = Participant::create([
                'user_id'             => $user->id,
                'first_name'          => trim($this->first_name),
                'last_name'           => trim($this->last_name),
                'phone'               => $user->phone,
                'city'                => trim($this->city),
                'email'               => null,
                'anecdote'            => $this->anecdote ? trim($this->anecdote) : null,
                'status'              => Participant::STATUS_PENDING,
                'phone_verified_at'   => now(),
            ]);

            $extension = ImageSanitizer::sanitize($this->photo->getRealPath());
            $participant->addMedia($this->photo->getRealPath())
                ->usingFileName(Str::uuid() . '.' . $extension)
                ->toMediaCollection('photo');

            return ['user' => $user, 'participant' => $participant, 'password' => $generatedPwd];
        });

        if ($result['password']) {
            $this->sendCredentialsSms($result['user'], $result['password']);
        }

        $this->finalizeParticipation($result['participant']);
    }

    protected function sendCredentialsSms(User $user, string $password): void
    {
        $loginUrl = route('login');
        $message = "Bienvenue sur DINOR. Votre mot de passe: {$password}. Connectez-vous avec votre numero {$user->phone} sur {$loginUrl}. Conservez ce SMS, il ne sera pas renvoye.";

        app(SmsDispatcher::class)->sendOnce(
            $user->phone,
            SmsLog::TYPE_CREDENTIALS,
            $message
        );
    }

    protected function finalizeParticipation(Participant $participant): void
    {
        if ($participant->email) {
            try {
                Notification::route('mail', $participant->email)
                    ->notify(new ParticipationReceived($participant));
            } catch (\Throwable $e) {
                Log::warning('Mail notif failed', ['err' => $e->getMessage()]);
            }
        }

        session(['participant_token' => $participant->dashboard_token]);
        $this->step = 'done';
        $this->reset(['photo', 'anecdote']);
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
            'uploadOpen'      => ContestSettings::isUploadPhase(),
            'contestEnded'    => ContestSettings::isEnded(),
            'isAuthed'        => Auth::check(),
            'communes'        => \App\Support\Abidjan::communes(),
            'quartiersMap'    => \App\Support\Abidjan::quartiersByCommune(),
        ]);
    }
}
