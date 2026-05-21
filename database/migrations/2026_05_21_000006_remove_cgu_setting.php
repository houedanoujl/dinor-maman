<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')->where('key', 'contest.cgu')->delete();
        Cache::forget('contest_cgu');
    }

    public function down(): void
    {
        // Pas de restauration — contenu supprimé volontairement.
    }
};
