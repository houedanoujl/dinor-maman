@extends('layouts.app')

@section('content')
<section class="container mx-auto max-w-5xl px-4 py-10">

    {{-- En-tête participant --}}
    <div class="flex flex-col items-center gap-6 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:flex-row sm:items-center sm:p-8">
        @php($photo = $participant->getFirstMediaUrl('photo', 'thumb'))
        @if($photo)
            <img src="{{ $photo }}" alt="{{ $participant->full_name }}"
                 class="h-24 w-24 flex-none rounded-full object-cover ring-4 ring-dinor-gold/30 sm:h-28 sm:w-28">
        @else
            <div class="flex h-24 w-24 flex-none items-center justify-center rounded-full bg-dinor-cream text-3xl font-bold text-dinor-red ring-4 ring-dinor-gold/30 sm:h-28 sm:w-28">
                {{ strtoupper(mb_substr($participant->first_name, 0, 1) . mb_substr($participant->last_name, 0, 1)) }}
            </div>
        @endif

        <div class="flex-1 text-center sm:text-left">
            <p class="text-sm font-semibold uppercase tracking-wide text-dinor-red">Mon espace</p>
            <h1 class="font-display text-2xl font-bold text-dinor-dark sm:text-3xl">
                Bonjour {{ $participant->first_name }} !
            </h1>
            <p class="mt-1 text-sm text-gray-600">{{ $participant->city }}</p>

            {{-- Badge statut --}}
            <div class="mt-3 flex justify-center sm:justify-start">
                @if($participant->status === \App\Models\Participant::STATUS_APPROVED)
                    <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-4 py-1.5 text-sm font-semibold text-emerald-700 ring-1 ring-emerald-200">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        Photo approuvée et publiée
                    </span>
                @elseif($participant->status === \App\Models\Participant::STATUS_REJECTED)
                    <span class="inline-flex items-center gap-2 rounded-full bg-red-50 px-4 py-1.5 text-sm font-semibold text-red-700 ring-1 ring-red-200">
                        <span class="h-2 w-2 rounded-full bg-red-500"></span>
                        Participation refusée
                    </span>
                @else
                    <span class="inline-flex items-center gap-2 rounded-full bg-amber-50 px-4 py-1.5 text-sm font-semibold text-amber-700 ring-1 ring-amber-200">
                        <span class="h-2 w-2 animate-pulse rounded-full bg-amber-500"></span>
                        En attente de validation
                    </span>
                @endif
            </div>

            @if($participant->status === \App\Models\Participant::STATUS_REJECTED && $participant->rejection_reason)
                <p class="mt-2 text-sm text-red-600">Motif : {{ $participant->rejection_reason }}</p>
            @endif
        </div>
    </div>

    {{-- KPI cards --}}
    <div class="mt-8 grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Total votes</p>
            <p class="mt-2 text-3xl font-bold text-dinor-red">{{ number_format((int) $participant->vote_count, 0, ',', ' ') }}</p>
        </div>
        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Aujourd'hui</p>
            <p class="mt-2 text-3xl font-bold text-dinor-dark">+{{ $todayVotes }}</p>
        </div>
        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">7 derniers jours</p>
            <p class="mt-2 text-3xl font-bold text-dinor-dark">+{{ $last7Total }}</p>
        </div>
        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Classement</p>
            @if($rank)
                <p class="mt-2 text-3xl font-bold text-dinor-gold">
                    #{{ $rank }}<span class="ml-1 text-base font-medium text-gray-400">/ {{ $totalApproved }}</span>
                </p>
            @else
                <p class="mt-2 text-base text-gray-400">— en attente —</p>
            @endif
        </div>
    </div>

    {{-- Histogramme --}}
    <div class="mt-8 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <div class="mb-6 flex items-end justify-between gap-4">
            <div>
                <h2 class="font-display text-xl font-bold text-dinor-dark">Votes des 14 derniers jours</h2>
                <p class="text-sm text-gray-500">Le bar foncé = aujourd'hui</p>
            </div>
            <div class="hidden text-right text-sm text-gray-500 sm:block">
                Pic : <span class="font-semibold text-dinor-dark">{{ $maxDaily }}</span>
            </div>
        </div>

        <div class="grid grid-cols-14 items-end gap-1.5" style="grid-template-columns: repeat(14, minmax(0, 1fr)); height: 200px;">
            @foreach($dailyVotes as $day)
                @php($pct = $maxDaily > 0 ? max(2, round(($day['count'] / $maxDaily) * 100)) : 2)
                @php($isToday = $day['date']->isToday())
                <div class="flex h-full flex-col items-center justify-end">
                    <div class="relative flex w-full flex-col items-center justify-end" style="height: 100%;">
                        <span class="absolute -top-5 text-[10px] font-semibold {{ $isToday ? 'text-dinor-red' : 'text-gray-400' }}">
                            {{ $day['count'] > 0 ? $day['count'] : '' }}
                        </span>
                        <div class="w-full rounded-t-md transition-all duration-500 {{ $isToday ? 'bg-dinor-red' : 'bg-dinor-gold/70 hover:bg-dinor-gold' }}"
                             style="height: {{ $pct }}%;"
                             title="{{ $day['label'] }} : {{ $day['count'] }} vote(s)"></div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-2 grid gap-1.5" style="grid-template-columns: repeat(14, minmax(0, 1fr));">
            @foreach($dailyVotes as $day)
                <div class="text-center text-[10px] {{ $day['date']->isToday() ? 'font-bold text-dinor-red' : 'text-gray-500' }}">
                    {{ $day['date']->format('j/n') }}
                </div>
            @endforeach
        </div>
    </div>

    {{-- Partage / actions --}}
    @if($participant->status === \App\Models\Participant::STATUS_APPROVED && $shareUrl)
        <div class="mt-8 rounded-2xl border border-dinor-gold/30 bg-dinor-cream/50 p-6 shadow-sm">
            <h2 class="font-display text-xl font-bold text-dinor-dark">Faites monter votre score</h2>
            <p class="mt-1 text-sm text-gray-600">Partagez votre lien sur WhatsApp, Facebook, Instagram pour récolter plus de votes.</p>

            <div class="mt-4 flex flex-col gap-3 sm:flex-row">
                <input type="text" value="{{ $shareUrl }}" readonly
                       onclick="this.select()"
                       class="flex-1 rounded-full border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700 focus:border-dinor-red focus:outline-none">
                <a href="https://wa.me/?text={{ urlencode('Votez pour ma photo dans le concours Un moment de cuisine avec maman ! ' . $shareUrl) }}"
                   target="_blank" rel="noopener"
                   class="inline-flex items-center justify-center gap-2 rounded-full bg-emerald-500 px-5 py-2.5 font-semibold text-white shadow-sm transition hover:bg-emerald-600">
                    Partager sur WhatsApp
                </a>
                <a href="{{ route('participant.show', $participant) }}"
                   class="inline-flex items-center justify-center rounded-full bg-dinor-red px-5 py-2.5 font-semibold text-white shadow-sm transition hover:bg-dinor-red/90">
                    Voir ma page publique
                </a>
            </div>
        </div>
    @elseif($participant->status === \App\Models\Participant::STATUS_PENDING)
        <div class="mt-8 rounded-2xl border border-amber-200 bg-amber-50 p-6">
            <p class="text-sm text-amber-800">
                Votre photo est en cours de modération. Vous recevrez un email dès qu'elle sera approuvée et apparaîtra dans la galerie publique.
            </p>
        </div>
    @endif

</section>
@endsection
