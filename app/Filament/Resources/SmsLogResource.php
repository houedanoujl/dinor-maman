<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SmsLogResource\Pages;
use App\Models\SmsLog;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Forms;
use Illuminate\Database\Eloquent\Builder;

class SmsLogResource extends Resource
{
    protected static ?string $model = SmsLog::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationLabel = 'Logs SMS';

    protected static ?string $modelLabel = 'Log SMS';

    protected static ?string $pluralModelLabel = 'Logs SMS';

    protected static \UnitEnum|string|null $navigationGroup = 'Journaux';

    protected static ?int $navigationSort = 100;

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
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Téléphone')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        SmsLog::TYPE_CREDENTIALS => 'info',
                        SmsLog::TYPE_APPROVAL    => 'success',
                        SmsLog::TYPE_REJECTION   => 'danger',
                        default                  => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        SmsLog::TYPE_CREDENTIALS => 'Mot de passe',
                        SmsLog::TYPE_APPROVAL    => 'Approbation',
                        SmsLog::TYPE_REJECTION   => 'Rejet',
                        default                  => $state,
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        SmsLog::STATUS_SENT    => 'success',
                        SmsLog::STATUS_FAILED  => 'danger',
                        SmsLog::STATUS_SKIPPED => 'gray',
                        default                => 'gray',
                    }),
                Tables\Columns\TextColumn::make('provider')
                    ->label('Provider')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('sent_at')
                    ->label('Envoyé le')
                    ->dateTime('d/m/Y H:i:s')
                    ->placeholder('—')
                    ->sortable(),
                Tables\Columns\TextColumn::make('error')
                    ->label('Erreur')
                    ->limit(60)
                    ->tooltip(fn ($record) => $record->error)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        SmsLog::TYPE_CREDENTIALS => 'Mot de passe',
                        SmsLog::TYPE_APPROVAL    => 'Approbation',
                        SmsLog::TYPE_REJECTION   => 'Rejet',
                    ]),
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        SmsLog::STATUS_SENT    => 'Envoyé',
                        SmsLog::STATUS_FAILED  => 'Échoué',
                        SmsLog::STATUS_SKIPPED => 'Ignoré',
                    ]),
                Filter::make('phone')
                    ->schema([Forms\Components\TextInput::make('phone')->label('Téléphone')])
                    ->query(fn (Builder $q, array $data) => $q->when(
                        $data['phone'] ?? null,
                        fn ($q, $v) => $q->where('phone', 'like', "%{$v}%")
                    )),
                Filter::make('date')
                    ->schema([
                        Forms\Components\DatePicker::make('from')->label('Du'),
                        Forms\Components\DatePicker::make('until')->label('Au'),
                    ])
                    ->query(fn (Builder $q, array $data) => $q
                        ->when($data['from'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
                        ->when($data['until'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))),
            ])
            ->recordActions([
                ViewAction::make()
                    ->modalHeading('Détail du SMS'),
            ]);
    }

    public static function infolist(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema->components([
            \Filament\Infolists\Components\TextEntry::make('phone')->label('Téléphone'),
            \Filament\Infolists\Components\TextEntry::make('type')->label('Type'),
            \Filament\Infolists\Components\TextEntry::make('status')->label('Statut'),
            \Filament\Infolists\Components\TextEntry::make('sent_at')->label('Envoyé le')->dateTime('d/m/Y H:i:s'),
            \Filament\Infolists\Components\TextEntry::make('message')->label('Contenu')->columnSpanFull(),
            \Filament\Infolists\Components\TextEntry::make('error')->label('Erreur')->columnSpanFull()->placeholder('—'),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSmsLogs::route('/'),
        ];
    }
}
