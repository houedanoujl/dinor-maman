<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 20);
            $table->string('type', 32);
            $table->string('provider', 32)->default('twilio');
            $table->string('status', 16)->default('sent');
            $table->text('message')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->unique(['phone', 'type'], 'sms_logs_phone_type_unique');
            $table->index('sent_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_logs');
    }
};
