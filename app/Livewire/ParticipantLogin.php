<?php

namespace App\Livewire;

use App\Models\Participant;
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

        $normalized = preg_replace('/\s+/', '', trim($this->phone));

        $participant = Participant::query()
            ->where('phone', $normalized)
            ->orWhere('phone', ltrim($normalized, '+'))
            ->orWhere('phone', '+' . ltrim($normalized, '+'))
            ->first();

        // Réponse identique que le numéro existe ou non (anti-énumération)
        $this->sent = true;
        $this->maskedPhone = $this->mask($normalized);

        if (! $participant || ! $participant->dashboard_token) {
            return;
        }

        $url = route('participant.dashboard', $participant->dashboard_token);

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
