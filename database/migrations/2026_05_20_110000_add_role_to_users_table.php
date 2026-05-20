<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('voter')->after('email');
            $table->string('phone', 20)->nullable()->after('role');
            $table->timestamp('phone_verified_at')->nullable()->after('phone');
            $table->index('role');
        });

        // Les admins existants gardent l'accès Filament. Marquer comme admin.
        DB::table('users')->update(['role' => 'admin']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropColumn(['role', 'phone', 'phone_verified_at']);
        });
    }
};
