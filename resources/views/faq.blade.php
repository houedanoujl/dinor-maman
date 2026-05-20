@extends('layouts.app')

@section('content')
<div class="container mx-auto max-w-3xl px-4 py-12">
    <div class="mb-8">
        <p class="text-xs font-bold uppercase tracking-wider text-dinor-gold">Concours</p>
        <h1 class="mt-2 font-display text-3xl font-bold text-dinor-dark md:text-4xl">
            Questions fréquentes
        </h1>
        <p class="mt-2 text-gray-600">Jeu-concours « Un moment de cuisine avec maman »</p>
    </div>

    <div class="space-y-3" x-data="{ open: null }">

        @forelse ($faqItems as $i => $item)
            <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
                <button type="button"
                        x-on:click="open = open === {{ $i }} ? null : {{ $i }}"
                        class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left text-sm font-semibold text-dinor-dark transition hover:text-dinor-red">
                    <span>{{ $item['q'] }}</span>
                    <svg class="h-5 w-5 shrink-0 text-dinor-gold transition-transform duration-200"
                         :class="open === {{ $i }} ? 'rotate-180' : ''"
                         fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="open === {{ $i }}"
                     x-transition:enter="transition duration-150 ease-out"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-cloak
                     class="border-t border-gray-100 px-5 py-4 text-sm text-gray-600">
                    {{ $item['a'] }}
                </div>
            </div>
        @empty
            <p class="py-8 text-center text-gray-400 italic">Aucune question pour l'instant.</p>
        @endforelse

    </div>

    <div class="mt-10 flex flex-col items-center gap-3 text-center sm:flex-row sm:justify-center">
        <a href="{{ route('contest.form') }}"
           class="inline-flex items-center justify-center rounded-full bg-dinor-red px-8 py-3 font-semibold text-white shadow-sm transition hover:bg-dinor-red/90">
            Je participe
        </a>
        <a href="{{ route('reglement') }}"
           class="inline-flex items-center justify-center rounded-full border border-gray-200 px-8 py-3 font-semibold text-dinor-dark transition hover:border-dinor-red hover:text-dinor-red">
            Lire le règlement
        </a>
    </div>
</div>
@endsection
