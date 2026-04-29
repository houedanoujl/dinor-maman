<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ContestSettings
{
    private const KEY = 'contest.ends_at';
    private const CACHE_KEY = 'contest_ends_at';

    public static function endsAt(): Carbon
    {
        $value = Cache::rememberForever(self::CACHE_KEY, function () {
            try {
                $row = DB::table('settings')->where('key', self::KEY)->value('value');
            } catch (\Throwable $e) {
                $row = null;
            }

            return $row ?: (string) config('contest.ends_at');
        });

        return Carbon::parse($value);
    }

    public static function setEndsAt(string|Carbon $value): void
    {
        $value = $value instanceof Carbon ? $value->toDateTimeString() : $value;

        DB::table('settings')->updateOrInsert(
            ['key' => self::KEY],
            ['value' => $value, 'updated_at' => now(), 'created_at' => now()]
        );

        Cache::forget(self::CACHE_KEY);
    }
}
