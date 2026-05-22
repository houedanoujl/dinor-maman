<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Suppression du rôle voter : votes désormais anonymes (cookie).
 * Delete tous les users role=voter (n'avaient aucune participation).
 */
return new class extends Migration
{
    public function up(): void
    {
        // Safety: ne supprime que les voters sans participation liée
        $voters = DB::table('users')->where('role', 'voter')->pluck('id');

        if ($voters->isNotEmpty()) {
            // Vérif : aucun voter ne devrait avoir de participant (role serait participant)
            $withParticipant = DB::table('participants')
                ->whereIn('user_id', $voters)
                ->pluck('user_id');

            $toDelete = $voters->diff($withParticipant);

            if ($toDelete->isNotEmpty()) {
                DB::table('users')->whereIn('id', $toDelete)->delete();
            }

            // Si un voter a une participation (cas anormal), reclassifier en participant
            if ($withParticipant->isNotEmpty()) {
                DB::table('users')->whereIn('id', $withParticipant)
                    ->update(['role' => 'participant']);
            }
        }
    }

    public function down(): void
    {
        // Pas de rollback : voters supprimés irrécupérables
    }
};
