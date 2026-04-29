@extends('layouts.app')

@section('content')
<section class="container mx-auto px-4 py-10">
    <a href="{{ route('contest.gallery') }}" class="mb-6 inline-flex items-center gap-1 text-sm font-medium text-gray-500 hover:text-dinor-red">
        ← Retour à la galerie
    </a>

    <div class="grid gap-8 lg:grid-cols-[1.1fr_1fr]">
        <div class="overflow-hidden rounded-3xl bg-white shadow-dinor">
            @if ($participant->getFirstMediaUrl('photo', 'card'))
                <img src="{{ $participant->getFirstMediaUrl('photo', 'card') }}"
                     alt="{{ $participant->full_name }}"
                     class="h-full w-full object-cover" />
            @else
                <div class="flex aspect-square w-full items-center justify-center bg-linear-to-br from-dinor-red to-dinor-gold text-9xl font-extrabold text-white">
                    {{ strtoupper(substr($participant->first_name, 0, 1) . substr($participant->last_name, 0, 1)) }}
                </div>
            @endif
        </div>

        <div class="flex flex-col">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1 rounded-full bg-dinor-gold/15 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-dinor-gold">
                    Classement #{{ $rank }}
                </span>
                <span class="text-xs text-gray-500">Approuvé{{ $participant->approved_at ? ' le ' . $participant->approved_at->translatedFormat('d M Y') : '' }}</span>
            </div>

            <h1 class="mt-3 font-display text-4xl font-bold leading-tight text-dinor-dark md:text-5xl">
                {{ $participant->full_name }}
            </h1>
            <p class="mt-1 text-lg text-gray-600">{{ $participant->city }}</p>

            <div class="mt-6 flex items-center gap-4 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                <div class="flex-1">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Votes reçus</p>
                    <p class="font-display text-4xl font-bold text-dinor-red">{{ number_format($participant->vote_count) }}</p>
                </div>
                <livewire:vote-button :participant="$participant" :key="'profile-vote-'.$participant->id" />
            </div>

            <p class="mt-6 text-gray-600">
                Soutenez <strong>{{ $participant->first_name }}</strong> en cliquant sur ❤︎. Un seul vote par personne —
                partagez le lien à vos proches pour faire grimper le classement.
            </p>

            <div class="mt-6 flex flex-wrap gap-2">
                <button type="button"
                        onclick="navigator.clipboard?.writeText('{{ $shareUrl }}'); this.innerText='Lien copié ✓'"
                        class="btn-ghost text-sm">
                    Copier mon lien de partage
                </button>
                <a href="https://wa.me/?text={{ urlencode($participant->full_name . ' — ' . $shareUrl) }}"
                   target="_blank" rel="noopener"
                   class="btn-ghost text-sm">
                    Partager sur WhatsApp
                </a>
            </div>
        </div>
    </div>

    @if ($similar->isNotEmpty())
        <div class="mt-16">
            <h2 class="font-display text-2xl font-bold text-dinor-dark">Aussi à {{ $participant->city }}</h2>
            <div class="mt-5 grid gap-4 sm:grid-cols-3">
                @foreach ($similar as $p)
                    <a href="{{ route('participant.show', $p) }}"
                       class="group overflow-hidden rounded-2xl bg-white shadow-sm transition hover:shadow-dinor">
                        <div class="relative aspect-square">
                            @if ($p->getFirstMediaUrl('photo', 'card'))
                                <img src="{{ $p->getFirstMediaUrl('photo', 'card') }}"
                                     alt="{{ $p->full_name }}"
                                     class="h-full w-full object-cover transition duration-500 group-hover:scale-105" />
                            @else
                                <div class="flex h-full w-full items-center justify-center bg-linear-to-br from-dinor-red to-dinor-gold text-4xl font-extrabold text-white">
                                    {{ strtoupper(substr($p->first_name, 0, 1) . substr($p->last_name, 0, 1)) }}
                                </div>
                            @endif
                        </div>
                        <div class="p-3">
                            <p class="truncate font-semibold text-dinor-dark group-hover:text-dinor-red">{{ $p->full_name }}</p>
                            <p class="text-xs text-gray-500">{{ $p->vote_count }} vote{{ $p->vote_count > 1 ? 's' : '' }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</section>
@endsection
