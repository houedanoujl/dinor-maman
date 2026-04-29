<?php

namespace App\Livewire;

use App\Models\Participant;
use App\Notifications\ParticipationReceived;
use App\Services\SmsNotifier;
use App\Support\ContestSettings;
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

    public bool $submitted = false;

    #[Validate('accepted')]
    public bool $consent = false;

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
                'photo' => "La phase d'upload est terminée. Les nouvelles participations ne sont plus acceptées.",
            ]);
        }

        $this->ensureSubmissionIsAllowed();

        $this->validate();

        $participant = DB::transaction(function () {
            $participant = Participant::create([
                'first_name' => trim($this->first_name),
                'last_name'  => trim($this->last_name),
                'phone'      => trim($this->phone),
                'city'       => trim($this->city),
                'email'      => $this->email ? strtolower(trim($this->email)) : null,
                'status'     => Participant::STATUS_PENDING,
            ]);

            $participant->addMedia($this->photo->getRealPath())
                ->usingFileName(Str::uuid() . '.' . $this->safePhotoExtension())
                ->toMediaCollection('photo');

            return $participant;
        });

        if ($participant->email) {
            Notification::route('mail', $participant->email)
                ->notify(new ParticipationReceived($participant));
        }

        app(SmsNotifier::class)->send(
            $participant->phone,
            "Bonjour {$participant->first_name}, votre participation a bien ete recue et est en attente de validation."
        );

        // Connecte le participant via session pour qu'il accède à son espace
        session(['participant_token' => $participant->dashboard_token]);

        $this->submitted = true;
        $this->reset(['first_name', 'last_name', 'phone', 'city', 'email', 'photo', 'consent']);
    }

    public function render()
    {
        return view('livewire.contest-form');
    }

    protected function ensureSubmissionIsAllowed(): void
    {
        $ipKey = 'contest-submit:ip:' . request()->ip();
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
