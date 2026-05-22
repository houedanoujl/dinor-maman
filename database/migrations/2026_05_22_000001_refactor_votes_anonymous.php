<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Refactor votes pour vote anonyme (1 seul vote par concours, identifié par voter_token cookie).
 * Supprime user_id, ajoute voter_token, unique global sur voter_token.
 */
return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        // Reset votes existants (changement de modèle d'identification)
        DB::table('votes')->truncate();
        DB::table('participants')->update(['vote_count' => 0]);

        if ($driver === 'mysql') {
            // Drop FKs
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
            foreach (['unique_vote_user', 'unique_vote_user_daily', 'votes_user_id_foreign'] as $idx) {
                if ($indexes->contains($idx)) {
                    DB::statement("ALTER TABLE votes DROP INDEX `{$idx}`");
                }
            }

            $hasVoteDate = collect(DB::select("SHOW COLUMNS FROM votes LIKE 'vote_date'"))->isNotEmpty();
            if ($hasVoteDate) {
                DB::statement('ALTER TABLE votes DROP COLUMN vote_date');
            }

            $hasUserId = collect(DB::select("SHOW COLUMNS FROM votes LIKE 'user_id'"))->isNotEmpty();
            if ($hasUserId) {
                DB::statement('ALTER TABLE votes DROP COLUMN user_id');
            }
        }

        Schema::table('votes', function (Blueprint $table) use ($driver) {
            if (! Schema::hasColumn('votes', 'voter_token')) {
                $table->string('voter_token', 64)->nullable()->after('participant_id');
            }
        });

        // Unique global : 1 vote par voter_token sur tout le concours
        if ($driver === 'mysql') {
            $indexes = collect(DB::select("SHOW INDEX FROM votes"))->pluck('Key_name')->unique();
            if (! $indexes->contains('unique_vote_token')) {
                DB::statement('ALTER TABLE votes ADD UNIQUE KEY unique_vote_token (voter_token)');
            }
            // Recréer FK participant_id
            $fks = collect(DB::select("
                SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'votes'
                  AND REFERENCED_TABLE_NAME = 'participants'
            "))->pluck('CONSTRAINT_NAME');
            if ($fks->isEmpty()) {
                DB::statement('ALTER TABLE votes ADD CONSTRAINT votes_participant_id_foreign FOREIGN KEY (participant_id) REFERENCES participants(id) ON DELETE CASCADE');
            }
        } else {
            DB::statement('DROP INDEX IF EXISTS unique_vote_user');
            DB::statement('DROP INDEX IF EXISTS unique_vote_user_daily');
            DB::statement('CREATE UNIQUE INDEX unique_vote_token ON votes (voter_token)');
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            $indexes = collect(DB::select("SHOW INDEX FROM votes"))->pluck('Key_name')->unique();
            if ($indexes->contains('unique_vote_token')) {
                DB::statement('ALTER TABLE votes DROP INDEX unique_vote_token');
            }
        } else {
            DB::statement('DROP INDEX IF EXISTS unique_vote_token');
        }

        Schema::table('votes', function (Blueprint $table) {
            if (Schema::hasColumn('votes', 'voter_token')) {
                $table->dropColumn('voter_token');
            }
        });

        Schema::table('votes', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('participant_id')->constrained()->cascadeOnDelete();
            $table->unique(['participant_id', 'user_id'], 'unique_vote_user');
        });
    }
};
