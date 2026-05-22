<?php

namespace App\Livewire;

use App\Models\Participant;
use App\Models\User;
use App\Services\SmsNotifier;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.app')]
class ParticipantLogin extends Component
{
    #[Validate('required|string|min:6|max:20')]
    public string $phone = '';

    public bool $sent = false;

    public string $maskedPhone = '';

    public function submit(): void
    {
        $this->validate();

        $key = 'participant-login:' . request()->ip();
        if (RateLimiter::tooManyAttempts($key, 10)) {
            throw ValidationException::withMessages([
                'phone' => 'Trop de tentatives. Réessayez dans une minute.',
            ]);
        }
        RateLimiter::hit($key, 60);

        if (! User::isValidCiPhone($this->phone)) {
            $this->sent = true;
            $this->maskedPhone = $this->mask($this->phone);
            return;
        }

        $normalized = User::normalizePhone($this->phone);

        $participant = Participant::where('phone', $normalized)->first();

        // Réponse identique que le numéro existe ou non (anti-énumération)
        $this->sent = true;
        $this->maskedPhone = $this->mask($normalized);

        if (! $participant) {
            return;
        }

        // Rotation: génère un nouveau token plaintext à chaque demande de lien.
        // L'ancien lien partagé devient invalide.
        $plainToken = $participant->regenerateDashboardToken();

        $url = route('participant.dashboard', $plainToken);

        // Envoi SMS du lien
        try {
            app(SmsNotifier::class)->send(
                $participant->phone,
                "Votre lien de connexion: {$url} (valable tant que le concours est ouvert)."
            );
        } catch (\Throwable $e) {
            // silencieux
        }

        // Envoi e-mail si dispo
        if ($participant->email) {
            try {
                Notification::route('mail', $participant->email)
                    ->notify(new \App\Notifications\LoginLink($participant));
            } catch (\Throwable $e) {
                // silencieux
            }
        }
    }

    protected function mask(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);
        if (strlen($digits) <= 4) {
            return $phone;
        }
        return substr($phone, 0, 3) . str_repeat('•', max(0, strlen($phone) - 5)) . substr($phone, -2);
    }

    public function render()
    {
        return view('livewire.participant-login');
    }
}
