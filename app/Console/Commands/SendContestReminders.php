<?php

namespace App\Console\Commands;

use App\Models\Participant;
use App\Notifications\ContestEndingSoon;
use App\Services\SmsNotifier;
use App\Support\ContestSettings;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;

class SendContestReminders extends Command
{
    protected $signature = 'contest:send-reminders';

    protected $description = 'Send J-3 and J-1 reminders before contest end';

    public function handle(): int
    {
        $endsAt = ContestSettings::endsAt()->endOfDay();
        $daysLeft = now()->startOfDay()->diffInDays($endsAt, false);

        if (! in_array($daysLeft, (array) config('contest.reminder_days', [3, 1]), true)) {
            $this->info('No reminders to send today.');
            return self::SUCCESS;
        }

        $participants = Participant::approved()->get();
        $sms = app(SmsNotifier::class);

        foreach ($participants as $participant) {
            if ($participant->email) {
                Notification::route('mail', $participant->email)
                    ->notify(new ContestEndingSoon($participant, $daysLeft));
            }

            $sms->send(
                $participant->phone,
                "Rappel: plus que {$daysLeft} jour(s) avant la fin du concours. Partagez vite: " . route('participant.show', $participant) . '?ref=' . $participant->id
            );
        }

        $this->info("Reminders sent for J-{$daysLeft}.");

        return self::SUCCESS;
    }
}
