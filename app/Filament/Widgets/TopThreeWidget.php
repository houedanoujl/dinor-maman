<?php

namespace App\Filament\Widgets;

use App\Models\Participant;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TopThreeWidget extends BaseWidget
{
    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Top 3 des candidats';

    protected ?string $pollingInterval = '30s';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Participant::approved()
                    ->orderByDesc('vote_count')
                    ->orderByDesc('approved_at')
                    ->limit(3)
            )
            ->paginated(false)
            ->columns([
                Tables\Columns\TextColumn::make('rank')
                    ->label('#')
                    ->state(function ($record, $rowLoop) {
                        return ['🥇', '🥈', '🥉'][$rowLoop->index] ?? ($rowLoop->index + 1);
                    }),
                Tables\Columns\SpatieMediaLibraryImageColumn::make('photo')
                    ->label('Photo')
                    ->collection('photo')
                    ->conversion('thumb')
                    ->square()
                    ->size(70),
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Candidat')
                    ->state(fn ($record) => trim($record->first_name . ' ' . $record->last_name))
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('city')
                    ->label('Ville'),
                Tables\Columns\TextColumn::make('vote_count')
                    ->label('Votes')
                    ->badge()
                    ->color('warning')
                    ->sortable(),
            ]);
    }
}
