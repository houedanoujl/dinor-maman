<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ParticipantResource\Pages;
use App\Models\Participant;
use App\Notifications\ParticipationApproved;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Notification;

class ParticipantResource extends Resource
{
    protected static ?string $model = Participant::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = 'Participants';

    protected static ?string $modelLabel = 'Participant';

    public static function getNavigationBadge(): ?string
    {
        return (string) Participant::pending()->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informations')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('first_name')->required()->label('Prénom'),
                    Forms\Components\TextInput::make('last_name')->required()->label('Nom'),
                    Forms\Components\TextInput::make('phone')->tel()->required()->label('Téléphone'),
                    Forms\Components\TextInput::make('city')->required()->label('Ville'),
                    Forms\Components\TextInput::make('email')->email()->label('Email'),
                    Forms\Components\Select::make('status')
                        ->label('Statut')
                        ->options([
                            'pending'  => 'En attente',
                            'approved' => 'Approuvé',
                            'rejected' => 'Rejeté',
                        ])
                        ->required(),
                ]),
            Forms\Components\SpatieMediaLibraryFileUpload::make('photo')
                ->collection('photo')
                ->image()
                ->imageEditor()
                ->label('Photo'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('vote_count', 'desc')
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('photo')
                    ->collection('photo')
                    ->conversion('thumb')
                    ->square()
                    ->size(60),
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Nom')
                    ->searchable(['first_name', 'last_name']),
                Tables\Columns\TextColumn::make('city')
                    ->label('Ville')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Téléphone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('vote_count')
                    ->label('Votes')
                    ->sortable()
                    ->badge()
                    ->color('warning'),
                Tables\Columns\ToggleColumn::make('approved_toggle')
                    ->label('Approuvé')
                    ->getStateUsing(fn ($record) => $record->status === Participant::STATUS_APPROVED)
                    ->updateStateUsing(function ($record, $state) {
                        $newStatus = $state ? Participant::STATUS_APPROVED : Participant::STATUS_PENDING;
                        $record->update([
                            'status' => $newStatus,
                            'approved_at' => $state ? now() : null,
                        ]);

                        if ($state && $record->email) {
                            Notification::route('mail', $record->email)
                                ->notify(new ParticipationApproved($record));
                        }

                        FilamentNotification::make()
                            ->success()
                            ->title($state ? 'Participant approuvé' : 'Approbation retirée')
                            ->send();
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Soumis le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'pending'  => 'En attente',
                        'approved' => 'Approuvé',
                        'rejected' => 'Rejeté',
                    ]),
                SelectFilter::make('city')
                    ->label('Ville')
                    ->options(fn () => Participant::query()->distinct()->orderBy('city')->pluck('city', 'city')->toArray())
                    ->searchable(),
            ])
            ->recordActions([
                Actions\EditAction::make(),
                Actions\Action::make('reject')
                    ->label('Rejeter')
                    ->color('danger')
                    ->icon('heroicon-o-x-mark')
                    ->visible(fn ($record) => $record->status !== 'rejected')
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->update(['status' => 'rejected'])),
            ])
            ->headerActions([
                Actions\Action::make('exportRanking')
                    ->label('Exporter le classement (CSV)')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function () {
                        $filename = 'classement_dinor_' . now()->format('Y-m-d_His') . '.csv';

                        return response()->streamDownload(function () {
                            $out = fopen('php://output', 'w');
                            fputcsv($out, ['Rang', 'Prénom', 'Nom', 'Téléphone', 'Ville', 'Email', 'Votes']);

                            Participant::approved()
                                ->orderByDesc('vote_count')
                                ->get()
                                ->each(function ($p, $i) use ($out) {
                                    fputcsv($out, [
                                        $i + 1,
                                        self::csvCell($p->first_name),
                                        self::csvCell($p->last_name),
                                        self::csvCell($p->phone),
                                        self::csvCell($p->city),
                                        self::csvCell($p->email),
                                        $p->vote_count,
                                    ]);
                                });

                            fclose($out);
                        }, $filename, ['Content-Type' => 'text/csv']);
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListParticipants::route('/'),
            'create' => Pages\CreateParticipant::route('/create'),
            'edit'   => Pages\EditParticipant::route('/{record}/edit'),
        ];
    }

    protected static function csvCell(mixed $value): string
    {
        $value = str_replace(["\r", "\n"], ' ', (string) $value);

        return preg_match('/^[\s]*[=+\-@]/', $value) === 1 ? "'{$value}" : $value;
    }
}
