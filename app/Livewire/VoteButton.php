<?php

namespace App\Livewire;

use App\Models\Participant;
use App\Models\Vote;
use App\Support\ContestSettings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class VoteButton extends Component
{
    public Participant $participant;
    public bool $hasVoted = false;
    public int $count = 0;

    public function mount(Participant $participant): void
    {
        $this->participant = $participant;
        $this->count = $participant->vote_count;
        $this->hasVoted = $this->checkAlreadyVoted();
    }

    public function toggleVote()
    {
        if (! Auth::check()) {
            session(['url.intended' => url()->current()]);
            return redirect()->route('login')
                ->with('status', 'Connectez-vous pour voter.');
        }

        if (! Auth::user()->canVote()) {
            $this->dispatch('vote-error', message: 'Votre compte ne peut pas voter.');
            return;
        }

        if (now()->greaterThan(ContestSettings::endsAt())) {
            $this->dispatch('toast', type: 'error', message: 'Le concours est terminé. Les votes sont clos.');
            return;
        }

        $userKey = 'vote:user:' . Auth::id();
        if (RateLimiter::tooManyAttempts($userKey, 30)) {
            $this->dispatch('vote-error', message: 'Trop de votes en peu de temps. Réessayez dans une minute.');
            return;
        }
        RateLimiter::hit($userKey, 60);

        if ($this->checkAlreadyVoted()) {
            $this->hasVoted = true;
            return;
        }

        if ($this->participant->status !== Participant::STATUS_APPROVED) {
            $this->dispatch('vote-error', message: 'Cette photo n\'est pas encore validée.');
            return;
        }

        if ($this->participant->user_id === Auth::id()) {
            $this->dispatch('vote-error', message: 'Vous ne pouvez pas voter pour votre propre photo.');
            return;
        }

        try {
            DB::transaction(function () {
                Vote::create([
                    'participant_id' => $this->participant->id,
                    'user_id'        => Auth::id(),
                    'ip_address'     => request()->ip(),
                    'session_id'     => hash('sha256', session()->getId()),
                    'user_agent'     => substr((string) request()->userAgent(), 0, 255),
                ]);

                Participant::where('id', $this->participant->id)
                    ->increment('vote_count');
            });

            $this->count++;
            $this->hasVoted = true;
            $this->dispatch('vote-cast', participantId: $this->participant->id);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                $this->hasVoted = true;
                return;
            }
            throw $e;
        }
    }

    protected function checkAlreadyVoted(): bool
    {
        if (! Auth::check()) {
            return false;
        }

        return Vote::where('participant_id', $this->participant->id)
            ->where('user_id', Auth::id())
            ->exists();
    }

    public function render()
    {
        return view('livewire.vote-button');
    }
}
