@extends('layouts.app')

@section('content')
<section class="container mx-auto px-4 py-10">
    <div class="mb-8 text-center">
        <p class="text-xs font-bold uppercase tracking-wider text-dinor-gold">Palmares</p>
        <h1 class="mt-2 font-display text-4xl font-bold text-dinor-dark">Gagnants du concours</h1>
        <p class="mt-2 text-gray-600">Cycle {{ $cycle }} - Top 3 officiel</p>
    </div>

    @if ($winners->isEmpty())
        <div class="mx-auto max-w-xl rounded-2xl border border-gray-200 bg-white p-8 text-center text-gray-600 shadow-sm">
            Les gagnants n'ont pas encore ete annonces.
        </div>
    @else
        <div class="mx-auto grid max-w-5xl gap-5 md:grid-cols-3">
            @foreach ($winners as $winner)
                @php
                    $p = $winner->participant;
                    $img = $p?->getFirstMediaUrl('photo', 'card');
                @endphp
                <article class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
                    <a href="{{ route('participant.show', $p) }}" class="relative block aspect-[4/5]">
                        @if ($img)
                            <img src="{{ $img }}" alt="{{ $p->full_name }}" class="h-full w-full object-cover" />
                        @else
                            <div class="flex h-full w-full items-center justify-center bg-linear-to-br from-dinor-red to-dinor-gold text-6xl font-extrabold text-white">
                                {{ strtoupper(substr($p->first_name, 0, 1) . substr($p->last_name, 0, 1)) }}
                            </div>
                        @endif
                        <span class="absolute left-3 top-3 rounded-full bg-white px-3 py-1 text-sm font-bold text-dinor-red shadow">#{{ $winner->rank }}</span>
                    </a>
                    <div class="p-4">
                        <p class="truncate font-display text-xl font-bold text-dinor-dark">{{ $p->full_name }}</p>
                        <p class="text-sm text-gray-500">{{ $p->city }}</p>
                        <p class="mt-2 text-sm font-semibold text-dinor-red">{{ number_format($winner->vote_count_snapshot) }} votes</p>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</section>
@endsection
