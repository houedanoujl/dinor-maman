<?php

namespace Database\Seeders;

use App\Support\ContestSettings;
use Illuminate\Database\Seeder;

class ContestContentSeeder extends Seeder
{
    public function run(): void
    {
        ContestSettings::setReglement($this->reglementHtml());
        ContestSettings::setFaq($this->faqItems());
    }

    private function reglementHtml(): string
    {
        return <<<'HTML'
<p class="text-sm text-gray-500"><em>En vigueur à compter du 22/05/2026</em></p>

<h2>Article 1 – Organisateur</h2>
<p>Le jeu-concours « 1 moment de cuisine avec maman » est organisé par <strong>DINOR CI</strong>, ci-après dénommée « l'Organisateur », sur ses plateformes digitales officielles, notamment sa page Facebook et la plateforme de vote.</p>

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
<p>Sont exclues de la participation :</p>
<ul>
    <li>Les membres du personnel de DINOR CI ;</li>
    <li>Toute personne impliquée directement ou indirectement dans l'organisation du jeu.</li>
</ul>

<h2>Article 4 – Modalités de participation</h2>
<p>Pour participer au jeu, chaque participant doit :</p>
<ol>
    <li>Soumettre une photo via le formulaire de participation ;</li>
    <li>Fournir ses coordonnées : prénom, nom, téléphone et ville ;</li>
    <li>Ajouter une courte légende à sa photo : souvenir, anecdote ou message adressé à maman ;</li>
    <li>Accepter le présent règlement.</li>
</ol>
<p>La photo doit montrer un moment de cuisine ou de repas partagé avec maman ou une figure maternelle. La participation est gratuite et <strong>limitée à une seule photo par personne</strong>, contrôlée par numéro de téléphone. Toute participation incomplète, frauduleuse ou non conforme sera considérée comme nulle.</p>

<h2>Article 5 – Critères de validation des photos</h2>
<p>Les photos soumises seront modérées par l'équipe DINOR avant publication. Pour être validée, une photo doit :</p>
<ul>
    <li>Être nette et de bonne qualité ;</li>
    <li>Montrer clairement le participant et sa maman ou une figure maternelle ;</li>
    <li>Être liée à un contexte de cuisine, de repas familial ou de souvenir culinaire ;</li>
    <li>Ne comporter aucun contenu violent, offensant, discriminatoire, illicite ou contraire aux bonnes mœurs ;</li>
    <li>Ne pas être une photo générée avec IA.</li>
</ul>
<p>Toute photo ne respectant pas ces critères pourra être rejetée. Le participant pourra être informé du motif du rejet.</p>

<h2>Article 6 – Durée du jeu</h2>
<ul>
    <li><strong>Ouverture du jeu :</strong> Vendredi 22 mai 2026 à 09h00 — publication officielle sur la page Facebook et Instagram de DINOR.</li>
    <li><strong>Clôture des participations :</strong> Lundi 25 mai 2026 à 23h59.</li>
    <li><strong>Clôture des votes :</strong> Jeudi 28 mai 2026 à 12h00.</li>
    <li><strong>Annonce des gagnants :</strong> Jeudi 28 mai 2026 à 15h00 sur les plateformes officielles de DINOR.</li>
    <li><strong>Remise des récompenses :</strong> Vendredi 29 mai 2026.</li>
</ul>
<p>DINOR CI se réserve le droit de modifier les dates du jeu ou d'annuler l'opération en cas de force majeure ou de circonstances indépendantes de sa volonté.</p>

<h2>Article 7 – Votes</h2>
<p>Pour voter, chaque visiteur doit créer un compte sur la plateforme en renseignant son numéro de téléphone. Un mot de passe lui est alors envoyé par SMS. Chaque utilisateur ne peut voter qu'une seule fois par participant. Les votes sont également contrôlés par adresse IP, session et tout autre dispositif technique mis en place par l'Organisateur afin de prévenir les abus.</p>
<p>Les votes automatisés, frauduleux ou obtenus par des moyens non conformes entraîneront la disqualification du participant concerné.</p>

<h2>Article 8 – Désignation des gagnants</h2>
<p>À la clôture du concours, les <strong>3 participants</strong> ayant obtenu le plus grand nombre de votes légitimes seront désignés gagnants. En cas d'égalité entre plusieurs participants, la photo soumise en premier sera priorisée. La décision de DINOR CI est souveraine et sans appel.</p>

<h2>Article 9 – Dotations</h2>
<p>Les gagnants remporteront des lots composés de :</p>
<ul>
    <li>Produits DINOR (huile Dinor, mayonnaise Dinor, gadgets).</li>
</ul>
<p>Les lots sont strictement personnels, non échangeables, non remboursables et non cessibles. Aucun équivalent en espèces ne pourra être réclamé.</p>

<h2>Article 10 – Annonce et remise des lots</h2>
<p>Les gagnants seront annoncés sur les plateformes officielles de DINOR CI. Après l'annonce, chaque gagnant devra contacter DINOR CI par message privé ou via le canal indiqué afin de confirmer ses coordonnées. La remise des lots se fera selon les modalités communiquées par DINOR CI.</p>

<h2>Article 11 – Droits d'utilisation des photos</h2>
<p>En soumettant une photo, le participant autorise DINOR CI à utiliser ladite photo dans le cadre de la communication liée au jeu-concours, notamment sur :</p>
<ul>
    <li>Les réseaux sociaux ;</li>
    <li>Le site web ou l'application DINOR ;</li>
    <li>Les supports promotionnels liés au concours.</li>
</ul>
<p>Aucune utilisation commerciale externe ne sera faite sans accord préalable du participant.</p>

<h2>Article 12 – Données personnelles</h2>
<p>Les données collectées dans le cadre du jeu-concours, notamment le nom, prénom, téléphone et ville, sont utilisées uniquement pour la gestion du concours, la modération des participations, les notifications et la remise des lots. Ces données ne seront ni vendues ni cédées à des tiers.</p>
<p>Afin de garantir l'intégrité du vote et prévenir les abus, l'Organisateur enregistre également des données techniques liées à chaque inscription, connexion et vote :</p>
<ul>
    <li>l'adresse IP utilisée lors de l'inscription, de la connexion et de chaque vote ;</li>
    <li>la date et l'heure des actions effectuées ;</li>
    <li>l'identifiant technique de session et le type de navigateur (user-agent).</li>
</ul>
<p>Ces informations sont conservées pendant toute la durée du concours et jusqu'à six (6) mois après son terme, à des fins de détection de fraudes et de contestation éventuelle des résultats.</p>
<p>Conformément à la réglementation applicable en Côte d'Ivoire, chaque participant dispose d'un droit d'accès, de rectification et de suppression de ses données personnelles en contactant DINOR CI ou les autorités ivoiriennes chargées de la protection des données personnelles (ARTCI).</p>

<h2>Article 13 – Responsabilité</h2>
<p>DINOR CI ne saurait être tenu responsable :</p>
<ul>
    <li>En cas de problème technique lié à Facebook, à l'application ou au formulaire de participation ;</li>
    <li>En cas de participation frauduleuse ;</li>
    <li>En cas d'impossibilité pour un gagnant de bénéficier de son lot pour des raisons indépendantes de la volonté de l'Organisateur ;</li>
    <li>En cas de suppression ou de rejet d'une photo non conforme au présent règlement.</li>
</ul>

<h2>Article 14 – Modification du règlement</h2>
<p>DINOR CI se réserve le droit de modifier, suspendre ou annuler le présent jeu-concours à tout moment en cas de force majeure ou de nécessité liée au bon déroulement de l'opération. Les participants seront informés de toute modification substantielle.</p>

<h2>Article 15 – Acceptation du règlement</h2>
<p>La participation au jeu-concours implique l'acceptation pleine, entière et sans réserve du présent règlement.</p>

<h2>Article 16 – Mention obligatoire Facebook</h2>
<p>Ce jeu-concours n'est ni géré, ni sponsorisé, ni approuvé par Facebook. Les informations communiquées par les participants sont fournies à DINOR CI et non à Facebook.</p>
HTML;
    }

    private function faqItems(): array
    {
        return [
            ['q' => 'Quel est le principe du concours ?',
             'a' => "Le concours « Un moment de cuisine avec maman » invite les participants à partager une photo souvenir en cuisine avec leur maman ou une figure maternelle."],
            ['q' => 'Qui peut participer ?',
             'a' => "Toute personne souhaitant partager une photo d'un moment de cuisine avec sa maman ou une quelconque personne qu'elle considère comme sa maman."],
            ['q' => 'Comment participer ?',
             'a' => "Pour participer, il faut soumettre une photo via le formulaire de participation, ajouter ses coordonnées et accepter le règlement du concours."],
            ['q' => 'Quelles informations faut-il fournir ?',
             'a' => "Il faut renseigner son prénom, son nom, son numéro de téléphone et sa ville et une photo."],
            ['q' => 'La participation est-elle gratuite ?',
             'a' => "Oui, la participation au concours est entièrement gratuite."],
            ['q' => 'Combien de photos peut-on soumettre ?',
             'a' => "Chaque participant peut soumettre une seule photo. La participation est contrôlée par numéro de téléphone."],
            ['q' => 'Quel type de photo est accepté ?',
             'a' => "La photo doit être nette, de bonne qualité, montrer clairement le participant avec sa maman ou une figure maternelle, et être prise dans un contexte de cuisine."],
            ['q' => 'Est-ce qu\'une photo peut être refusée ?',
             'a' => "Oui. Une photo peut être refusée si elle est floue, de mauvaise qualité, hors sujet ou si elle contient un élément violent, offensant, inapproprié ou généré par IA."],
            ['q' => 'Comment savoir si ma photo est acceptée ?',
             'a' => "Après soumission, la photo est vérifiée par l'équipe DINOR. Le participant verra sa photo dans la galerie des photos pour le concours ou recevra une notification en cas d'approbation ou de rejet. La durée de validation d'une photo est de 1 heure maximum entre 8h00 et 19h00. Cependant elle peut être validée bien avant si elle remplit toutes les conditions."],
            ['q' => 'Comment les gagnants sont-ils désignés ?',
             'a' => "Les gagnants seront les 3 participants ayant obtenu le plus grand nombre de votes valides (likes) à la clôture du concours Jeudi 28 Mai 2026 à 12h00."],
            ['q' => 'Qui peut voter ?',
             'a' => "Pour voter, il faut créer un compte avec son numéro de téléphone. Un mot de passe est envoyé gratuitement par SMS et permet de se connecter à la plateforme."],
            ['q' => 'Peut-on voter plusieurs fois ?',
             'a' => "Non. Chaque compte ne peut voter qu'une seule fois par participant. Les votes sont également contrôlés par adresse IP et session pour prévenir les abus."],
            ['q' => 'Les votes frauduleux sont-ils autorisés ?',
             'a' => "Non. Les votes automatisés ou frauduleux peuvent entraîner la disqualification du participant concerné."],
            ['q' => 'Que se passe-t-il en cas d\'égalité ?',
             'a' => "En cas d'égalité, la photo soumise en premier sera priorisée."],
            ['q' => 'Quelles sont les récompenses ?',
             'a' => "Les gagnants recevront des lots composés de produits DINOR (huile Dinor, mayonnaise Dinor, gadgets)."],
            ['q' => 'Ma photo peut-elle être utilisée par DINOR ?',
             'a' => "Oui. En participant, vous autorisez DINOR à utiliser votre photo dans le cadre de la communication liée au concours : réseaux sociaux, site web ou supports promotionnels."],
            ['q' => 'Mes données personnelles seront-elles partagées ?',
             'a' => "Non. Les données collectées servent uniquement à la gestion du concours et aux notifications liées à la participation."],
            ['q' => 'Puis-je demander la suppression de mes données ?',
             'a' => "Oui. Conformément à la réglementation ivoirienne sur la protection des données personnelles (ARTCI), vous pouvez demander l'accès, la modification ou la suppression de vos données en contactant l'équipe organisatrice ou les autorités ivoiriennes chargées de la protection des données personnelles."],
            ['q' => 'Le règlement peut-il être modifié ?',
             'a' => "L'organisateur se réserve le droit de modifier le règlement si nécessaire. Les participants seront informés en cas de changement important."],
            ['q' => 'Quelle est la date de début du concours ?',
             'a' => "Le concours est en vigueur à compter du 22/05/2026."],
        ];
    }
}
