<!DOCTYPE html>
<html lang="fr" class="h-full bg-dinor-cream">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ $title ?? config('app.name', 'Un moment de cuisine avec maman') }}</title>
    <meta property="og:type" content="website" />
    <meta property="og:title" content="{{ $ogTitle ?? ($title ?? config('app.name', 'Un moment de cuisine avec maman')) }}" />
    <meta property="og:description" content="{{ $ogDescription ?? 'Concours photo culinaire: Un moment de cuisine avec maman.' }}" />
    <meta property="og:url" content="{{ $ogUrl ?? url()->current() }}" />
    <meta property="og:image" content="{{ $ogImage ?? asset('favicon.ico') }}" />
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|playfair-display:600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    @if($gaId = config('services.google.analytics_id'))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', @json($gaId), { anonymize_ip: true });
        </script>
    @endif
</head>
<body class="min-h-full pb-24 font-sans text-dinor-dark antialiased md:pb-0">
    @php
        $participantToken = session('participant_token');
        $currentParticipant = null;

        if ($participantToken) {
            $currentParticipant = \App\Models\Participant::where('dashboard_token', $participantToken)->first();

            if (! $currentParticipant) {
                session()->forget('participant_token');
                $participantToken = null;
            }
        }
    @endphp

    <header class="sticky top-0 z-30 border-b border-gray-100 bg-white/95 backdrop-blur" x-data="{ mobileOpen: false }">
        <div class="container mx-auto flex items-center justify-between gap-4 px-4 py-3">
            <x-dinor-logo />

            {{-- Nav desktop --}}
            <nav class="hidden items-center gap-1 text-sm font-medium md:flex sm:gap-3">
                <a href="{{ route('contest.gallery') }}"
                   class="px-3 py-2 font-semibold text-dinor-red transition hover:text-dinor-red
                          @if(request()->routeIs('contest.gallery')) border-b-2 border-dinor-red @endif">
                    Galerie
                </a>
                @if($contestEnded)
                    <a href="{{ route('winners.index') }}"
                       class="inline-flex items-center justify-center rounded-full bg-dinor-gold px-5 py-2 font-semibold text-white shadow-sm transition hover:bg-dinor-gold/90">
                        Gagnants
                    </a>
                @elseif(! auth()->check() && ! session('participant_token'))
                    <a href="{{ route('register') }}"
                       class="inline-flex items-center justify-center rounded-full bg-dinor-red px-4 py-2 font-semibold text-white shadow-sm transition hover:bg-dinor-red/90">
                        S'inscrire
                    </a>
                    <a href="{{ route('login') }}"
                       class="px-3 py-2 font-semibold text-gray-600 transition hover:text-dinor-red">
                        Se connecter
                    </a>
                @endif
                <x-user-menu />
            </nav>

            {{-- Bouton hamburger mobile --}}
            <button type="button"
                    x-on:click="mobileOpen = !mobileOpen"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 text-dinor-dark transition hover:border-dinor-red hover:text-dinor-red md:hidden"
                    :aria-expanded="mobileOpen.toString()"
                    aria-label="Menu">
                <svg x-show="!mobileOpen" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <svg x-show="mobileOpen" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Drawer mobile --}}
        <div x-show="mobileOpen"
             x-transition:enter="transition duration-200 ease-out"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-cloak
             class="border-t border-gray-100 bg-white md:hidden">
            <nav class="container mx-auto flex flex-col gap-1 px-4 py-3 text-sm font-medium">

                @php
                    $authUser = auth()->user();
                    $userParticipant = $currentParticipant ?? ($authUser ? \App\Models\Participant::where('user_id', $authUser->id)->first() : null);
                @endphp

                @if($authUser || $userParticipant)
                    {{-- Section infos compte --}}
                    <div class="mb-2 rounded-xl bg-dinor-cream/50 px-4 py-3">
                        <p class="text-xs uppercase tracking-wide text-gray-400">Connecté</p>
                        <p class="truncate text-sm font-semibold text-dinor-dark">
                            {{ $authUser?->name ?? $userParticipant?->full_name }}
                        </p>
                        @if($authUser)
                            <p class="truncate text-xs text-gray-500">{{ $authUser->email }}</p>
                            <span class="mt-1 inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold
                                  {{ $authUser->isAdmin() ? 'bg-purple-50 text-purple-700' : ($authUser->isParticipant() ? 'bg-dinor-gold/10 text-dinor-gold' : 'bg-dinor-red/10 text-dinor-red') }}">
                                {{ $authUser->isAdmin() ? 'Admin' : ($authUser->isParticipant() ? 'Participant' : 'Votant') }}
                            </span>
                        @endif
                    </div>
                @endif

                <a href="{{ route('home') }}"
                   class="flex items-center gap-3 rounded-xl px-4 py-3 text-dinor-dark transition hover:bg-dinor-cream hover:text-dinor-red {{ request()->routeIs('home') ? 'bg-dinor-cream text-dinor-red' : '' }}">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-8 9 8M5 10v10h14V10"/></svg>
                    Accueil
                </a>

                <a href="{{ route('contest.gallery') }}"
                   class="flex items-center gap-3 rounded-xl px-4 py-3 text-dinor-dark transition hover:bg-dinor-cream hover:text-dinor-red {{ request()->routeIs('contest.gallery') ? 'bg-dinor-cream text-dinor-red' : '' }}">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 5h16v14H4z M4 15l4.5-4.5 3 3L14 11l6 6"/></svg>
                    Galerie
                </a>

                @if($contestEnded)
                    <a href="{{ route('winners.index') }}"
                       class="flex items-center gap-3 rounded-xl bg-dinor-gold px-4 py-3 font-semibold text-white shadow-sm">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 21h8M12 17v4M7 4h10v4a5 5 0 0 1-10 0z"/></svg>
                        Gagnants
                    </a>
                @elseif($uploadPhase && (! $authUser || ! $userParticipant))
                    <a href="{{ route('register', ['role' => 'participant']) }}"
                       class="flex items-center gap-3 rounded-xl bg-dinor-red px-4 py-3 font-semibold text-white shadow-sm">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg>
                        Participer
                    </a>
                @endif

                @if($userParticipant)
                    <a href="{{ $userParticipant->dashboard_url }}"
                       class="flex items-center gap-3 rounded-xl px-4 py-3 text-dinor-dark transition hover:bg-dinor-cream hover:text-dinor-red">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8z M4 21a8 8 0 0 1 16 0"/></svg>
                        Mon espace
                    </a>
                    @if($userParticipant->status === \App\Models\Participant::STATUS_APPROVED)
                        <a href="{{ route('participant.show', $userParticipant) }}"
                           class="flex items-center gap-3 rounded-xl px-4 py-3 text-dinor-dark transition hover:bg-dinor-cream hover:text-dinor-red">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            Ma page publique
                        </a>
                    @endif
                @endif

                @if($authUser?->isVoter() && ! $userParticipant)
                    <a href="{{ route('register', ['role' => 'participant']) }}"
                       class="flex items-center gap-3 rounded-xl border-2 border-dinor-gold/40 bg-dinor-gold/5 px-4 py-3 font-semibold text-dinor-gold transition hover:bg-dinor-gold/10">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/></svg>
                        Devenir participant
                    </a>
                @endif

                @if($authUser?->isAdmin())
                    <a href="/admin"
                       class="flex items-center gap-3 rounded-xl px-4 py-3 text-dinor-dark transition hover:bg-dinor-cream hover:text-dinor-red">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        Administration
                    </a>
                @endif

                <div class="my-2 border-t border-gray-100"></div>

                <a href="{{ route('reglement') }}"
                   class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm text-gray-600 transition hover:bg-dinor-cream hover:text-dinor-red">
                    Règlement
                </a>
                <a href="{{ route('faq') }}"
                   class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm text-gray-600 transition hover:bg-dinor-cream hover:text-dinor-red">
                    FAQ
                </a>
                <a href="{{ route('cgu') }}"
                   class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm text-gray-600 transition hover:bg-dinor-cream hover:text-dinor-red">
                    CGU
                </a>

                @if(! $authUser && ! $userParticipant)
                    <div class="mt-2 flex flex-col gap-2 border-t border-gray-100 pt-3">
                        <a href="{{ route('register') }}"
                           class="flex items-center justify-center gap-2 rounded-full bg-dinor-red px-4 py-3 font-semibold text-white shadow-sm">
                            S'inscrire
                        </a>
                        <a href="{{ route('login') }}"
                           class="flex items-center justify-center gap-2 rounded-full border border-gray-200 px-4 py-3 font-semibold text-dinor-dark">
                            Se connecter
                        </a>
                    </div>
                @endif

                @if($authUser)
                    <form method="POST" action="{{ route('logout') }}" class="mt-2 border-t border-gray-100 pt-3">
                        @csrf
                        <button type="submit"
                                class="flex w-full items-center gap-3 rounded-xl px-4 py-3 text-left text-sm font-semibold text-red-600 transition hover:bg-red-50">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            Se déconnecter
                        </button>
                    </form>
                @elseif($userParticipant)
                    <form method="POST" action="{{ route('participant.logout') }}" class="mt-2 border-t border-gray-100 pt-3">
                        @csrf
                        <button type="submit"
                                class="flex w-full items-center gap-3 rounded-xl px-4 py-3 text-left text-sm font-semibold text-red-600 transition hover:bg-red-50">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            Se déconnecter
                        </button>
                    </form>
                @endif
            </nav>
        </div>
    </header>

    <main>
        @if($votePhase && $hasSeparateVotePhase)
            <div class="bg-amber-50 border-b border-amber-200">
                <div class="container mx-auto px-4 py-2 text-center text-xs font-semibold text-amber-800">
                    Phase de vote en cours — les nouvelles inscriptions et modifications de photos sont clôturées. Continuez à voter jusqu'au {{ \Illuminate\Support\Carbon::parse($contestEndsAt)->isoFormat('D MMM à HH[h]mm') }}.
                </div>
            </div>
        @endif
        @if(session('status'))
            <div class="container mx-auto px-4 pt-4">
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ session('status') }}
                </div>
            </div>
        @endif
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    <footer class="mt-16 bg-dinor-dark text-white">
        <div class="container mx-auto px-4 py-12">
            <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-3">

                {{-- Branding --}}
                <div class="flex flex-col gap-4">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-3 group">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-dinor-red shadow-lg">
                            <svg viewBox="0 0 32 32" class="h-6 w-6" fill="white" aria-hidden="true">
                                <path d="M16 27 C16 27 4 19.5 4 11.5 C4 7.9 6.9 5 10.5 5 C12.6 5 14.5 6.1 16 7.8 C17.5 6.1 19.4 5 21.5 5 C25.1 5 28 7.9 28 11.5 C28 19.5 16 27 16 27Z" opacity="0.3"/>
                                <line x1="11" y1="8" x2="10" y2="24" stroke="white" stroke-width="1.6" stroke-linecap="round"/>
                                <line x1="9.5" y1="8" x2="9.5" y2="13" stroke="white" stroke-width="1.3" stroke-linecap="round"/>
                                <line x1="11" y1="8" x2="11" y2="13" stroke="white" stroke-width="1.3" stroke-linecap="round"/>
                                <line x1="12.5" y1="8" x2="12.5" y2="13" stroke="white" stroke-width="1.3" stroke-linecap="round"/>
                                <ellipse cx="21" cy="10" rx="2" ry="2.5"/>
                                <line x1="21" y1="12.5" x2="21" y2="24" stroke="white" stroke-width="1.6" stroke-linecap="round"/>
                            </svg>
                        </span>
                        <span class="font-display text-sm font-bold leading-tight text-white">
                            Un moment de<br>cuisine avec maman
                        </span>
                    </a>
                    <p class="max-w-xs text-sm leading-relaxed text-white/60">
                        Partagez votre plus beau moment en cuisine, invitez vos proches à voter et célébrez les recettes familiales.
                    </p>
                    <p class="text-xs text-white/40">&copy; {{ date('Y') }} Dinor. Tous droits réservés.</p>
                </div>

                {{-- Navigation --}}
                <div>
                    <p class="mb-4 text-xs font-bold uppercase tracking-widest text-dinor-gold">Navigation</p>
                    <ul class="flex flex-col gap-2.5 text-sm text-white/70">
                        <li><a href="{{ route('home') }}" class="transition hover:text-white">Accueil</a></li>
                        <li><a href="{{ route('contest.gallery') }}" class="transition hover:text-white">Galerie photos</a></li>
                        @if($uploadPhase && !$contestEnded)
                            <li><a href="{{ route('register', ['role' => 'participant']) }}" class="transition hover:text-white">Participer</a></li>
                        @endif
                        @if($contestEnded)
                            <li><a href="{{ route('winners.index') }}" class="transition hover:text-white">Gagnants</a></li>
                        @endif
                        <li><a href="{{ route('participant.login') }}" class="transition hover:text-white">Mon espace</a></li>
                    </ul>
                </div>

                {{-- Infos --}}
                <div>
                    <p class="mb-4 text-xs font-bold uppercase tracking-widest text-dinor-gold">Informations</p>
                    <ul class="flex flex-col gap-2.5 text-sm text-white/70">
                        <li><a href="{{ route('reglement') }}" class="transition hover:text-white">Règlement du concours</a></li>
                        <li><a href="{{ route('faq') }}" class="transition hover:text-white">Foire aux questions</a></li>
                        <li><a href="{{ route('cgu') }}" class="transition hover:text-white">CGU</a></li>
                    </ul>
                    @if(!$contestEnded)
                        <div x-data="countdown('{{ $contestEndsAt }}')" class="mt-6 inline-flex items-center gap-2 rounded-lg border border-dinor-gold/30 bg-dinor-gold/10 px-3 py-2 text-xs text-dinor-gold">
                            <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                <circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>
                            </svg>
                            <span class="font-semibold">Fin dans&nbsp;<span class="font-bold" x-text="label"></span></span>
                        </div>
                    @endif
                </div>

            </div>
        </div>

        {{-- Bottom bar --}}
        <div class="border-t border-white/10">
            <div class="container mx-auto flex flex-wrap items-center justify-between gap-3 px-4 py-4 text-xs text-white/40">
                <span>Concours photo culinaire — Édition {{ date('Y') }}</span>
                <span>Propulsé par <span class="text-dinor-gold font-semibold">Dinor</span></span>
            </div>
        </div>
    </footer>

    <nav class="fixed inset-x-0 bottom-0 z-40 border-t border-gray-100 bg-white/95 px-3 pb-[calc(0.75rem+env(safe-area-inset-bottom))] pt-2 shadow-[0_-10px_30px_rgba(0,0,0,0.08)] backdrop-blur md:hidden" aria-label="Navigation mobile">
        <div class="mx-auto grid max-w-md grid-cols-3 gap-1">
            <a href="{{ route('contest.gallery') }}"
               class="flex min-h-14 flex-col items-center justify-center gap-1 rounded-xl px-2 py-1 text-[11px] font-semibold transition {{ request()->routeIs('contest.gallery') ? 'bg-dinor-red/10 text-dinor-red' : 'text-gray-500 hover:bg-dinor-cream hover:text-dinor-red' }}"
               @if(request()->routeIs('contest.gallery')) aria-current="page" @endif>
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 5h16v14H4z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 15l4.5-4.5 3 3L14 11l6 6" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.5 8.5h.01" />
                </svg>
                <span>Galerie</span>
            </a>

            @if($contestEnded)
                <a href="{{ route('winners.index') }}"
                   class="flex min-h-14 flex-col items-center justify-center gap-1 rounded-xl px-2 py-1 text-[11px] font-semibold transition {{ request()->routeIs('winners.index') ? 'bg-dinor-red/10 text-dinor-red' : 'text-gray-500 hover:bg-dinor-cream hover:text-dinor-red' }}"
                   @if(request()->routeIs('winners.index')) aria-current="page" @endif>
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 21h8" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 17v4" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 4h10v4a5 5 0 0 1-10 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 5H3v2a3 3 0 0 0 4 2.83" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 5h2v2a3 3 0 0 1-4 2.83" />
                    </svg>
                    <span>Gagnants</span>
                </a>
            @elseif($uploadPhase)
                <a href="{{ route('register', ['role' => 'participant']) }}"
                   class="flex min-h-14 flex-col items-center justify-center gap-1 rounded-xl px-2 py-1 text-[11px] font-semibold transition {{ request()->routeIs('register') ? 'bg-dinor-red/10 text-dinor-red' : 'text-gray-500 hover:bg-dinor-cream hover:text-dinor-red' }}"
                   @if(request()->routeIs('register')) aria-current="page" @endif>
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" />
                    </svg>
                    <span>Participer</span>
                </a>
            @else
                <a href="{{ route('home') }}"
                   class="flex min-h-14 flex-col items-center justify-center gap-1 rounded-xl px-2 py-1 text-[11px] font-semibold transition {{ request()->routeIs('home') ? 'bg-dinor-red/10 text-dinor-red' : 'text-gray-500 hover:bg-dinor-cream hover:text-dinor-red' }}"
                   @if(request()->routeIs('home')) aria-current="page" @endif>
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-8 9 8" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 10v10h14V10" />
                    </svg>
                    <span>Accueil</span>
                </a>
            @endif

            @if($currentParticipant)
                <a href="{{ $currentParticipant->dashboard_url }}"
                   class="flex min-h-14 flex-col items-center justify-center gap-1 rounded-xl px-2 py-1 text-[11px] font-semibold transition {{ request()->routeIs('participant.dashboard') ? 'bg-dinor-red/10 text-dinor-red' : 'text-gray-500 hover:bg-dinor-cream hover:text-dinor-red' }}"
                   @if(request()->routeIs('participant.dashboard')) aria-current="page" @endif>
                    @php($avatar = $currentParticipant->getFirstMediaUrl('photo', 'thumb'))
                    @if($avatar)
                        <img src="{{ $avatar }}" alt="" class="h-5 w-5 rounded-full object-cover" />
                    @else
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 21a8 8 0 0 1 16 0" />
                        </svg>
                    @endif
                    <span>Mon espace</span>
                </a>
            @else
                <a href="{{ route('participant.login') }}"
                   class="flex min-h-14 flex-col items-center justify-center gap-1 rounded-xl px-2 py-1 text-[11px] font-semibold transition {{ request()->routeIs('participant.login') ? 'bg-dinor-red/10 text-dinor-red' : 'text-gray-500 hover:bg-dinor-cream hover:text-dinor-red' }}"
                   @if(request()->routeIs('participant.login')) aria-current="page" @endif>
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 17l5-5-5-5" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12H3" />
                    </svg>
                    <span>Connexion</span>
                </a>
            @endif
        </div>
    </nav>

    <div x-data="toastBus()" x-on:toast.window="push($event.detail)" class="pointer-events-none fixed bottom-28 right-4 z-50 flex w-[92vw] max-w-sm flex-col gap-2 md:bottom-4">
        <template x-for="item in items" :key="item.id">
            <div class="pointer-events-auto rounded-xl border px-4 py-3 text-sm shadow-lg"
                 :class="item.type === 'success' ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : (item.type === 'warning' ? 'border-amber-200 bg-amber-50 text-amber-800' : 'border-red-200 bg-red-50 text-red-800')">
                <p x-text="item.message"></p>
            </div>
        </template>
    </div>

    <script>
        function toastBus() {
            return {
                items: [],
                push(detail) {
                    const id = Date.now() + Math.random();
                    this.items.push({ id, type: detail?.type || 'success', message: detail?.message || '' });
                    setTimeout(() => {
                        this.items = this.items.filter((x) => x.id !== id);
                    }, 3500);
                }
            };
        }

        function countdown(endsAt) {
            return {
                label: '',
                tick() {
                    const end = new Date(endsAt.replace(' ', 'T'));
                    const now = new Date();
                    const diff = end - now;
                    if (diff <= 0) {
                        this.label = 'Terminé';
                        return;
                    }

                    const d = Math.floor(diff / (1000 * 60 * 60 * 24));
                    const h = Math.floor((diff / (1000 * 60 * 60)) % 24);
                    const m = Math.floor((diff / (1000 * 60)) % 60);
                    this.label = `${d}j ${h}h ${m}m`;
                },
                init() {
                    this.tick();
                    setInterval(() => this.tick(), 60000);
                }
            };
        }
    </script>

    @livewireScripts
</body>
</html>
