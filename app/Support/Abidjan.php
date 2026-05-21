<?php

namespace App\Support;

class Abidjan
{
    /** Toutes les communes (principales + périphériques). */
    public static function communes(): array
    {
        $principales = array_column(config('abidjan.communes_principales', []), 'nom');
        $peripheriques = array_column(config('abidjan.communes_peripheriques', []), 'nom');

        return array_values(array_merge($principales, $peripheriques));
    }

    /** Map commune => quartiers[]. */
    public static function quartiersByCommune(): array
    {
        $map = [];
        foreach (config('abidjan.communes_principales', []) as $c) {
            $map[$c['nom']] = $c['quartiers'];
        }
        foreach (config('abidjan.communes_peripheriques', []) as $c) {
            $map[$c['nom']] = $c['quartiers'];
        }
        return $map;
    }

    /** Quartiers d'une commune donnée. */
    public static function quartiers(string $commune): array
    {
        return self::quartiersByCommune()[$commune] ?? [];
    }

    /** Liste plate "Commune - Quartier" → utile pour selects globaux. */
    public static function flat(): array
    {
        $out = [];
        foreach (self::quartiersByCommune() as $commune => $quartiers) {
            foreach ($quartiers as $q) {
                $label = "{$commune} - {$q}";
                $out[$label] = $label;
            }
        }
        return $out;
    }

    /** Map options compatible Filament/Blade <option>. */
    public static function communesOptions(): array
    {
        return array_combine(self::communes(), self::communes());
    }
}
