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
            $reglement = str_replace(
                "Conformément à la réglementation applicable, chaque participant dispose d'un droit d'accès, de rectification et de suppression de ses données personnelles en contactant DINOR CI.",
                "Conformément à la réglementation applicable en Côte d'Ivoire, chaque participant dispose d'un droit d'accès, de rectification et de suppression de ses données personnelles en contactant DINOR CI ou les autorités ivoiriennes chargées de la protection des données personnelles (ARTCI).",
                $reglement
            );

            DB::table('settings')->where('key', 'contest.reglement')->update([
                'value'      => $reglement,
                'updated_at' => now(),
            ]);
        }

        $faqRaw = DB::table('settings')->where('key', 'contest.faq')->value('value');
        if ($faqRaw) {
            $faq = json_decode($faqRaw, true) ?: [];
            foreach ($faq as &$item) {
                if (($item['q'] ?? '') === 'Quelles sont les récompenses ?') {
                    $item['a'] = "Les gagnants recevront des lots composés de produits DINOR (huile Dinor, mayonnaise Dinor, gadgets).";
                }
                if (($item['q'] ?? '') === 'Puis-je demander la suppression de mes données ?') {
                    $item['a'] = "Oui. Conformément à la réglementation ivoirienne sur la protection des données personnelles (ARTCI), vous pouvez demander l'accès, la modification ou la suppression de vos données en contactant l'équipe organisatrice ou les autorités ivoiriennes chargées de la protection des données personnelles.";
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
