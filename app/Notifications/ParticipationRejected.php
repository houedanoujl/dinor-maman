<?php

namespace App\Notifications;

use App\Models\Participant;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ParticipationRejected extends Notification
{
    use Queueable;

    public function __construct(public Participant $participant) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Un moment de cuisine avec maman — Participation non retenue')
            ->greeting("Bonjour {$this->participant->first_name},")
            ->line('Nous avons examiné votre participation au concours « Un moment de cuisine avec maman ».')
            ->line('Malheureusement, elle n\'a pas pu être retenue pour la raison suivante :');

        if ($this->participant->rejection_reason) {
            $message->line('**' . $this->participant->rejection_reason . '**');
        }

        return $message
            ->line('Vous pouvez soumettre une nouvelle photo en vous assurant que :')
            ->line('• Vous et votre maman êtes clairement visibles')
            ->line('• La photo se passe dans un contexte de cuisine')
            ->line('• L\'image est nette et de bonne qualité')
            ->line('• Le contenu est approprié')
            ->action('Soumettre une nouvelle participation', route('contest.form'))
            ->salutation('L\'équipe Un moment de cuisine avec maman');
    }
}
