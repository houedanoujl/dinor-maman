<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $rows = [
            'contest.ends_at'        => '2026-05-28 12:00:00',
            'contest.upload_ends_at' => '2026-05-25 23:59:59',
            'contest.announce_at'    => '2026-05-28 15:00:00',
        ];

        foreach ($rows as $key => $value) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value, 'updated_at' => now(), 'created_at' => now()]
            );
        }

        Cache::forget('contest_ends_at');
        Cache::forget('contest_upload_ends_at');
    }

    public function down(): void
    {
        DB::table('settings')->whereIn('key', [
            'contest.ends_at',
            'contest.upload_ends_at',
            'contest.announce_at',
        ])->delete();

        Cache::forget('contest_ends_at');
        Cache::forget('contest_upload_ends_at');
    }
};
