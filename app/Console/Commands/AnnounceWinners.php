<?php

namespace App\Console\Commands;

use App\Models\Participant;
use App\Models\Winner;
use App\Notifications\WinnerAnnouncement;
use App\Services\SmsNotifier;
use App\Support\ContestSettings;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class AnnounceWinners extends Command
{
    protected $signature = 'contest:announce-winners';

    protected $description = 'Freeze top 3 winners and notify them';

    public function handle(): int
    {
        if (now()->lessThanOrEqualTo(ContestSettings::endsAt())) {
            $this->warn('Contest is not finished yet.');
            return self::FAILURE;
        }

        // Cycle basé sur le mois de fin du concours (et non le mois courant)
        $cycle = ContestSettings::endsAt()->format('Y-m');
        if (Winner::where('contest_cycle', $cycle)->exists()) {
            $this->info('Winners already announced for this cycle.');
            return self::SUCCESS;
        }

        $top = Participant::approved()
            ->orderByDesc('vote_count')
            ->orderByDesc('approved_at')
            ->take(3)
            ->get();

        $sms = app(SmsNotifier::class);

        foreach ($top as $index => $participant) {
            $rank = $index + 1;

            Winner::create([
                'participant_id' => $participant->id,
                'rank' => $rank,
                'vote_count_snapshot' => $participant->vote_count,
                'announced_at' => now(),
                'contest_cycle' => $cycle,
            ]);

            if ($participant->email) {
                Notification::route('mail', $participant->email)
                    ->notify(new WinnerAnnouncement($participant, $rank));
            }

            $sms->send(
                $participant->phone,
                "Bravo ! Vous etes #{$rank} du concours. Consultez les resultats sur le site."
            );
        }

        $this->info('Winners announced.');

        return self::SUCCESS;
    }
}
