<?php

use App\Livewire\ContestForm;
use App\Livewire\GalleryView;
use App\Models\Participant;
use App\Models\ShareVisit;
use App\Models\Winner;
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
        'collage' => $collage,
        'approvedCount' => (clone $approved)->count(),
        'totalVotes' => (clone $approved)->sum('vote_count'),
        'contestEndsAt' => config('contest.ends_at'),
        'contestEnded' => now()->greaterThan(config('contest.ends_at')),
    ]);
})->name('home');

Route::get('/participer', ContestForm::class)
    ->middleware('throttle:30,1')
    ->name('contest.form');

Route::get('/reglement', fn () => view('reglement'))
    ->name('reglement');

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

    $ref = request()->integer('ref');
    $validRef = Participant::approved()->whereKey($ref)->exists() ? $ref : null;

    if ($validRef && $validRef !== $participant->id) {
        $sessionHash = hash('sha256', session()->getId());

        ShareVisit::firstOrCreate(
            [
                'participant_id' => $participant->id,
                'ref_participant_id' => $validRef,
                'session_hash' => $sessionHash,
            ],
            [
                'ip_address' => request()->ip(),
                'user_agent' => substr((string) request()->userAgent(), 0, 255),
                'visited_at' => now(),
            ]
        );
    }

    return view('participant', [
        'participant' => $participant,
        'rank' => $rank,
        'similar' => $similar,
        'shareUrl' => route('participant.show', $participant) . '?ref=' . $participant->id,
        'title' => $participant->full_name . ' - Un moment de cuisine avec maman',
        'ogTitle' => $participant->full_name . ' - Votez pour cette participation',
        'ogDescription' => 'Soutenez ' . $participant->first_name . ' dans le concours Un moment de cuisine avec maman.',
        'ogImage' => $participant->getFirstMediaUrl('photo', 'card') ?: null,
        'ogUrl' => route('participant.show', $participant),
    ]);
})->middleware('throttle:120,1')->name('participant.show');

Route::get('/mon-espace/{token}', function (string $token) {
    $participant = Participant::where('dashboard_token', $token)->firstOrFail();

    // Mémorise le participant en session pour afficher son menu utilisateur
    session(['participant_token' => $participant->dashboard_token]);

    // Classement (uniquement si approuvé)
    $rank = null;
    $totalApproved = Participant::approved()->count();
    if ($participant->status === Participant::STATUS_APPROVED) {
        $rank = Participant::approved()
            ->where(function ($q) use ($participant) {
                $q->where('vote_count', '>', $participant->vote_count)
                  ->orWhere(function ($q2) use ($participant) {
                      $q2->where('vote_count', $participant->vote_count)
                         ->where('approved_at', '<', $participant->approved_at);
                  });
            })->count() + 1;
    }

    // Histogramme: votes par jour sur les 14 derniers jours
    $start = now()->subDays(13)->startOfDay();
    $rawCounts = $participant->votes()
        ->where('created_at', '>=', $start)
        ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
        ->groupBy('day')
        ->pluck('total', 'day');

    $dailyVotes = collect();
    for ($i = 0; $i < 14; $i++) {
        $date = $start->copy()->addDays($i);
        $key = $date->format('Y-m-d');
        $dailyVotes->push([
            'date' => $date,
            'label' => $date->isoFormat('dd D/M'),
            'count' => (int) ($rawCounts[$key] ?? 0),
        ]);
    }

    return view('participant-dashboard', [
        'participant' => $participant,
        'rank' => $rank,
        'totalApproved' => $totalApproved,
        'dailyVotes' => $dailyVotes,
        'maxDaily' => max(1, $dailyVotes->max('count')),
        'todayVotes' => (int) ($rawCounts[now()->format('Y-m-d')] ?? 0),
        'last7Total' => (int) $participant->votes()->where('created_at', '>=', now()->subDays(7))->count(),
        'shareUrl' => $participant->status === Participant::STATUS_APPROVED
            ? route('participant.show', $participant) . '?ref=' . $participant->id
            : null,
        'title' => 'Mon espace — ' . $participant->full_name,
    ]);
})->middleware('throttle:60,1')->name('participant.dashboard');

Route::post('/deconnexion', function () {
    session()->forget('participant_token');
    return redirect()->route('home')->with('status', 'Vous êtes déconnecté.');
})->name('participant.logout');

Route::get('/gagnants', function () {
    $cycle = now()->format('Y-m');

    $winners = Winner::query()
        ->with('participant')
        ->where('contest_cycle', $cycle)
        ->orderBy('rank')
        ->get();

    return view('winners', [
        'winners' => $winners,
        'cycle' => $cycle,
    ]);
})->name('winners.index');
