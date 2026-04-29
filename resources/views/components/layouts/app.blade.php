<!DOCTYPE html>
<html lang="fr" class="h-full bg-dinor-cream">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ $title ?? config('app.name', 'Dinor — cuisine avec maman') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|playfair-display:600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-full font-sans text-dinor-dark antialiased">
    <header class="sticky top-0 z-30 border-b border-gray-100 bg-white/90 backdrop-blur">
        <div class="container mx-auto flex items-center justify-between gap-4 px-4 py-3">
            <x-dinor-logo />
            <nav class="flex items-center gap-1 text-sm font-medium sm:gap-4">
                <a href="{{ route('contest.gallery') }}" class="rounded-lg px-3 py-1.5 text-dinor-dark transition hover:text-dinor-red">Galerie</a>
                <a href="{{ route('contest.form') }}" class="btn-dinor !py-2 !px-4 text-sm">Participer</a>
            </nav>
        </div>
    </header>

    <main>
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    <footer class="mt-16 border-t border-gray-100 bg-white py-8">
        <div class="container mx-auto flex flex-col items-center gap-3 px-4 text-center text-sm text-gray-500 sm:flex-row sm:justify-between sm:text-left">
            <span class="inline-flex items-center gap-2">
                <svg viewBox="0 0 24 24" class="h-4 w-4 text-dinor-gold" fill="currentColor" aria-hidden="true">
                    <path d="M11 2v8.59l-2.3-2.3-1.4 1.42L12 14.41l4.7-4.7-1.4-1.42-2.3 2.3V2zM4 16h16v2H4z" />
                </svg>
                &copy; {{ date('Y') }} Dinor — un concept de cuisine avec maman
            </span>
            <span class="text-xs text-gray-400">Concours photo</span>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
