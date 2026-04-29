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
        $shareUrl = route('participant.show', $this->participant) . '?ref=' . $this->participant->id;
        $dashboardUrl = $this->participant->dashboard_url;

        $message = (new MailMessage)
            ->subject('Felicitations, votre photo est en ligne !')
            ->greeting("Bravo {$this->participant->first_name} !")
            ->line('Votre photo vient d\'etre validee et apparait maintenant dans la galerie publique du concours Un moment de cuisine avec maman.')
            ->line('Partagez ce lien a vos proches pour recolter un maximum de votes:')
            ->line($shareUrl)
            ->action('Voir ma photo et partager', $shareUrl);

        if ($dashboardUrl) {
            $message->line('Suivez vos votes en temps reel et votre classement depuis votre espace personnel:')
                ->line($dashboardUrl);
        }

        return $message
            ->line('Astuce: vous pouvez aussi partager ce lien sur WhatsApp ou Facebook.')
            ->salutation('L\'equipe Un moment de cuisine avec maman');
    }
}
