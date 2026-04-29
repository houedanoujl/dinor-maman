<!DOCTYPE html>
<html lang="fr" class="h-full bg-dinor-cream">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ $title ?? 'Dinor — Un moment de cuisine avec maman' }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=poppins:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-full font-sans text-dinor-dark antialiased">
    <header class="bg-white shadow-sm">
        <div class="container mx-auto px-4 py-3 flex items-center justify-between">
            <a href="{{ route('home') }}" class="text-2xl font-extrabold text-dinor-red">Dinor</a>
            <nav class="flex gap-4 text-sm font-medium">
                <a href="{{ route('contest.gallery') }}" class="hover:text-dinor-red">Galerie</a>
                <a href="{{ route('contest.form') }}" class="btn-dinor !py-1.5">Participer</a>
            </nav>
        </div>
    </header>

    <main class="py-6">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    <footer class="mt-12 py-8 text-center text-sm text-gray-500">
        © {{ date('Y') }} Dinor — Un moment de cuisine avec maman
    </footer>

    @livewireScripts
</body>
</html>
