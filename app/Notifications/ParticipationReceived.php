<?php

namespace App\Notifications;

use App\Models\Participant;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ParticipationReceived extends Notification
{
    use Queueable;

    public function __construct(public Participant $participant) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('un concept de cuisine avec maman - Participation recue')
            ->greeting("Bonjour {$this->participant->first_name},")
            ->line('Votre participation au jeu "un concept de cuisine avec maman" a bien ete enregistree.')
            ->line('Elle est actuellement en attente de validation par notre equipe.')
            ->salutation('A bientot sur un concept de cuisine avec maman !');
    }
}
