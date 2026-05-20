@extends('layouts.app')

@section('content')
<div class="container mx-auto max-w-3xl px-4 py-12">
    <div class="mb-8">
        <p class="text-xs font-bold uppercase tracking-wider text-dinor-gold">Légal</p>
        <h1 class="mt-2 font-display text-3xl font-bold text-dinor-dark md:text-4xl">
            Conditions générales d'utilisation
        </h1>
        <p class="mt-3 text-sm text-gray-500">Jeu-concours « Un moment de cuisine avec maman »</p>
    </div>

    <div class="prose prose-gray max-w-none rounded-2xl border border-gray-100 bg-white p-6 shadow-sm text-gray-700
                [&_h2]:font-display [&_h2]:text-xl [&_h2]:font-bold [&_h2]:text-dinor-dark [&_h2]:mt-6 [&_h2]:mb-2
                [&_p]:mt-2 [&_ul]:mt-2 [&_ul]:list-disc [&_ul]:pl-5 [&_ul]:space-y-1
                [&_ol]:mt-2 [&_ol]:list-decimal [&_ol]:pl-5 [&_ol]:space-y-1
                [&_li]:text-gray-700 [&_strong]:text-dinor-dark">
        @if ($content)
            {!! $content !!}
        @else
            <p class="text-gray-400 italic">CGU en cours de rédaction.</p>
        @endif
    </div>

    <div class="mt-8 flex flex-col items-center gap-3 text-center sm:flex-row sm:justify-center">
        <a href="{{ route('reglement') }}"
           class="inline-flex items-center justify-center rounded-full border border-gray-200 px-8 py-3 font-semibold text-dinor-dark transition hover:border-dinor-red hover:text-dinor-red">
            Règlement du concours
        </a>
        <a href="{{ route('faq') }}"
           class="inline-flex items-center justify-center rounded-full border border-gray-200 px-8 py-3 font-semibold text-dinor-dark transition hover:border-dinor-red hover:text-dinor-red">
            FAQ
        </a>
    </div>
</div>
@endsection
