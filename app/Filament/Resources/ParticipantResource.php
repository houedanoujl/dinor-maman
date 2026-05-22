<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ParticipantResource\Pages;
use App\Models\Participant;
use App\Models\SmsLog;
use App\Notifications\ParticipationApproved;
use App\Notifications\ParticipationRejected;
use App\Services\SmsDispatcher;
use App\Services\SmsNotifier;
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
                    Forms\Components\Select::make('city')
                        ->required()
                        ->label('Ville / Quartier')
                        ->options(\App\Support\Abidjan::flat())
                        ->searchable(),
                    Forms\Components\TextInput::make('email')->email()->label('Email'),
                    Forms\Components\TextInput::make('user.plain_password')
                        ->label('Mot de passe (clair)')
                        ->revealable()
                        ->password()
                        ->disabled()
                        ->dehydrated(false)
                        ->placeholder('—')
                        ->helperText('Mot de passe envoyé par SMS lors de l\'inscription.'),
                    Forms\Components\Select::make('status')
                        ->label('Statut')
                        ->options([
                            'pending'  => 'En attente',
                            'approved' => 'Approuvé',
                            'rejected' => 'Rejeté',
                        ])
                        ->required(),
                    Forms\Components\Textarea::make('anecdote')
                        ->label('Anecdote / Message')
                        ->rows(3)
                        ->maxLength(500)
                        ->placeholder('Message laissé par le participant…')
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('rejection_reason')
                        ->label('Motif de rejet')
                        ->rows(3)
                        ->placeholder('Précisez la raison du rejet visible par le participant…')
                        ->visible(fn ($get) => $get('status') === 'rejected'),
                ]),
            Forms\Components\SpatieMediaLibraryFileUpload::make('photo')
                ->collection('photo')
                ->image()
                ->imageEditor()
                ->label('Photo'),
        ]);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with(['media', 'user']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('vote_count', 'desc')
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
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
                    ->sortable()
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->city),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Téléphone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.plain_password')
                    ->label('Mot de passe')
                    ->copyable()
                    ->copyMessage('Mot de passe copié')
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('user.signup_ip')
                    ->label('IP inscription')
                    ->copyable()
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('user.last_login_ip')
                    ->label('IP dernière connexion')
                    ->copyable()
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('anecdote')
                    ->label('Anecdote')
                    ->limit(60)
                    ->placeholder('—')
                    ->toggleable(),
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
                            'rejection_reason' => $state ? null : $record->rejection_reason,
                        ]);

                        if ($state) {
                            // Régénère un dashboard_token plaintext pour le mail et le SMS.
                            $record->regenerateDashboardToken();

                            if ($record->email) {
                                Notification::route('mail', $record->email)
                                    ->notify(new ParticipationApproved($record));
                            }

                            app(SmsNotifier::class)->send(
                                $record->phone,
                                'Bonne nouvelle ! Votre photo est approuvee. Partagez ce lien: ' . route('participant.show', $record) . '?ref=' . $record->id
                            );
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
                    ->label('Ville / Quartier')
                    ->options(fn () => \App\Support\Abidjan::flat())
                    ->searchable(),
            ])
            ->recordActions([
                Actions\ActionGroup::make([
                Actions\EditAction::make(),
                Actions\Action::make('resendPassword')
                    ->label('Renvoyer mot de passe SMS')
                    ->icon('heroicon-o-chat-bubble-bottom-center-text')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Renvoyer le mot de passe par SMS ?')
                    ->modalDescription(fn ($record) => 'Génère un nouveau mot de passe (8 chiffres) et l\'envoie au ' . $record->phone . '. L\'ancien sera invalidé.')
                    ->modalSubmitActionLabel('Renvoyer')
                    ->visible(fn ($record) => filled($record->phone) && $record->user_id)
                    ->action(function ($record) {
                        $user = $record->user;
                        if (! $user) {
                            FilamentNotification::make()->danger()->title('Aucun compte associé')->send();
                            return;
                        }

                        $password = str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
                        $user->forceFill([
                            'password' => \Illuminate\Support\Facades\Hash::make($password),
                            'plain_password' => $password,
                        ])->save();

                        $loginUrl = route('login');
                        $message = "DINOR. Nouveau mot de passe: {$password}. Connectez-vous avec votre numero {$user->phone} sur {$loginUrl}.";

                        [$ok, $err] = app(SmsDispatcher::class)->sendNow($user->phone, SmsLog::TYPE_CREDENTIALS, $message);

                        if ($ok) {
                            FilamentNotification::make()
                                ->success()
                                ->title('SMS envoyé')
                                ->body("Nouveau mot de passe envoyé au {$user->phone}.")
                                ->send();
                        } else {
                            FilamentNotification::make()
                                ->danger()
                                ->title('Échec envoi SMS')
                                ->body($err ?: 'Erreur inconnue. Mot de passe régénéré, communiquez-le manuellement.')
                                ->persistent()
                                ->send();
                        }
                    }),
                Actions\Action::make('reject')
                    ->label('Rejeter')
                    ->color('danger')
                    ->icon('heroicon-o-x-mark')
                    ->visible(fn ($record) => $record->status !== 'rejected')
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Motif de rejet')
                            ->rows(4)
                            ->placeholder('Expliquez la raison du rejet (sera envoyé au participant par email si disponible)…')
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status'           => Participant::STATUS_REJECTED,
                            'rejection_reason' => $data['rejection_reason'],
                        ]);

                        if ($record->email) {
                            \Illuminate\Support\Facades\Notification::route('mail', $record->email)
                                ->notify(new ParticipationRejected($record));
                        }

                        app(SmsNotifier::class)->send(
                            $record->phone,
                            'Votre participation n\'a pas ete retenue. Motif: ' . $data['rejection_reason']
                        );

                        FilamentNotification::make()
                            ->danger()
                            ->title('Participation rejetée')
                            ->body($record->email ? 'Le participant a été notifié par email.' : 'Aucun email — le participant n\'a pas été notifié.')
                            ->send();
                    }),
                ])->label('Actions')->icon('heroicon-m-ellipsis-vertical')->button(),
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
