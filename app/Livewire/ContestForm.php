<?php

namespace App\Livewire;

use App\Models\Participant;
use App\Notifications\ParticipationReceived;
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

    public function submit(): void
    {
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

        $this->submitted = true;
        $this->reset(['first_name', 'last_name', 'phone', 'city', 'email', 'photo']);

        session()->flash('success', 'Votre participation a bien ete enregistree et est en attente de validation par notre equipe.');
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
