<?php

use App\Models\Participant;
use App\Livewire\ContestForm;
use App\Livewire\GalleryView;
use App\Livewire\VoteButton;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $approved = Participant::approved();

    $topParticipants = (clone $approved)
        ->orderByDesc('vote_count')
        ->orderByDesc('approved_at')
        ->take(3)
        ->get();

    $collage = (clone $approved)
        ->whereNotIn('id', $topParticipants->pluck('id'))
        ->orderByDesc('approved_at')
        ->take(6)
        ->get();

    return view('home', [
        'topParticipants' => $topParticipants,
        'collage'         => $collage,
        'approvedCount'   => (clone $approved)->count(),
        'totalVotes'      => (clone $approved)->sum('vote_count'),
    ]);
})->name('home');

Route::get('/participer', ContestForm::class)
    ->middleware('throttle:30,1')
    ->name('contest.form');

Route::get('/galerie', GalleryView::class)
    ->middleware('throttle:120,1')
    ->name('contest.gallery');

Route::get('/profil/{participant}', function (Participant $participant) {
    abort_unless($participant->status === Participant::STATUS_APPROVED, 404);

    $rank = Participant::approved()
        ->where(function ($q) use ($participant) {
            $q->where('vote_count', '>', $participant->vote_count)
              ->orWhere(function ($q2) use ($participant) {
                  $q2->where('vote_count', $participant->vote_count)
                     ->where('approved_at', '>', $participant->approved_at);
              });
        })->count() + 1;

    $similar = Participant::approved()
        ->where('id', '!=', $participant->id)
        ->where('city', $participant->city)
        ->orderByDesc('vote_count')
        ->take(3)
        ->get();

    return view('participant', [
        'participant' => $participant,
        'rank'        => $rank,
        'similar'     => $similar,
    ]);
})->middleware('throttle:120,1')->name('participant.show');
