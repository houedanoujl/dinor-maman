@extends('layouts.app')

@section('content')
<section class="relative overflow-hidden">
    <div class="container mx-auto px-4 py-14 lg:py-20">
        <div class="mx-auto max-w-3xl text-center">
            <p class="mb-4 inline-flex items-center gap-2 rounded-full {{ $contestEnded ? 'bg-gray-100 text-gray-600' : 'bg-dinor-red/10 text-dinor-red' }} px-3 py-1 text-xs font-semibold uppercase tracking-wider">
                <span class="h-1.5 w-1.5 rounded-full {{ $contestEnded ? 'bg-gray-500' : 'bg-dinor-red' }}"></span>
                {{ $contestEnded ? 'Concours terminé' : 'Concours photo en cours' }}
            </p>
            <h1 class="font-display text-4xl font-bold leading-[1.05] text-dinor-dark md:text-6xl">
                Un moment de<br />
                <span class="text-dinor-red">cuisine</span> avec maman
            </h1>
            <p class="mt-6 mx-auto max-w-xl text-lg leading-8 text-gray-600">
                @if($contestEnded)
                    Le concours est terminé — découvrez le palmarès des familles qui ont marqué cette édition.
                @else
                    Partagez votre plus beau moment en cuisine, invitez vos proches à voter
                    et célébrez les recettes qui ont bercé votre famille.
                @endif
            </p>
            <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                @if($contestEnded)
                    <a href="{{ route('winners.index') }}" class="btn-dinor">Voir les gagnants</a>
                    <a href="{{ route('contest.gallery') }}" class="btn-ghost">Revoir la galerie</a>
                @elseif($uploadPhase)
                    <a href="{{ route('register', ['role' => 'participant']) }}" class="btn-dinor">Je participe</a>
                    <a href="{{ route('contest.gallery') }}" class="btn-ghost">Voir la galerie</a>
                @else
                    <a href="{{ route('contest.gallery') }}" class="btn-dinor">Voter pour ma favorite</a>
                    <a href="{{ route('participant.login') }}" class="btn-ghost">Accéder à mon espace</a>
                @endif
            </div>

            @if($contestEnded)
                <div class="mt-5 inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-gray-50 px-4 py-2 text-sm text-gray-600">
                    <span class="font-semibold">Statut:</span>
                    <span class="font-bold text-dinor-dark">Terminé le {{ \Illuminate\Support\Carbon::parse($contestEndsAt)->isoFormat('D MMM Y') }}</span>
                </div>
            @else
                <div x-data="countdown('{{ $contestEndsAt }}')" class="mt-5 inline-flex items-center gap-2 rounded-xl border border-dinor-gold/30 bg-dinor-gold/10 px-4 py-2 text-sm text-dinor-dark">
                    <span class="font-semibold">Fin du concours dans:</span>
                    <span class="font-bold" x-text="label"></span>
                </div>
            @endif

            <dl class="mt-10 flex flex-wrap justify-center gap-x-10 gap-y-4 text-sm">
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
                <a href="{{ route('participant.show', $participant) }}" class="relative block aspect-4/5 bg-linear-to-br from-dinor-red to-dinor-gold">
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
                <div class="p-5">
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <a href="{{ route('participant.show', $participant) }}" class="block truncate font-display text-lg font-bold text-dinor-dark hover:text-dinor-red">
                                {{ $participant->full_name }}
                            </a>
                            <p class="truncate text-sm text-gray-500">{{ $participant->city }}</p>
                        </div>
                        <div class="shrink-0 text-right">
                            <p class="font-display text-2xl font-bold text-dinor-red leading-none">{{ number_format($participant->vote_count) }}</p>
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-500">vote{{ $participant->vote_count > 1 ? 's' : '' }}</p>
                        </div>
                    </div>

                    <div class="mt-4 flex items-center gap-3">
                        <livewire:vote-button :participant="$participant" :key="'home-vote-'.$participant->id" />
                    </div>
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
