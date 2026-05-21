<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            // Dédupliquer : ne garder que le 1er vote par couple (participant, user)
            DB::statement("
                DELETE v1 FROM votes v1
                INNER JOIN votes v2
                  ON v1.participant_id = v2.participant_id
                 AND v1.user_id = v2.user_id
                 AND v1.user_id IS NOT NULL
                 AND v1.id > v2.id
            ");

            // Drop FK avant manipulation index
            $fks = collect(DB::select("
                SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'votes'
                  AND REFERENCED_TABLE_NAME IS NOT NULL
            "))->pluck('CONSTRAINT_NAME');

            foreach ($fks as $fk) {
                DB::statement("ALTER TABLE votes DROP FOREIGN KEY `{$fk}`");
            }

            $indexes = collect(DB::select("SHOW INDEX FROM votes"))->pluck('Key_name')->unique();

            // Recréer ancien unique avant de drop le quotidien
            DB::statement('ALTER TABLE votes ADD UNIQUE KEY unique_vote_user (participant_id, user_id)');

            if ($indexes->contains('unique_vote_user_daily')) {
                DB::statement('ALTER TABLE votes DROP INDEX unique_vote_user_daily');
            }

            // Drop colonne générée
            $hasVoteDate = collect(DB::select("SHOW COLUMNS FROM votes LIKE 'vote_date'"))->isNotEmpty();
            if ($hasVoteDate) {
                DB::statement('ALTER TABLE votes DROP COLUMN vote_date');
            }

            // Recréer FK
            DB::statement('ALTER TABLE votes ADD CONSTRAINT votes_participant_id_foreign FOREIGN KEY (participant_id) REFERENCES participants(id) ON DELETE CASCADE');
            DB::statement('ALTER TABLE votes ADD CONSTRAINT votes_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE');
            return;
        }

        if ($driver === 'sqlite') {
            DB::statement("
                DELETE FROM votes WHERE id NOT IN (
                    SELECT MIN(id) FROM votes WHERE user_id IS NOT NULL GROUP BY participant_id, user_id
                ) AND user_id IS NOT NULL
            ");
            DB::statement('DROP INDEX IF EXISTS unique_vote_user_daily');
            DB::statement('CREATE UNIQUE INDEX unique_vote_user ON votes (participant_id, user_id)');
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement("
                DELETE FROM votes WHERE id NOT IN (
                    SELECT MIN(id) FROM votes WHERE user_id IS NOT NULL GROUP BY participant_id, user_id
                ) AND user_id IS NOT NULL
            ");
            DB::statement('DROP INDEX IF EXISTS unique_vote_user_daily');
            DB::statement('CREATE UNIQUE INDEX unique_vote_user ON votes (participant_id, user_id)');
            return;
        }
    }

    public function down(): void
    {
        // Pas de rollback automatique — utiliser migration 2026_05_21_000005.
    }
};
