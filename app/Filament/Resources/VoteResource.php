<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VoteResource\Pages;
use App\Models\Vote;
use App\Models\Participant;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VoteResource extends Resource
{
    protected static ?string $model = Vote::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-heart';

    protected static ?string $navigationLabel = 'Logs Votes';

    protected static ?string $modelLabel = 'Vote';

    protected static ?string $pluralModelLabel = 'Logs Votes';

    protected static \UnitEnum|string|null $navigationGroup = 'Journaux';

    protected static ?int $navigationSort = 110;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->paginated([25, 50, 100, 250])
            ->defaultPaginationPageOption(50)
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Votant')
                    ->searchable()
                    ->placeholder('Anonyme'),
                Tables\Columns\TextColumn::make('user.phone')
                    ->label('Téléphone')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('participant.full_name')
                    ->label('Pour')
                    ->searchable(['first_name', 'last_name']),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP')
                    ->searchable()
                    ->copyable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('session_id')
                    ->label('Session')
                    ->limit(12)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('user_agent')
                    ->label('User-Agent')
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->user_agent)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                DeleteAction::make()
                    ->label('Supprimer')
                    ->modalHeading('Supprimer ce vote ?')
                    ->modalDescription('Action irréversible. Le compteur de votes du participant sera décrémenté.')
                    ->successNotificationTitle('Vote supprimé.'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Supprimer la sélection')
                        ->modalHeading('Supprimer les votes sélectionnés ?')
                        ->modalDescription('Action irréversible. Les compteurs de votes seront décrémentés.'),
                ]),
            ])
            ->filters([
                SelectFilter::make('participant_id')
                    ->label('Participant')
                    ->options(fn () => Participant::query()
                        ->orderBy('first_name')
                        ->get()
                        ->mapWithKeys(fn ($p) => [$p->id => $p->full_name])
                        ->toArray())
                    ->searchable(),
                Filter::make('ip_address')
                    ->schema([Forms\Components\TextInput::make('ip')->label('IP')])
                    ->query(fn (Builder $q, array $data) => $q->when(
                        $data['ip'] ?? null,
                        fn ($q, $v) => $q->where('ip_address', 'like', "%{$v}%")
                    )),
                Filter::make('phone')
                    ->schema([Forms\Components\TextInput::make('phone')->label('Téléphone votant')])
                    ->query(fn (Builder $q, array $data) => $q->when(
                        $data['phone'] ?? null,
                        fn ($q, $v) => $q->whereHas('user', fn ($q2) => $q2->where('phone', 'like', "%{$v}%"))
                    )),
                Filter::make('date')
                    ->schema([
                        Forms\Components\DatePicker::make('from')->label('Du'),
                        Forms\Components\DatePicker::make('until')->label('Au'),
                    ])
                    ->query(fn (Builder $q, array $data) => $q
                        ->when($data['from'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
                        ->when($data['until'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVotes::route('/'),
        ];
    }
}
