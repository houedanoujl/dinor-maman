<?php

use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\GalleryView;
use App\Livewire\ParticipantLogin;
use App\Models\Participant;
use App\Models\ShareVisit;
use App\Models\Winner;
use App\Support\ContestSettings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $approved = Participant::approved();

    $topParticipants = (clone $approved)
        ->orderByDesc('vote_count')
        ->orderBy('created_at', 'asc')
        ->take(3)
        ->get();

    $collage = (clone $approved)
        ->whereNotIn('id', $topParticipants->pluck('id'))
        ->orderByDesc('created_at')
        ->take(6)
        ->get();

    return view('home', [
        'topParticipants' => $topParticipants,
        'collage' => $collage,
        'approvedCount' => (clone $approved)->count(),
        'totalVotes' => (clone $approved)->sum('vote_count'),
    ]);
})->name('home');

Route::get('/inscription', Register::class)
    ->middleware('throttle:30,1')
    ->name('register');

Route::get('/login', Login::class)
    ->middleware(['guest', 'throttle:30,1'])
    ->name('login');

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('home')->with('status', 'Déconnecté.');
})->middleware('auth')->name('logout');

Route::redirect('/participer', '/inscription?role=participant', 301)
    ->name('contest.form');

Route::get('/connexion', ParticipantLogin::class)
    ->middleware('throttle:30,1')
    ->name('participant.login');

Route::get('/reglement', fn () => view('reglement'))
    ->name('reglement');

Route::get('/faq', function () {
    $faq = ContestSettings::getFaq();
    return view('faq', ['faqItems' => $faq]);
})->name('faq');

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
                     ->where('created_at', '<', $participant->created_at);
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

$renderParticipantDashboard = function (Participant $participant) {
    // Classement (uniquement si approuvé)
    $rank = null;
    $totalApproved = Participant::approved()->count();
    if ($participant->status === Participant::STATUS_APPROVED) {
        $rank = Participant::approved()
            ->where(function ($q) use ($participant) {
                $q->where('vote_count', '>', $participant->vote_count)
                  ->orWhere(function ($q2) use ($participant) {
                      $q2->where('vote_count', $participant->vote_count)
                         ->where('created_at', '<', $participant->created_at);
                  });
            })->count() + 1;
    }

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
};

Route::get('/mon-espace/{token}', function (string $token) use ($renderParticipantDashboard) {
    $participant = Participant::findByDashboardToken($token);
    abort_if(! $participant, 404);

    session(['participant_token' => $token]);

    if ($participant->user_id && (! Auth::check() || Auth::id() !== $participant->user_id)) {
        Auth::loginUsingId($participant->user_id, true);
    }

    return $renderParticipantDashboard($participant);
})->middleware('throttle:30,1')->name('participant.dashboard');

// Espace personnel - Auth ou session participant_token.
Route::get('/espace', function () use ($renderParticipantDashboard) {
    // Cas 1: Auth set
    if (Auth::check()) {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return redirect('/admin');
        }

        $participant = Participant::where('user_id', $user->id)->first();
        if ($participant) {
            return $renderParticipantDashboard($participant);
        }

        // User authentifié sans participation: redirect home (votes anonymes)
        return redirect()->route('home');
    }

    // Cas 2: fallback via session participant_token (lien SMS sans Auth)
    $token = session('participant_token');
    if ($token) {
        $participant = Participant::findByDashboardToken($token);
        if ($participant) {
            if ($participant->user_id) {
                Auth::loginUsingId($participant->user_id, true);
            }
            return $renderParticipantDashboard($participant);
        }
        session()->forget('participant_token');
    }

    return redirect()->route('login')->with('status', 'Connectez-vous pour accéder à votre espace.');
})->middleware('throttle:30,1')->name('account');

Route::post('/deconnexion', function () {
    session()->forget('participant_token');
    return redirect()->route('home')->with('status', 'Vous êtes déconnecté.');
})->name('participant.logout');

Route::get('/gagnants', function () {
    $endsAt = ContestSettings::endsAt();
    $cycle = $endsAt->format('Y-m');

    // La d\u00e9signation des gagnants se fait via scheduler:
    // `php artisan contest:announce-winners` (jamais d\u00e9clench\u00e9 par requ\u00eate HTTP).

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
