@extends('layouts.app')

@section('title', $title)

@section('content')
<div class="container mx-auto max-w-3xl px-4 py-8">
    <div class="mb-6">
        <p class="text-xs font-bold uppercase tracking-wider text-dinor-gold">Espace personnel</p>
        <h1 class="mt-1 font-display text-3xl font-bold text-dinor-dark md:text-4xl">
            Bonjour {{ $user->name }}
        </h1>
        <p class="mt-2 text-gray-600">Retrouvez ici votre mot de passe et l'historique de vos votes.</p>
    </div>

    {{-- Mot de passe proéminent --}}
    @if ($user->plain_password)
        <div x-data="{ show: false, copied: false }"
             class="mb-6 rounded-2xl border-2 border-dinor-gold bg-linear-to-br from-dinor-gold/10 to-dinor-red/5 p-6 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-dinor-gold/20">
                    <svg class="h-6 w-6 text-dinor-gold" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-dinor-gold">Votre mot de passe</p>
                    <p class="text-xs text-gray-500">Conservez-le pour vous reconnecter.</p>
                </div>
            </div>
            <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center">
                <code class="flex-1 rounded-xl border border-dinor-gold/40 bg-white px-5 py-4 text-center font-mono text-2xl font-bold tracking-widest text-dinor-dark"
                      x-text="show ? @js($user->plain_password) : '••••••••'"></code>
                <div class="flex gap-2">
                    <button type="button"
                            x-on:click="show = !show"
                            class="inline-flex items-center justify-center gap-2 rounded-full border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-dinor-dark transition hover:border-dinor-red hover:text-dinor-red">
                        <span x-text="show ? 'Masquer' : 'Afficher'"></span>
                    </button>
                    <button type="button"
                            x-on:click="navigator.clipboard?.writeText(@js($user->plain_password)); copied = true; setTimeout(() => copied = false, 1500)"
                            class="inline-flex items-center justify-center gap-2 rounded-full bg-dinor-red px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-dinor-red/90">
                        <svg x-show="!copied" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        <svg x-show="copied" x-cloak class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        <span x-text="copied ? 'Copié !' : 'Copier'"></span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Devenir participant --}}
    @if ($user->isVoter())
        <div class="mb-6 rounded-2xl border border-dinor-red/20 bg-dinor-red/5 p-5">
            <div class="flex items-start gap-3">
                <svg class="mt-0.5 h-5 w-5 shrink-0 text-dinor-red" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                <div class="flex-1">
                    <p class="font-semibold text-dinor-dark">Envie de participer ?</p>
                    <p class="mt-1 text-sm text-gray-600">Soumettez votre photo en cuisine avec maman et tentez de gagner.</p>
                    <a href="{{ route('register', ['role' => 'participant']) }}"
                       class="mt-3 inline-flex items-center justify-center rounded-full bg-dinor-red px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-dinor-red/90">
                        Participer au concours
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- Historique votes --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <h2 class="font-display text-xl font-bold text-dinor-dark">Mes votes ({{ $votes->count() }})</h2>

        @if ($votes->isEmpty())
            <div class="mt-4 rounded-xl border border-dashed border-gray-200 bg-gray-50 p-6 text-center">
                <p class="text-sm text-gray-600">Vous n'avez pas encore voté.</p>
                <a href="{{ route('contest.gallery') }}"
                   class="mt-3 inline-flex items-center justify-center rounded-full bg-dinor-red px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-dinor-red/90">
                    Voir la galerie
                </a>
            </div>
        @else
            <ul class="mt-4 divide-y divide-gray-100">
                @foreach ($votes as $vote)
                    @php($p = $vote->participant)
                    @php($thumb = $p?->getFirstMediaUrl('photo', 'thumb'))
                    <li class="flex items-center gap-3 py-3">
                        @if ($thumb)
                            <img src="{{ $thumb }}" alt="" class="h-12 w-12 rounded-lg object-cover" />
                        @else
                            <div class="h-12 w-12 rounded-lg bg-gray-100"></div>
                        @endif
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-semibold text-dinor-dark">{{ $p?->full_name ?? '—' }}</p>
                            <p class="truncate text-xs text-gray-500">{{ $p?->city }} · voté le {{ $vote->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        @if ($p && $p->status === \App\Models\Participant::STATUS_APPROVED)
                            <a href="{{ route('participant.show', $p) }}"
                               class="shrink-0 rounded-full border border-gray-200 px-3 py-1 text-xs font-semibold text-dinor-dark transition hover:border-dinor-red hover:text-dinor-red">
                                Voir
                            </a>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
@endsection
