<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Corrige unique constraint votes: 1 vote par (participant, voter_token), pas global.
 * Permet à un visiteur de voter pour plusieurs participants, mais 1 seule fois par participant.
 */
return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            $indexes = collect(DB::select("SHOW INDEX FROM votes"))->pluck('Key_name')->unique();

            if ($indexes->contains('unique_vote_token')) {
                DB::statement('ALTER TABLE votes DROP INDEX unique_vote_token');
            }

            if (! $indexes->contains('unique_vote_participant_token')) {
                DB::statement('ALTER TABLE votes ADD UNIQUE KEY unique_vote_participant_token (participant_id, voter_token)');
            }
        } else {
            DB::statement('DROP INDEX IF EXISTS unique_vote_token');
            DB::statement('CREATE UNIQUE INDEX unique_vote_participant_token ON votes (participant_id, voter_token)');
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            $indexes = collect(DB::select("SHOW INDEX FROM votes"))->pluck('Key_name')->unique();
            if ($indexes->contains('unique_vote_participant_token')) {
                DB::statement('ALTER TABLE votes DROP INDEX unique_vote_participant_token');
            }
            DB::statement('ALTER TABLE votes ADD UNIQUE KEY unique_vote_token (voter_token)');
        } else {
            DB::statement('DROP INDEX IF EXISTS unique_vote_participant_token');
            DB::statement('CREATE UNIQUE INDEX unique_vote_token ON votes (voter_token)');
        }
    }
};
