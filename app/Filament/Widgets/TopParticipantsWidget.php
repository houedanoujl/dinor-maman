<?php

namespace App\Filament\Widgets;

use App\Models\Participant;
use App\Models\Vote;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TopParticipantsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = '15s';

    protected function getStats(): array
    {
        $top = Participant::approved()
            ->orderByDesc('vote_count')
            ->take(3)
            ->get();

        $stats = [
            Stat::make('Participants', Participant::count()),
            Stat::make('En attente', Participant::pending()->count())->color('warning'),
            Stat::make('Total des votes', Vote::count())->color('success'),
        ];

        $medals = ['🥇', '🥈', '🥉'];
        foreach ($top as $i => $p) {
            $stats[] = Stat::make("{$medals[$i]} {$p->full_name}", $p->vote_count . ' votes')
                ->description($p->city)
                ->color('warning');
        }

        return $stats;
    }
}
