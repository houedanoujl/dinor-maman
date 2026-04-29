@extends('layouts.app')

@section('content')
<section class="container mx-auto px-4 py-10">
    <a href="{{ route('contest.form') }}"
       class="block relative rounded-3xl overflow-hidden shadow-dinor bg-gradient-to-br from-dinor-red to-dinor-gold p-10 text-white text-center">
        <h1 class="text-4xl md:text-5xl font-extrabold drop-shadow">
            Un moment de cuisine avec maman
        </h1>
        <p class="mt-3 text-lg opacity-90">
            Partagez votre photo, récoltez des votes, gagnez avec Dinor.
        </p>
        <span class="inline-block mt-6 bg-white text-dinor-red font-bold px-6 py-3 rounded-xl">
            Je participe →
        </span>
    </a>

    <div class="mt-10 text-center">
        <a href="{{ route('contest.gallery') }}" class="text-dinor-red font-semibold hover:underline">
            Voir la galerie des participants
        </a>
    </div>
</section>
@endsection
