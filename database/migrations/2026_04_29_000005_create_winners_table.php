<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('winners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rank');
            $table->unsignedInteger('vote_count_snapshot')->default(0);
            $table->timestamp('announced_at')->nullable();
            $table->string('contest_cycle', 20)->default(date('Y-m'));
            $table->timestamps();

            $table->unique(['participant_id', 'contest_cycle']);
            $table->unique(['rank', 'contest_cycle']);
            $table->index(['contest_cycle', 'rank']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('winners');
    }
};
