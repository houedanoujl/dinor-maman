<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('share_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ref_participant_id')->nullable()->constrained('participants')->nullOnDelete();
            $table->string('session_hash', 64)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('visited_at');
            $table->timestamps();

            $table->index(['participant_id', 'visited_at']);
            $table->index(['ref_participant_id', 'visited_at']);
            $table->unique(['participant_id', 'ref_participant_id', 'session_hash'], 'share_visits_unique_session_ref');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('share_visits');
    }
};
