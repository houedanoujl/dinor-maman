@extends('layouts.app')

@section('content')
<section class="relative overflow-hidden">
    <div class="container mx-auto grid gap-10 px-4 py-14 lg:grid-cols-[1.05fr_1fr] lg:items-center lg:py-20">
        <div>
            <p class="mb-4 inline-flex items-center gap-2 rounded-full bg-dinor-red/10 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-dinor-red">
                <span class="h-1.5 w-1.5 rounded-full bg-dinor-red"></span>
                Concours photo en cours
            </p>
            <h1 class="font-display text-4xl font-bold leading-[1.05] text-dinor-dark md:text-6xl">
                Cuisine,<br />
                <span class="text-dinor-red">souvenirs</span> &<br />
                amour de maman.
            </h1>
            <p class="mt-6 max-w-xl text-lg leading-8 text-gray-600">
                Partagez votre plus beau moment en cuisine, invitez vos proches à voter
                et célébrez les recettes qui ont bercé votre famille.
            </p>
            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('contest.form') }}" class="btn-dinor">Je participe</a>
                <a href="{{ route('contest.gallery') }}" class="btn-ghost">Voir la galerie</a>
            </div>

            <dl class="mt-10 flex flex-wrap gap-x-10 gap-y-4 text-sm">
                <div>
                    <dt class="text-gray-500">Participants</dt>
                    <dd class="font-display text-2xl font-bold text-dinor-dark">{{ number_format($approvedCount) }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Votes enregistrés</dt>
                    <dd class="font-display text-2xl font-bold text-dinor-red">{{ number_format($totalVotes) }}</dd>
                </div>
            </dl>
        </div>

        <div class="relative">
            @if ($collage->isNotEmpty() || $topParticipants->isNotEmpty())
                @php $cards = $topParticipants->concat($collage)->take(5); @endphp
                <div class="grid grid-cols-6 grid-rows-6 gap-3 h-[480px] md:h-[560px]">
                    @foreach ($cards as $i => $p)
                        @php
                            $spans = [
                                'col-span-4 row-span-4',
                                'col-span-2 row-span-3',
                                'col-span-2 row-span-3',
                                'col-span-3 row-span-2',
                                'col-span-3 row-span-2',
                            ];
                            $img = $p->getFirstMediaUrl('photo', 'card');
                        @endphp
                        <a href="{{ route('participant.show', $p) }}"
                           class="group relative overflow-hidden rounded-2xl shadow-md {{ $spans[$i] ?? 'col-span-3 row-span-2' }}">
                            @if ($img)
                                <img src="{{ $img }}" alt="{{ $p->full_name }}"
                                     class="h-full w-full object-cover transition duration-500 group-hover:scale-105" />
                            @else
                                <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-dinor-red to-dinor-gold text-4xl font-extrabold text-white">
                                    {{ strtoupper(substr($p->first_name, 0, 1) . substr($p->last_name, 0, 1)) }}
                                </div>
                            @endif
                            <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent p-3 text-white">
                                <p class="truncate text-sm font-semibold">{{ $p->full_name }}</p>
                                <p class="flex items-center gap-1 text-xs opacity-90">
                                    <svg class="h-3 w-3 text-dinor-gold" viewBox="0 0 24 24" fill="currentColor"><path d="M12 21s-7.5-4.6-9.5-9.1C1.1 8.6 3.4 5 7 5c2 0 3.6 1 5 2.6C13.4 6 15 5 17 5c3.6 0 5.9 3.6 4.5 6.9C19.5 16.4 12 21 12 21z"/></svg>
                                    {{ $p->vote_count }}
                                </p>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="flex aspect-square items-center justify-center rounded-2xl bg-gradient-to-br from-dinor-red to-dinor-gold p-12 text-center text-white">
                    <p class="font-display text-2xl">Soyez la première participation publiée !</p>
                </div>
            @endif
        </div>
    </div>
</section>

<section class="container mx-auto px-4 py-14">
    <div class="mb-8 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-dinor-gold">Classement</p>
            <h2 class="font-display text-3xl font-bold text-dinor-dark md:text-4xl">Top 3 du moment</h2>
        </div>
        <a href="{{ route('contest.gallery') }}" class="font-semibold text-dinor-red hover:underline">Tout le classement →</a>
    </div>

    <div class="grid gap-5 md:grid-cols-3">
        @forelse ($topParticipants as $participant)
            <article class="group overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition hover:shadow-dinor">
                <a href="{{ route('participant.show', $participant) }}" class="relative block aspect-[4/5] bg-gradient-to-br from-dinor-red to-dinor-gold">
                    @if ($participant->getFirstMediaUrl('photo', 'card'))
                        <img src="{{ $participant->getFirstMediaUrl('photo', 'card') }}"
                             alt="{{ $participant->full_name }}"
                             class="h-full w-full object-cover transition duration-500 group-hover:scale-105" />
                    @else
                        <div class="flex h-full w-full items-center justify-center text-7xl font-extrabold text-white">
                            {{ strtoupper(substr($participant->first_name, 0, 1) . substr($participant->last_name, 0, 1)) }}
                        </div>
                    @endif
                    <div class="absolute left-3 top-3 flex h-10 w-10 items-center justify-center rounded-full bg-white font-display text-lg font-bold text-dinor-red shadow">
                        {{ $loop->iteration }}
                    </div>
                </a>
                <div class="flex items-center justify-between gap-3 p-5">
                    <div class="min-w-0">
                        <a href="{{ route('participant.show', $participant) }}" class="block truncate font-display text-lg font-bold text-dinor-dark hover:text-dinor-red">
                            {{ $participant->full_name }}
                        </a>
                        <p class="truncate text-sm text-gray-500">{{ $participant->city }}</p>
                    </div>
                    <livewire:vote-button :participant="$participant" :key="'home-vote-'.$participant->id" />
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-10 text-center text-gray-500 md:col-span-3">
                Aucune participation approuvée pour l'instant — soyez la première !
            </div>
        @endforelse
    </div>
</section>
@endsection
