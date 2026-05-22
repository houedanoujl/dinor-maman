<?php

namespace App\Filament\Pages;

use App\Models\Participant;
use App\Models\SmsLog;
use App\Models\User;
use App\Models\Vote;
use App\Models\Winner;
use App\Support\ContestSettings;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema as DbSchema;

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
        $faq = ContestSettings::getFaq();

        $this->form->fill([
            'ends_at'        => ContestSettings::endsAt(),
            'upload_ends_at' => ContestSettings::uploadEndsAt()->equalTo(ContestSettings::endsAt())
                ? null
                : ContestSettings::uploadEndsAt(),
            'announce_at'    => ContestSettings::announceAt(),
            'reglement'      => ContestSettings::getReglement() ?? $this->defaultReglement(),
            'faq'            => $faq ?: $this->defaultFaq(),
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

                Section::make("Phase d'upload")
                    ->description("Optionnel. Jusqu'à cette date les participants peuvent soumettre et modifier leur photo. Après : phase de vote uniquement (modifications bloquées), votes ouverts jusqu'à la fin du concours. Laissez vide pour autoriser les uploads jusqu'à la fin.")
                    ->schema([
                        Forms\Components\DateTimePicker::make('upload_ends_at')
                            ->label("Fin de la phase d'upload")
                            ->seconds(false)
                            ->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->before('ends_at')
                            ->helperText("Doit être antérieure à la fin du concours."),
                    ]),

                Section::make("Annonce des gagnants")
                    ->description("Date et heure de proclamation des résultats. Affichée publiquement sur la plateforme.")
                    ->schema([
                        Forms\Components\DateTimePicker::make('announce_at')
                            ->label("Proclamation des résultats")
                            ->seconds(false)
                            ->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->after('ends_at')
                            ->helperText("Doit être postérieure à la fin des votes."),
                    ]),

                Section::make('Règlement du concours')
                    ->description('Contenu affiché sur la page /reglement.')
                    ->schema([
                        Forms\Components\RichEditor::make('reglement')
                            ->label('Texte du règlement')
                            ->toolbarButtons([
                                'bold', 'italic', 'underline',
                                'h2', 'h3', 'bulletList', 'orderedList',
                                'link', 'blockquote', 'undo', 'redo',
                            ])
                            ->columnSpanFull(),
                    ]),

                Section::make('FAQ — Questions fréquentes')
                    ->description('Contenu affiché sur la page /faq.')
                    ->schema([
                        Forms\Components\Repeater::make('faq')
                            ->label('')
                            ->schema([
                                Forms\Components\TextInput::make('q')
                                    ->label('Question')
                                    ->required()
                                    ->columnSpanFull(),
                                Forms\Components\Textarea::make('a')
                                    ->label('Réponse')
                                    ->rows(3)
                                    ->required()
                                    ->columnSpanFull(),
                            ])
                            ->itemLabel(fn (array $state): ?string => $state['q'] ?? null)
                            ->addActionLabel('Ajouter une question')
                            ->collapsible()
                            ->collapsed()
                            ->reorderable()
                            ->columnSpanFull(),
                    ]),

            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('announceWinners')
                ->label('Figer le top 3')
                ->icon('heroicon-o-trophy')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Figer le top 3 des gagnants ?')
                ->modalDescription(function () {
                    $cycle = ContestSettings::endsAt()->format('Y-m');
                    $exists = Winner::where('contest_cycle', $cycle)->exists();
                    if ($exists) {
                        return "Top 3 déjà figé pour le cycle {$cycle}. Vous devez d'abord supprimer les gagnants actuels pour re-figer.";
                    }
                    return 'Crée un snapshot des 3 participants avec le plus de votes. Aucun SMS ni email envoyé — l\'annonce officielle reste à publier manuellement par DINOR sur ses plateformes (Facebook, Instagram).';
                })
                ->modalSubmitActionLabel('Figer maintenant')
                ->disabled(function () {
                    $cycle = ContestSettings::endsAt()->format('Y-m');
                    return Winner::where('contest_cycle', $cycle)->exists();
                })
                ->action(function () {
                    $cycle = ContestSettings::endsAt()->format('Y-m');

                    if (Winner::where('contest_cycle', $cycle)->exists()) {
                        Notification::make()->warning()->title('Top 3 déjà figé')->body("Cycle {$cycle} déjà annoncé.")->send();
                        return;
                    }

                    $top = Participant::approved()
                        ->orderByDesc('vote_count')
                        ->orderBy('created_at', 'asc')
                        ->take(3)
                        ->get();

                    if ($top->isEmpty()) {
                        Notification::make()->danger()->title('Aucun participant')->body('Aucun participant approuvé à figer.')->send();
                        return;
                    }

                    foreach ($top as $index => $participant) {
                        Winner::create([
                            'participant_id' => $participant->id,
                            'rank' => $index + 1,
                            'vote_count_snapshot' => $participant->vote_count,
                            'announced_at' => now(),
                            'contest_cycle' => $cycle,
                        ]);
                    }

                    Notification::make()
                        ->success()
                        ->title('Top 3 figé')
                        ->body('Snapshot enregistré. Publiez maintenant l\'annonce sur les plateformes DINOR.')
                        ->send();
                }),

            Action::make('resetContestData')
                ->label('Réinitialiser le concours')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('⚠ Supprimer toutes les participations et votes ?')
                ->modalDescription('Cette action supprime définitivement : tous les participants, leurs photos, tous les votes, tous les votants et les logs SMS. Les comptes administrateurs sont conservés. Action IRRÉVERSIBLE — à utiliser uniquement avant le lancement officiel du concours.')
                ->modalSubmitActionLabel('Oui, tout supprimer')
                ->form([
                    Forms\Components\TextInput::make('confirmation')
                        ->label('Tapez SUPPRIMER pour confirmer')
                        ->required()
                        ->rule(fn () => function ($attribute, $value, $fail) {
                            if ($value !== 'SUPPRIMER') {
                                $fail('Vous devez taper exactement SUPPRIMER.');
                            }
                        }),
                    Forms\Components\TextInput::make('admin_password')
                        ->label('Votre mot de passe administrateur')
                        ->password()
                        ->required()
                        ->rule(fn () => function ($attribute, $value, $fail) {
                            if (! Hash::check($value, Auth::user()->password)) {
                                $fail('Mot de passe incorrect.');
                            }
                        }),
                ])
                ->action(function (array $data) {
                    $this->performReset();

                    Notification::make()
                        ->success()
                        ->title('Concours réinitialisé')
                        ->body('Toutes les participations, votes et utilisateurs non-admin ont été supprimés.')
                        ->send();
                }),
        ];
    }

    protected function performReset(): void
    {
        DB::transaction(function () {
            Vote::query()->delete();

            Participant::query()->each(function (Participant $p) {
                $p->clearMediaCollection('photo');
                $p->delete();
            });

            DB::table('participants')->update(['vote_count' => 0]);

            User::where('role', '!=', User::ROLE_ADMIN)->delete();

            SmsLog::query()->delete();

            foreach (['vote:user:%', 'contest-submit%', 'login:%'] as $pattern) {
                try {
                    DB::table('cache')->where('key', 'like', '%' . $pattern)->delete();
                } catch (\Throwable $e) {
                    // ignore
                }
            }

            if (DB::connection()->getDriverName() === 'mysql') {
                foreach (['votes', 'participants', 'sms_logs'] as $table) {
                    if (DbSchema::hasTable($table)) {
                        DB::statement("ALTER TABLE {$table} AUTO_INCREMENT = 1");
                    }
                }
            }
        });
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
        ContestSettings::setUploadEndsAt($state['upload_ends_at'] ?? null);
        ContestSettings::setAnnounceAt($state['announce_at'] ?? null);

        if (! empty($state['reglement'])) {
            ContestSettings::setReglement($state['reglement']);
        }

        if (! empty($state['faq'])) {
            ContestSettings::setFaq($state['faq']);
        }

        Notification::make()
            ->success()
            ->title('Reglages enregistres')
            ->send();
    }

    protected function defaultFaq(): array
    {
        return [
            ['q' => 'Quel est le principe du concours ?', 'a' => 'Le concours « Un moment de cuisine avec maman » invite les participants à partager une photo souvenir en cuisine avec leur maman ou une figure maternelle.'],
            ['q' => 'Qui peut participer ?', 'a' => "Toute personne souhaitant partager une photo d'un moment de cuisine avec sa maman ou une quelconque personne qu'elle considère comme sa maman."],
            ['q' => 'Comment participer ?', 'a' => 'Pour participer, il faut soumettre une photo via le formulaire de participation, ajouter ses coordonnées et accepter le règlement du concours.'],
            ['q' => 'La participation est-elle gratuite ?', 'a' => 'Oui, la participation au concours est entièrement gratuite.'],
            ['q' => 'Combien de photos peut-on soumettre ?', 'a' => 'Chaque participant peut soumettre une seule photo. La participation est contrôlée par numéro de téléphone.'],
            ['q' => 'Comment les gagnants sont-ils désignés ?', 'a' => "Les gagnants seront les 3 participants ayant obtenu le plus grand nombre de votes valides à la clôture du concours."],
            ['q' => 'Qui peut voter ?', 'a' => 'Tout le monde peut voter, sans inscription.'],
            ['q' => 'Peut-on voter plusieurs fois ?', 'a' => 'Chaque visiteur peut voter une seule fois par participant, mais peut soutenir plusieurs participants différents.'],
            ['q' => 'Mes données personnelles seront-elles partagées ?', 'a' => 'Non. Les données collectées servent uniquement à la gestion du concours et aux notifications liées à la participation.'],
        ];
    }

    protected function defaultReglement(): string
    {
        return <<<'HTML'
<h2>Article 1 – Organisateur</h2>
<p>Le jeu-concours « 1 moment de cuisine avec maman » est organisé par DINOR CI, ci-après dénommée « l'Organisateur », sur ses plateformes digitales officielles, notamment sa page Facebook et la plateforme de vote.</p>

<h2>Article 2 – Objet du jeu</h2>
<p>Dans le cadre de la fête des mères, DINOR CI organise un jeu gratuit sans obligation d'achat intitulé « 1 moment de cuisine avec maman ».</p>
<p>Ce jeu a pour objectif de célébrer les souvenirs précieux de cuisine en famille, les moments de transmission, d'amour et de gourmandise partagés avec maman ou une figure maternelle.</p>

<h2>Article 3 – Conditions de participation</h2>
<p>La participation au jeu est ouverte à toute personne :</p>
<ul>
<li>Résidant en Côte d'Ivoire ;</li>
<li>Âgée d'au moins 18 ans ;</li>
<li>Disposant d'un compte Facebook valide ou d'un accès à l'application DINOR ;</li>
<li>Acceptant sans réserve le présent règlement.</li>
</ul>
<p>Sont exclues de la participation : les membres du personnel de DINOR CI et toute personne impliquée directement ou indirectement dans l'organisation du jeu.</p>

<h2>Article 4 – Modalités de participation</h2>
<p>Pour participer au jeu, chaque participant doit :</p>
<ol>
<li>Soumettre une photo via le formulaire de participation ;</li>
<li>Fournir ses coordonnées : prénom, nom, téléphone et ville ;</li>
<li>Ajouter une courte légende à sa photo : souvenir, anecdote ou message adressé à maman ;</li>
<li>Accepter le présent règlement.</li>
</ol>
<p>La participation est gratuite et limitée à une seule photo par personne, contrôlée par numéro de téléphone.</p>

<h2>Article 5 – Critères de validation des photos</h2>
<p>Les photos soumises seront modérées par l'équipe DINOR avant publication. Pour être validée, une photo doit :</p>
<ul>
<li>Être nette et de bonne qualité ;</li>
<li>Montrer clairement le participant et sa maman ou une figure maternelle ;</li>
<li>Être liée à un contexte de cuisine, de repas familial ou de souvenir culinaire ;</li>
<li>Ne comporter aucun contenu violent, offensant, discriminatoire, illicite ou contraire aux bonnes mœurs ;</li>
<li>Ne pas être une photo générée avec IA.</li>
</ul>

<h2>Article 6 – Durée du jeu</h2>
<ul>
<li><strong>Ouverture :</strong> à compter du 20 mai 2026</li>
<li><strong>Clôture des participations et votes :</strong> lundi 25 mai 2026 à 12h00</li>
<li><strong>Annonce des gagnants :</strong> mercredi 27 mai 2026</li>
<li><strong>Remise des récompenses :</strong> vendredi 29 mai 2026</li>
</ul>

<h2>Article 7 – Votes</h2>
<p>Les votes sont ouverts à tous, sans inscription obligatoire. Chaque visiteur peut voter une seule fois par participant. Les votes automatisés ou frauduleux entraîneront la disqualification du participant concerné.</p>

<h2>Article 8 – Désignation des gagnants</h2>
<p>À la clôture du concours, les 3 participants ayant obtenu le plus grand nombre de votes légitimes seront désignés gagnants. En cas d'égalité, la photo soumise en premier sera priorisée.</p>

<h2>Article 9 – Dotations</h2>
<p>Les gagnants remporteront des lots composés de produits DINOR. Les lots sont strictement personnels, non échangeables, non remboursables et non cessibles. Aucun équivalent en espèces ne pourra être réclamé.</p>

<h2>Article 10 – Annonce et remise des lots</h2>
<p>Les gagnants seront annoncés sur les plateformes officielles de DINOR CI. Chaque gagnant devra contacter DINOR CI dans un délai de 24 heures après l'annonce pour confirmer ses coordonnées.</p>

<h2>Article 11 – Droits d'utilisation des photos</h2>
<p>En soumettant une photo, le participant autorise DINOR CI à utiliser ladite photo dans le cadre de la communication liée au jeu-concours (réseaux sociaux, site web, supports promotionnels). Aucune utilisation commerciale externe ne sera faite sans accord préalable.</p>

<h2>Article 12 – Données personnelles</h2>
<p>Les données collectées (nom, prénom, téléphone, ville) sont utilisées uniquement pour la gestion du concours, la modération, les notifications et la remise des lots. Ces données ne seront ni vendues ni cédées à des tiers. Chaque participant dispose d'un droit d'accès, de rectification et de suppression en contactant DINOR CI.</p>

<h2>Article 13 – Responsabilité</h2>
<p>DINOR CI ne saurait être tenu responsable en cas de problème technique, de participation frauduleuse, ou d'impossibilité pour un gagnant de bénéficier de son lot pour des raisons indépendantes de la volonté de l'Organisateur.</p>

<h2>Article 14 – Modification du règlement</h2>
<p>DINOR CI se réserve le droit de modifier, suspendre ou annuler le présent jeu-concours à tout moment en cas de force majeure. Les participants seront informés de toute modification substantielle.</p>

<h2>Article 15 – Acceptation du règlement</h2>
<p>La participation au jeu-concours implique l'acceptation pleine, entière et sans réserve du présent règlement.</p>

<h2>Article 16 – Mention obligatoire Facebook</h2>
<p>Ce jeu-concours n'est ni géré, ni sponsorisé, ni approuvé par Facebook. Les informations communiquées par les participants sont fournies à DINOR CI et non à Facebook.</p>
HTML;
    }
}
