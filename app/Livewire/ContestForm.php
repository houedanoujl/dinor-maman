<?php

namespace App\Livewire;

use App\Models\Participant;
use App\Notifications\ParticipationReceived;
use Illuminate\Support\Facades\Notification;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

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

    #[Validate('required|image|mimes:jpeg,png,webp|max:5120')] // 5 Mo
    public $photo;

    public bool $submitted = false;

    public function submit(): void
    {
        $data = $this->validate();

        $participant = Participant::create([
            'first_name' => $this->first_name,
            'last_name'  => $this->last_name,
            'phone'      => $this->phone,
            'city'       => $this->city,
            'email'      => $this->email ?: null,
            'status'     => Participant::STATUS_PENDING,
        ]);

        $participant->addMedia($this->photo->getRealPath())
            ->usingFileName(uniqid('dinor_', true) . '.' . $this->photo->getClientOriginalExtension())
            ->toMediaCollection('photo');

        if ($participant->email) {
            Notification::route('mail', $participant->email)
                ->notify(new ParticipationReceived($participant));
        }

        $this->submitted = true;
        $this->reset(['first_name', 'last_name', 'phone', 'city', 'email', 'photo']);

        session()->flash('success', "Votre participation a bien été enregistrée et est en attente de validation par notre équipe.");
    }

    public function render()
    {
        return view('livewire.contest-form');
    }
}
