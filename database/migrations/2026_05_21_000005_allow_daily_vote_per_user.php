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
            $indexes = collect(DB::select("SHOW INDEX FROM votes"))->pluck('Key_name')->unique();

            // Drop FK qui s'appuient sur l'index avant de le supprimer
            $fks = collect(DB::select("
                SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'votes'
                  AND REFERENCED_TABLE_NAME IS NOT NULL
            "))->pluck('CONSTRAINT_NAME');

            foreach ($fks as $fk) {
                DB::statement("ALTER TABLE votes DROP FOREIGN KEY `{$fk}`");
            }

            // Colonne générée + nouvel index avant de drop l'ancien (MySQL exige qu'un index couvre la FK avant recréation)
            DB::statement("ALTER TABLE votes ADD COLUMN vote_date DATE GENERATED ALWAYS AS (DATE(created_at)) STORED");
            DB::statement("ALTER TABLE votes ADD UNIQUE KEY unique_vote_user_daily (participant_id, user_id, vote_date)");

            if ($indexes->contains('unique_vote_user')) {
                DB::statement('ALTER TABLE votes DROP INDEX unique_vote_user');
            }

            // Recréer FK
            DB::statement('ALTER TABLE votes ADD CONSTRAINT votes_participant_id_foreign FOREIGN KEY (participant_id) REFERENCES participants(id) ON DELETE CASCADE');
            DB::statement('ALTER TABLE votes ADD CONSTRAINT votes_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE');
            return;
        }

        if ($driver === 'sqlite') {
            // SQLite ne gère pas les unique multi-table proprement avec colonnes générées portables.
            // Approche : index unique sur expression.
            DB::statement('DROP INDEX IF EXISTS unique_vote_user');
            DB::statement("CREATE UNIQUE INDEX unique_vote_user_daily ON votes (participant_id, user_id, date(created_at))");
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE votes DROP CONSTRAINT IF EXISTS unique_vote_user');
            DB::statement("CREATE UNIQUE INDEX unique_vote_user_daily ON votes (participant_id, user_id, (created_at::date))");
            return;
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE votes DROP INDEX unique_vote_user_daily');
            DB::statement('ALTER TABLE votes DROP COLUMN vote_date');
            DB::statement('ALTER TABLE votes ADD UNIQUE KEY unique_vote_user (participant_id, user_id)');
            return;
        }

        if ($driver === 'sqlite' || $driver === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS unique_vote_user_daily');
            DB::statement("CREATE UNIQUE INDEX unique_vote_user ON votes (participant_id, user_id)");
            return;
        }
    }
};
