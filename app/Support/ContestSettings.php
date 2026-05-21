<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ContestSettings
{
    private const KEY = 'contest.ends_at';
    private const KEY_UPLOAD_ENDS = 'contest.upload_ends_at';
    private const KEY_REGLEMENT = 'contest.reglement';
    private const KEY_FAQ = 'contest.faq';
    private const CACHE_KEY = 'contest_ends_at';
    private const CACHE_KEY_UPLOAD_ENDS = 'contest_upload_ends_at';
    private const CACHE_KEY_REGLEMENT = 'contest_reglement';
    private const CACHE_KEY_FAQ = 'contest_faq';

    public static function endsAt(): Carbon
    {
        $value = Cache::remember(self::CACHE_KEY, 3600, function () {
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
        $value = Cache::remember(self::CACHE_KEY_UPLOAD_ENDS, 3600, function () {
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

    public static function getReglement(): ?string
    {
        return Cache::remember(self::CACHE_KEY_REGLEMENT, 3600, function () {
            try {
                return DB::table('settings')->where('key', self::KEY_REGLEMENT)->value('value');
            } catch (\Throwable $e) {
                return null;
            }
        });
    }

    public static function setReglement(string $html): void
    {
        DB::table('settings')->updateOrInsert(
            ['key' => self::KEY_REGLEMENT],
            ['value' => $html, 'updated_at' => now(), 'created_at' => now()]
        );

        Cache::forget(self::CACHE_KEY_REGLEMENT);
    }

    /** FAQ : tableau de {q, a} stocké en JSON. */
    public static function getFaq(): array
    {
        $raw = Cache::remember(self::CACHE_KEY_FAQ, 3600, function () {
            try {
                return DB::table('settings')->where('key', self::KEY_FAQ)->value('value');
            } catch (\Throwable $e) {
                return null;
            }
        });

        if ($raw) {
            $decoded = json_decode($raw, true);
            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    public static function setFaq(array $items): void
    {
        DB::table('settings')->updateOrInsert(
            ['key' => self::KEY_FAQ],
            ['value' => json_encode($items, JSON_UNESCAPED_UNICODE), 'updated_at' => now(), 'created_at' => now()]
        );

        Cache::forget(self::CACHE_KEY_FAQ);
    }

}
