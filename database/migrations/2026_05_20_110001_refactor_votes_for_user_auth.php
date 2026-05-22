<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Reset des votes anonymes existants (décision produit : vote = compte requis)
        DB::table('votes')->truncate();
        DB::table('participants')->update(['vote_count' => 0]);

        $indexes = collect(Schema::getIndexes('votes'))->pluck('name')->unique();

        // Drop FK pour pouvoir drop les uniques qui la couvrent
        Schema::table('votes', function (Blueprint $table) {
            $table->dropForeign(['participant_id']);
        });

        Schema::table('votes', function (Blueprint $table) use ($indexes) {
            if ($indexes->contains('unique_vote_ip')) {
                $table->dropUnique('unique_vote_ip');
            }
            if ($indexes->contains('unique_vote_session')) {
                $table->dropUnique('unique_vote_session');
            }
            if ($indexes->contains('votes_device_fingerprint_index')) {
                $table->dropIndex(['device_fingerprint']);
            }
        });

        Schema::table('votes', function (Blueprint $table) {
            // Réajouter FK participant_id
            $table->foreign('participant_id')->references('id')->on('participants')->cascadeOnDelete();

            $table->foreignId('user_id')->nullable()->after('participant_id')->constrained()->cascadeOnDelete();
            $table->unique(['participant_id', 'user_id'], 'unique_vote_user');
        });

        // Lier participant à un user (nullable pour rétro-compat). Sera rempli quand
        // un user_id existe avec ce phone, ou laissé null pour les anciens participants.
        Schema::table('participants', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('votes', function (Blueprint $table) {
            $table->dropUnique('unique_vote_user');
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');

            $table->unique(['participant_id', 'ip_address'], 'unique_vote_ip');
            $table->unique(['participant_id', 'session_id'], 'unique_vote_session');
            $table->index('device_fingerprint');
        });
    }
};
