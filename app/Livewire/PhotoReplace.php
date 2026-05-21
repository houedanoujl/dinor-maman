<?php

namespace App\Livewire;

use App\Models\Participant;
use App\Support\ContestSettings;
use App\Support\ImageSanitizer;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class PhotoReplace extends Component
{
    use WithFileUploads;

    public Participant $participant;

    #[Validate('required|image|mimes:jpeg,jpg,png|mimetypes:image/jpeg,image/png|max:4096')]
    public $photo = null;

    public bool $editing = false;

    public function mount(Participant $participant): void
    {
        $this->participant = $participant;
    }

    protected function authorizeOwner(): void
    {
        $token = session('participant_token');
        if (! $token || $token !== $this->participant->dashboard_token) {
            abort(403);
        }
    }

    public function startEdit(): void
    {
        $this->authorizeOwner();
        $this->editing = true;
    }

    public function cancel(): void
    {
        $this->editing = false;
        $this->photo = null;
        $this->resetValidation();
    }

    public function save(): void
    {
        $this->authorizeOwner();

        if (! ContestSettings::isUploadPhase()) {
            throw ValidationException::withMessages([
                'photo' => "La phase d'upload est terminée. La modification de photo n'est plus possible.",
            ]);
        }

        $this->validate();

        // Supprime l'ancienne photo et ajoute la nouvelle
        $this->participant->clearMediaCollection('photo');

        $extension = ImageSanitizer::sanitize($this->photo->getRealPath());

        $this->participant->addMedia($this->photo->getRealPath())
            ->usingFileName(Str::uuid() . '.' . $extension)
            ->toMediaCollection('photo');

        // Si la photo avait été approuvée, on repasse en pending pour re-validation
        if ($this->participant->status === Participant::STATUS_APPROVED) {
            $this->participant->update([
                'status' => Participant::STATUS_PENDING,
                'approved_at' => null,
            ]);
        } elseif ($this->participant->status === Participant::STATUS_REJECTED) {
            $this->participant->update([
                'status' => Participant::STATUS_PENDING,
                'rejection_reason' => null,
            ]);
        }

        $this->participant->refresh();
        $this->editing = false;
        $this->photo = null;

        $this->dispatch('toast', type: 'success', message: 'Photo mise à jour. Elle est en attente de validation.');
    }

    public function render()
    {
        return view('livewire.photo-replace');
    }
}
