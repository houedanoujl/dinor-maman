<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $row = DB::table('settings')->where('key', 'contest.reglement')->first();
        if (! $row) {
            return;
        }

        $current = (string) $row->value;
        if (str_contains($current, 'adresse IP utilisée lors de l\'inscription')) {
            return; // déjà patché
        }

        $insertion = "<p>Afin de garantir l'intégrité du vote et prévenir les abus, l'Organisateur enregistre également des données techniques liées à chaque inscription, connexion et vote :</p>\n"
            . "<ul>\n"
            . "    <li>l'adresse IP utilisée lors de l'inscription, de la connexion et de chaque vote ;</li>\n"
            . "    <li>la date et l'heure des actions effectuées ;</li>\n"
            . "    <li>l'identifiant technique de session et le type de navigateur (user-agent).</li>\n"
            . "</ul>\n"
            . "<p>Ces informations sont conservées pendant toute la durée du concours et jusqu'à six (6) mois après son terme, à des fins de détection de fraudes et de contestation éventuelle des résultats.</p>\n";

        // Insère le bloc IP avant le paragraphe « Conformément à la réglementation applicable ».
        $needle = "<p>Conformément à la réglementation applicable en Côte d'Ivoire";
        if (str_contains($current, $needle)) {
            $patched = str_replace($needle, $insertion . $needle, $current);
        } else {
            // Fallback: ajout à la fin
            $patched = $current . "\n" . $insertion;
        }

        DB::table('settings')
            ->where('key', 'contest.reglement')
            ->update(['value' => $patched, 'updated_at' => now()]);

        \Illuminate\Support\Facades\Cache::forget('contest_reglement');
    }

    public function down(): void
    {
        // Non réversible (patch texte).
    }
};
