<?php

namespace App\Notifications;

use App\Models\Participant;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContestEndingSoon extends Notification
{
    use Queueable;

    public function __construct(public Participant $participant, public int $daysLeft) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $shareUrl = route('participant.show', $this->participant) . '?ref=' . $this->participant->id;

        return (new MailMessage)
            ->subject("Rappel concours: plus que {$this->daysLeft} jour(s)")
            ->greeting("Bonjour {$this->participant->first_name},")
            ->line("Le concours se termine dans {$this->daysLeft} jour(s).")
            ->line('C\'est le moment de partager votre lien pour obtenir plus de votes.')
            ->action('Partager mon profil', $shareUrl)
            ->salutation('L\'equipe Un moment de cuisine avec maman');
    }
}
