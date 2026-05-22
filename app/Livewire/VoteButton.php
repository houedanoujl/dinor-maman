<?php

namespace App\Livewire;

use App\Http\Middleware\EnsureVoterToken;
use App\Models\Participant;
use App\Models\Vote;
use App\Support\ContestSettings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class VoteButton extends Component
{
    public const COOKIE_NAME = EnsureVoterToken::COOKIE_NAME;
    public const COOKIE_MINUTES = EnsureVoterToken::COOKIE_MINUTES;

    public Participant $participant;
    public bool $hasVoted = false;
    public int $count = 0;

    public function mount(Participant $participant): void
    {
        $this->participant = $participant;
        $this->count = $participant->vote_count;
        $this->refreshVoteState();
    }

    public function toggleVote()
    {
        if (now()->greaterThan(ContestSettings::endsAt())) {
            $this->dispatch('toast', type: 'error', message: 'Le concours est terminé. Les votes sont clos.');
            return;
        }

        if ($this->participant->status !== Participant::STATUS_APPROVED) {
            $this->dispatch('vote-error', message: 'Cette photo n\'est pas encore validée.');
            return;
        }

        $token = $this->resolveVoterToken();
        if ($token === '') {
            $this->dispatch('vote-error', message: 'Impossible d\'enregistrer votre vote. Activez les cookies puis réessayez.');
            return;
        }

        $ipKey = 'vote:ip:' . request()->ip();
        if (RateLimiter::tooManyAttempts($ipKey, 60)) {
            $this->dispatch('vote-error', message: 'Trop de tentatives. Réessayez dans une minute.');
            return;
        }
        RateLimiter::hit($ipKey, 60);

        if ($this->checkAlreadyVoted($token)) {
            $this->hasVoted = true;
            return;
        }

        try {
            DB::transaction(function () use ($token) {
                Vote::create([
                    'participant_id' => $this->participant->id,
                    'voter_token'    => $token,
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

    protected function resolveVoterToken(): string
    {
        return (string) (request()->attributes->get('voter_token') ?? request()->cookie(self::COOKIE_NAME, ''));
    }

    protected function checkAlreadyVoted(string $token): bool
    {
        return Vote::where('voter_token', $token)
            ->where('participant_id', $this->participant->id)
            ->exists();
    }

    protected function refreshVoteState(): void
    {
        $token = $this->resolveVoterToken();
        $this->hasVoted = $token !== '' && $this->checkAlreadyVoted($token);
    }

    public function render()
    {
        return view('livewire.vote-button');
    }
}
