<?php

namespace App\Console\Commands;

use App\Models\Participant;
use App\Models\Winner;
use App\Support\ContestSettings;
use Illuminate\Console\Command;

class AnnounceWinners extends Command
{
    protected $signature = 'contest:announce-winners {--force : Force annonce même avant la fin du concours}';

    protected $description = 'Fige le top 3 des participants dans la table winners (snapshot). Aucune notification envoyée — annonce externe par DINOR sur ses canaux officiels.';

    public function handle(): int
    {
        if (! $this->option('force') && now()->lessThanOrEqualTo(ContestSettings::endsAt())) {
            $this->warn('Le concours n\'est pas encore terminé. Utilisez --force pour ignorer.');
            return self::FAILURE;
        }

        $cycle = ContestSettings::endsAt()->format('Y-m');

        if (Winner::where('contest_cycle', $cycle)->exists()) {
            $this->info('Gagnants déjà figés pour le cycle ' . $cycle . '.');
            return self::SUCCESS;
        }

        $top = Participant::approved()
            ->orderByDesc('vote_count')
            ->orderBy('created_at', 'asc')
            ->take(3)
            ->get();

        if ($top->isEmpty()) {
            $this->warn('Aucun participant approuvé.');
            return self::FAILURE;
        }

        foreach ($top as $index => $participant) {
            $rank = $index + 1;

            Winner::create([
                'participant_id' => $participant->id,
                'rank' => $rank,
                'vote_count_snapshot' => $participant->vote_count,
                'announced_at' => now(),
                'contest_cycle' => $cycle,
            ]);

            $this->info("#{$rank} — {$participant->full_name} ({$participant->vote_count} votes)");
        }

        $this->info('Top 3 figé. L\'annonce officielle se fait sur les plateformes DINOR (cf règlement).');

        return self::SUCCESS;
    }
}
