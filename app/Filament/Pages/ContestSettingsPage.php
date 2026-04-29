<?php

namespace App\Filament\Pages;

use App\Support\ContestSettings;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ContestSettingsPage extends Page
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Reglages du concours';

    protected static ?string $title = 'Reglages du concours';

    protected static ?string $slug = 'contest-settings';

    protected static ?int $navigationSort = 90;

    protected string $view = 'filament.pages.contest-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'ends_at' => ContestSettings::endsAt(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Date de fin')
                    ->description('Apres cette date, les participations et les votes sont automatiquement bloques.')
                    ->schema([
                        Forms\Components\DateTimePicker::make('ends_at')
                            ->label('Fin du concours')
                            ->seconds(false)
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y H:i'),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Enregistrer')
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        $state = $this->form->getState();

        ContestSettings::setEndsAt($state['ends_at']);

        Notification::make()
            ->success()
            ->title('Reglages enregistres')
            ->body('La nouvelle date de fin du concours est appliquee.')
            ->send();
    }
}
