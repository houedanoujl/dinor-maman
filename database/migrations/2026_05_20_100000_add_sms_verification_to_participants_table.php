<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->string('sms_code', 6)->nullable()->after('phone');
            $table->timestamp('sms_code_expires_at')->nullable()->after('sms_code');
            $table->timestamp('phone_verified_at')->nullable()->after('sms_code_expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->dropColumn(['sms_code', 'sms_code_expires_at', 'phone_verified_at']);
        });
    }
};
