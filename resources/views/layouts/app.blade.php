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

    <header class="sticky top-0 z-30 border-b border-gray-100 bg-white/95 backdrop-blur">
        <div class="container mx-auto flex items-center justify-between gap-4 px-4 py-3">
            <x-dinor-logo />
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
                @elseif(! session('participant_token'))
                    @if($uploadPhase)
                        <a href="{{ route('contest.form') }}"
                           class="inline-flex items-center justify-center rounded-full bg-dinor-red px-4 py-2 font-semibold text-white shadow-sm transition hover:bg-dinor-red/90">
                            Participer
                        </a>
                    @endif
                    <a href="{{ route('participant.login') }}"
                       class="inline-flex items-center justify-center rounded-full border border-dinor-red px-4 py-2 font-semibold text-dinor-red transition hover:bg-dinor-red hover:text-white">
                        Se connecter
                    </a>
                @endif
                <x-user-menu />
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

    <footer class="mt-16 border-t border-gray-100 bg-white py-8">
        <div class="container mx-auto flex flex-col items-center gap-2 px-4 text-center text-sm text-gray-500">
            <span class="inline-flex items-center justify-center">
                <svg viewBox="0 0 32 32" class="h-6 w-6 text-dinor-gold" fill="currentColor" aria-hidden="true">
                    <path d="M16 27 C16 27 4 19.5 4 11.5 C4 7.9 6.9 5 10.5 5 C12.6 5 14.5 6.1 16 7.8 C17.5 6.1 19.4 5 21.5 5 C25.1 5 28 7.9 28 11.5 C28 19.5 16 27 16 27Z" fill="currentColor" opacity="0.3"/>
                    <line x1="11" y1="9" x2="10.2" y2="23" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                    <line x1="9.5" y1="9" x2="9.5" y2="14" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>
                    <line x1="11" y1="9" x2="11" y2="14" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>
                    <line x1="12.5" y1="9" x2="12.5" y2="14" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>
                    <ellipse cx="21" cy="11" rx="2" ry="2.5" fill="currentColor"/>
                    <line x1="21" y1="13.5" x2="21" y2="23" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                </svg>
            </span>
            <span>&copy; {{ date('Y') }} Un moment de cuisine avec maman</span>
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
                <a href="{{ route('contest.form') }}"
                   class="flex min-h-14 flex-col items-center justify-center gap-1 rounded-xl px-2 py-1 text-[11px] font-semibold transition {{ request()->routeIs('contest.form') ? 'bg-dinor-red/10 text-dinor-red' : 'text-gray-500 hover:bg-dinor-cream hover:text-dinor-red' }}"
                   @if(request()->routeIs('contest.form')) aria-current="page" @endif>
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
