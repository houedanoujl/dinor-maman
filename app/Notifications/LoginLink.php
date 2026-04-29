<?php

namespace App\Notifications;

use App\Models\Participant;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoginLink extends Notification
{
    use Queueable;

    public function __construct(public Participant $participant)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = $this->participant->dashboard_url;

        return (new MailMessage)
            ->subject('Votre lien de connexion - Un moment de cuisine avec maman')
            ->greeting('Bonjour ' . $this->participant->first_name . ',')
            ->line('Voici votre lien de connexion personnel pour accéder à votre espace participant.')
            ->action('Accéder à mon espace', $url)
            ->line('Conservez précieusement ce lien : il vous permet de suivre votre participation à tout moment.')
            ->line("Si vous n'êtes pas à l'origine de cette demande, vous pouvez ignorer ce message.");
    }
}
