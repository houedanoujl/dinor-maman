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
<body class="min-h-full font-sans text-dinor-dark antialiased">
    <header class="sticky top-0 z-30 border-b border-gray-100 bg-white/95 backdrop-blur">
        <div class="container mx-auto flex items-center justify-between gap-4 px-4 py-3">
            <x-dinor-logo />
            <nav class="flex items-center gap-1 text-sm font-medium sm:gap-3">
                <a href="{{ route('contest.gallery') }}"
                   class="px-3 py-2 font-semibold text-dinor-red transition hover:text-dinor-red
                          @if(request()->routeIs('contest.gallery')) border-b-2 border-dinor-red @endif">
                    Galerie
                </a>
                <a href="{{ route('contest.form') }}"
                   class="inline-flex items-center justify-center rounded-full bg-dinor-red px-5 py-2 font-semibold text-white shadow-sm transition hover:bg-dinor-red/90">
                    Participer
                </a>
            </nav>
        </div>
    </header>

    <main>
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

    <div x-data="toastBus()" x-on:toast.window="push($event.detail)" class="pointer-events-none fixed bottom-4 right-4 z-50 flex w-[92vw] max-w-sm flex-col gap-2">
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
