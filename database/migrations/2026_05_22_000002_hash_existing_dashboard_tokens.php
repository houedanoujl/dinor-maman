<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Hash existing plaintext tokens. Tokens en transit deviennent invalides,
        // mais c'est acceptable: nouveaux liens régénérés par participant login.
        DB::table('participants')
            ->whereNotNull('dashboard_token')
            ->orderBy('id')
            ->each(function ($row) {
                $token = (string) $row->dashboard_token;
                // Already-hashed tokens are 64 hex chars; skip those.
                if (strlen($token) === 64 && ctype_xdigit($token)) {
                    return;
                }
                DB::table('participants')
                    ->where('id', $row->id)
                    ->update(['dashboard_token' => hash('sha256', $token)]);
            });
    }

    public function down(): void
    {
        // Non réversible (one-way hash).
    }
};
