<?php

use App\Models\Participant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->string('dashboard_token', 64)->nullable()->unique()->after('email');
        });

        // Backfill pour les participants existants
        DB::table('participants')->whereNull('dashboard_token')->orderBy('id')->each(function ($row) {
            DB::table('participants')->where('id', $row->id)->update([
                'dashboard_token' => Str::random(40),
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->dropUnique(['dashboard_token']);
            $table->dropColumn('dashboard_token');
        });
    }
};
