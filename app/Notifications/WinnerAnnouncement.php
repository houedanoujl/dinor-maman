<?php

namespace App\Notifications;

use App\Models\Participant;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WinnerAnnouncement extends Notification
{
    use Queueable;

    public function __construct(public Participant $participant, public int $rank) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Resultats du concours: vous faites partie du Top 3')
            ->greeting("Felicitations {$this->participant->first_name} !")
            ->line("Votre participation a termine #{$this->rank} du concours Un moment de cuisine avec maman.")
            ->action('Voir les gagnants', route('winners.index'))
            ->salutation('L\'equipe Un moment de cuisine avec maman');
    }
}
