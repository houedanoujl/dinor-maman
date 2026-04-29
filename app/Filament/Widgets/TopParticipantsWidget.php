<?php

namespace App\Filament\Widgets;

use App\Models\Participant;
use App\Models\Vote;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class TopParticipantsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $today = now()->startOfDay();
        $participantsToday = Participant::where('created_at', '>=', $today)->count();
        $votesToday = Vote::where('created_at', '>=', $today)->count();

        $total = Participant::count();
        $approved = Participant::approved()->count();
        $validationRate = $total > 0 ? round(($approved / $total) * 100, 1) : 0;

        $topCities = Participant::query()
            ->select('city', DB::raw('COUNT(*) as total'))
            ->groupBy('city')
            ->orderByDesc('total')
            ->limit(3)
            ->pluck('total', 'city')
            ->map(fn ($count, $city) => $city . ': ' . $count)
            ->implode(' | ');

        return [
            Stat::make('Participations/jour', (string) $participantsToday)
                ->description('Nouvelles soumissions aujourd\'hui')
                ->color('primary'),
            Stat::make('Votes/jour', (string) $votesToday)
                ->description('Votes enregistres aujourd\'hui')
                ->color('success'),
            Stat::make('Taux de validation', $validationRate . '%')
                ->description($approved . ' approuves / ' . $total . ' total')
                ->color('warning'),
            Stat::make('Top villes', $topCities ?: 'Aucune donnee')
                ->description('Villes les plus actives')
                ->color('gray'),
        ];
    }
}
