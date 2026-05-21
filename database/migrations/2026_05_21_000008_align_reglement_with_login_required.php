<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $reglement = DB::table('settings')->where('key', 'contest.reglement')->value('value');

        if ($reglement) {
            $old = '<h2>Article 7 – Votes</h2>
<p>Les votes sont ouverts à tous, sans inscription obligatoire. Chaque visiteur peut voter une seule fois par participant, le vote étant contrôlé par adresse IP, session ou tout autre dispositif technique mis en place par l\'Organisateur.</p>';

            $new = '<h2>Article 7 – Votes</h2>
<p>Pour voter, chaque visiteur doit créer un compte sur la plateforme en renseignant son numéro de téléphone. Un mot de passe lui est alors envoyé par SMS. Chaque utilisateur ne peut voter qu\'une seule fois par participant. Les votes sont également contrôlés par adresse IP, session et tout autre dispositif technique mis en place par l\'Organisateur afin de prévenir les abus.</p>';

            $updated = str_replace($old, $new, $reglement);

            DB::table('settings')->where('key', 'contest.reglement')->update([
                'value'      => $updated,
                'updated_at' => now(),
            ]);
        }

        $faqRaw = DB::table('settings')->where('key', 'contest.faq')->value('value');
        if ($faqRaw) {
            $faq = json_decode($faqRaw, true) ?: [];
            foreach ($faq as &$item) {
                if (($item['q'] ?? '') === 'Qui peut voter ?') {
                    $item['a'] = "Pour voter, il faut créer un compte avec son numéro de téléphone. Un mot de passe est envoyé gratuitement par SMS et permet de se connecter à la plateforme.";
                }
                if (($item['q'] ?? '') === 'Peut-on voter plusieurs fois ?') {
                    $item['a'] = "Non. Chaque compte ne peut voter qu'une seule fois par participant. Les votes sont également contrôlés par adresse IP et session pour prévenir les abus.";
                }
            }
            unset($item);

            DB::table('settings')->where('key', 'contest.faq')->update([
                'value'      => json_encode($faq, JSON_UNESCAPED_UNICODE),
                'updated_at' => now(),
            ]);
        }

        Cache::forget('contest_reglement');
        Cache::forget('contest_faq');
    }

    public function down(): void
    {
        // Pas de rollback — modifications de contenu.
    }
};
