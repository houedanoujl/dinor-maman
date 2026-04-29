<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ContestSettings
{
    private const KEY = 'contest.ends_at';
    private const KEY_UPLOAD_ENDS = 'contest.upload_ends_at';
    private const CACHE_KEY = 'contest_ends_at';
    private const CACHE_KEY_UPLOAD_ENDS = 'contest_upload_ends_at';

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

    /**
     * Date de fin de la phase d'upload. Jusqu'à cette date les participants
     * peuvent soumettre et modifier leur photo. Au-delà : phase de vote
     * uniquement (modifications bloquées) jusqu'à endsAt().
     * Par défaut = endsAt() (pas de phase de vote séparée).
     */
    public static function uploadEndsAt(): Carbon
    {
        $value = Cache::rememberForever(self::CACHE_KEY_UPLOAD_ENDS, function () {
            try {
                $row = DB::table('settings')->where('key', self::KEY_UPLOAD_ENDS)->value('value');
            } catch (\Throwable $e) {
                $row = null;
            }

            return $row ?: null;
        });

        return $value ? Carbon::parse($value) : self::endsAt();
    }

    public static function setUploadEndsAt(string|Carbon|null $value): void
    {
        if ($value === null || $value === '') {
            DB::table('settings')->where('key', self::KEY_UPLOAD_ENDS)->delete();
        } else {
            $value = $value instanceof Carbon ? $value->toDateTimeString() : $value;

            DB::table('settings')->updateOrInsert(
                ['key' => self::KEY_UPLOAD_ENDS],
                ['value' => $value, 'updated_at' => now(), 'created_at' => now()]
            );
        }

        Cache::forget(self::CACHE_KEY_UPLOAD_ENDS);
    }

    /** Phase d'upload : soumission et modification de photos autorisées. */
    public static function isUploadPhase(): bool
    {
        return now()->lessThanOrEqualTo(self::uploadEndsAt())
            && now()->lessThanOrEqualTo(self::endsAt());
    }

    /** Phase de vote uniquement : uploads bloqués, votes ouverts. */
    public static function isVotePhase(): bool
    {
        return now()->greaterThan(self::uploadEndsAt())
            && now()->lessThanOrEqualTo(self::endsAt());
    }

    /** Concours terminé. */
    public static function isEnded(): bool
    {
        return now()->greaterThan(self::endsAt());
    }
}
