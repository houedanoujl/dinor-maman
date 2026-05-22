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
            $newArticle7 = '<h2>Article 7 – Votes</h2>
<p>Les votes sont ouverts à tous, sans création de compte ni numéro de téléphone. <strong>Chaque visiteur peut voter une seule fois</strong> pour un seul participant sur toute la durée du concours. Le vote est anonyme et contrôlé par cookie de navigation, adresse IP, session et tout autre dispositif technique mis en place par l\'Organisateur afin de prévenir les abus.</p>
<p>Les votes automatisés, frauduleux ou obtenus par des moyens non conformes entraîneront la disqualification du participant concerné.</p>';

            // Remplace l'Article 7 complet (et le paragraphe disqualif qui suit)
            $pattern = '#<h2>Article 7\s*[–-]\s*Votes</h2>.*?(?=<h2>Article 8)#s';
            $updated = preg_replace($pattern, $newArticle7 . "\n\n", $reglement, 1);

            if ($updated && $updated !== $reglement) {
                DB::table('settings')->where('key', 'contest.reglement')->update([
                    'value'      => $updated,
                    'updated_at' => now(),
                ]);
            }
        }

        $faqRaw = DB::table('settings')->where('key', 'contest.faq')->value('value');
        if ($faqRaw) {
            $faq = json_decode($faqRaw, true) ?: [];
            foreach ($faq as &$item) {
                if (($item['q'] ?? '') === 'Qui peut voter ?') {
                    $item['a'] = "Tout le monde peut voter, sans création de compte ni numéro de téléphone. Le vote est anonyme.";
                }
                if (($item['q'] ?? '') === 'Peut-on voter plusieurs fois ?') {
                    $item['a'] = "Non. Chaque visiteur ne peut voter qu'une seule fois pour un seul participant sur toute la durée du concours. Le contrôle se fait par cookie, IP et session pour prévenir les abus.";
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
