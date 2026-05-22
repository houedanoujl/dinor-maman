<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Utilisateurs';

    protected static ?string $modelLabel = 'Utilisateur';

    protected static ?string $pluralModelLabel = 'Utilisateurs';

    protected static ?int $navigationSort = 20;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount('votes');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informations')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nom')
                        ->required()
                        ->maxLength(100),
                    Forms\Components\TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(150),
                    Forms\Components\Select::make('role')
                        ->label('Rôle')
                        ->options(function ($record) {
                            $opts = [
                                User::ROLE_VOTER       => 'Votant',
                                User::ROLE_PARTICIPANT => 'Participant',
                            ];
                            // Seuls les admins existants peuvent rester admin
                            // (pas de promotion arbitraire depuis l'interface).
                            if ($record && $record->isAdmin()) {
                                $opts[User::ROLE_ADMIN] = 'Administrateur';
                            }
                            return $opts;
                        })
                        ->required()
                        ->native(false)
                        ->disabled(fn ($record) => $record && $record->id === auth()->id())
                        ->helperText('La promotion en Administrateur se fait uniquement via AdminSeeder / CLI (audit trail).'),
                    Forms\Components\TextInput::make('phone')
                        ->label('Téléphone')
                        ->tel()
                        ->maxLength(20),
                    Forms\Components\TextInput::make('password')
                        ->label('Nouveau mot de passe')
                        ->password()
                        ->revealable()
                        ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                        ->dehydrated(fn ($state) => filled($state))
                        ->required(fn (string $context): bool => $context === 'create')
                        ->afterStateUpdated(fn ($state, $set) => filled($state) ? $set('plain_password', $state) : null)
                        ->live(onBlur: true)
                        ->helperText('Laissez vide pour ne pas changer (en édition).'),
                    Forms\Components\TextInput::make('plain_password')
                        ->label('Mot de passe (clair)')
                        ->revealable()
                        ->password()
                        ->dehydrated(fn ($state) => filled($state))
                        ->helperText('Affiché en clair pour assistance utilisateur. Stocké pour permettre relais aux votants/participants.'),
                    Forms\Components\DateTimePicker::make('email_verified_at')
                        ->label('Email vérifié le')
                        ->native(false),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('role')
                    ->label('Rôle')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        User::ROLE_ADMIN       => 'danger',
                        User::ROLE_PARTICIPANT => 'warning',
                        User::ROLE_VOTER       => 'success',
                        default                => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        User::ROLE_ADMIN       => 'Admin',
                        User::ROLE_PARTICIPANT => 'Participant',
                        User::ROLE_VOTER       => 'Votant',
                        default                => $state,
                    }),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Téléphone')
                    ->searchable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('plain_password')
                    ->label('Mot de passe')
                    ->copyable()
                    ->copyMessage('Mot de passe copié')
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('signup_ip')
                    ->label('IP inscription')
                    ->copyable()
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('last_login_ip')
                    ->label('IP dernière connexion')
                    ->copyable()
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('last_login_at')
                    ->label('Dernière connexion')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('votes_count')
                    ->label('Votes')
                    ->badge()
                    ->color('primary')
                    ->sortable(),
                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label('Email vérifié')
                    ->boolean()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Inscrit le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label('Rôle')
                    ->options([
                        User::ROLE_VOTER       => 'Votant',
                        User::ROLE_PARTICIPANT => 'Participant',
                        User::ROLE_ADMIN       => 'Administrateur',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->visible(fn (User $record) => $record->id !== auth()->id()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
