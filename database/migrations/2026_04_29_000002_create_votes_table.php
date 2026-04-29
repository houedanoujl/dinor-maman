<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_id')->constrained()->cascadeOnDelete();
            $table->string('ip_address', 45);
            $table->string('session_id', 64);
            $table->string('device_fingerprint', 64)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            // Anti-triche : 1 vote par couple (participant, IP) ET (participant, session)
            $table->unique(['participant_id', 'ip_address'], 'unique_vote_ip');
            $table->unique(['participant_id', 'session_id'], 'unique_vote_session');
            $table->index('device_fingerprint');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};
