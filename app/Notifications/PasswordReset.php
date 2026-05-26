<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordReset extends Notification
{
    use Queueable;

    public function __construct(public string $plainPassword, public string $phone) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $loginUrl = route('login');

        return (new MailMessage)
            ->subject('Votre nouveau mot de passe - Un moment de cuisine avec maman')
            ->greeting('Bonjour,')
            ->line('Un nouveau mot de passe a ete genere pour votre compte.')
            ->line('Numero de telephone: ' . $this->phone)
            ->line('Nouveau mot de passe: ' . $this->plainPassword)
            ->action('Se connecter', $loginUrl)
            ->line('Pour des raisons de securite, ne partagez ce mot de passe avec personne.')
            ->line("Si vous n'avez pas demande cette reinitialisation, ignorez ce message — votre ancien mot de passe a ete invalide.")
            ->salutation("L'equipe Un moment de cuisine avec maman");
    }
}
