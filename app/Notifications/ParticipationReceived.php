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
            ->subject('Un moment de cuisine avec maman — Participation reçue')
            ->greeting("Bonjour {$this->participant->first_name},")
            ->line('Votre participation au concours **Un moment de cuisine avec maman** a bien été enregistrée.')
            ->line('Elle est actuellement en attente de validation par notre équipe. Vous recevrez un email dès qu\'elle sera approuvée.')
            ->line('En attendant, n\'hésitez pas à prévenir vos proches pour qu\'ils soient prêts à voter dès la publication !')
            ->action('Voir la galerie', route('contest.gallery'))
            ->salutation('À bientôt sur Un moment de cuisine avec maman !');
    }
}
