<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Garantit que chaque visiteur dispose d'un voter_token cohérent.
 * Lit cookie existant OU génère un nouveau token unique pour la requête,
 * expose via $request->attributes->get('voter_token') et $request->cookie(NAME) en mémoire.
 *
 * Évite la race où plusieurs instances Livewire généraient chacune leur token,
 * le dernier Cookie::queue() écrasant les précédents → vote enregistré avec un token
 * différent de celui finalement stocké côté client.
 */
class EnsureVoterToken
{
    public const COOKIE_NAME = 'dinor_voter';
    public const COOKIE_MINUTES = 60 * 24 * 365;

    public function handle(Request $request, Closure $next): Response
    {
        $token = (string) $request->cookie(self::COOKIE_NAME, '');

        if ($token === '') {
            $token = (string) Str::ulid() . bin2hex(random_bytes(8));
            // Override request cookie pour cette requête (lecture immédiate par composants)
            $request->cookies->set(self::COOKIE_NAME, $token);
            // Queue cookie pour response (Laravel l'envoie via Set-Cookie)
            Cookie::queue(self::COOKIE_NAME, $token, self::COOKIE_MINUTES);
        }

        $request->attributes->set('voter_token', $token);

        return $next($request);
    }
}
