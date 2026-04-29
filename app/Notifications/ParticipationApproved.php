<?php

namespace App\Notifications;

use App\Models\Participant;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ParticipationApproved extends Notification
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
            ->subject('Felicitations, votre photo est en ligne !')
            ->greeting("Bravo {$this->participant->first_name} !")
            ->line('Votre photo vient d\'etre validee et apparait dans la galerie publique.')
            ->line('Partagez l\'application avec vos proches pour recolter un maximum de votes.')
            ->action('Voir ma photo', url('/galerie'))
            ->salutation('L\'equipe un concept de cuisine avec maman');
    }
}
