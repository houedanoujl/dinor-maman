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
            ->subject('Dinor — Participation reçue')
            ->greeting("Bonjour {$this->participant->first_name},")
            ->line('Votre participation au jeu "Un moment de cuisine avec maman" a bien été enregistrée.')
            ->line('Elle est actuellement en attente de validation par notre équipe.')
            ->salutation('À bientôt sur Dinor !');
    }
}
