<?php

namespace App\Livewire;

use App\Models\Participant;
use App\Models\Vote;
use App\Support\ContestSettings;
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

    public function toggleVote(): void
    {
        if (now()->greaterThan(ContestSettings::endsAt())) {
            $this->dispatch('toast', type: 'error', message: 'Le concours est termine. Les votes sont clos.');
            return;
        }

        // 1. Rate limiting global par IP : max 30 votes / minute
        $ipKey = 'vote:ip:' . request()->ip();
        if (RateLimiter::tooManyAttempts($ipKey, 30)) {
            $this->dispatch('vote-error', message: 'Trop de votes en peu de temps. Réessayez dans une minute.');
            return;
        }
        RateLimiter::hit($ipKey, 60);

        // 2. Rate limiting fin (anti double-clic) : 1 action / 3s sur (IP + participant)
        $actionKey = 'vote:action:' . request()->ip() . ':' . $this->participant->id;
        if (RateLimiter::tooManyAttempts($actionKey, 1)) {
            return;
        }
        RateLimiter::hit($actionKey, 3);

        // 3. Vérification stricte : déjà voté depuis cette IP ou cette session ?
        if ($this->checkAlreadyVoted()) {
            $this->hasVoted = true;
            return;
        }

        // 4. Le participant doit être approuvé
        if ($this->participant->status !== Participant::STATUS_APPROVED) {
            $this->dispatch('vote-error', message: 'Cette photo n\'est pas encore validée.');
            return;
        }

        // 5. Insertion atomique. Les contraintes UNIQUE de la table votes
        //    garantissent qu'aucun double-vote ne peut être enregistré, même
        //    en cas de race condition.
        try {
            DB::transaction(function () {
                Vote::create([
                    'participant_id'     => $this->participant->id,
                    'ip_address'         => request()->ip(),
                    'session_id'         => hash('sha256', session()->getId()),
                    'device_fingerprint' => $this->fingerprint(),
                    'user_agent'         => substr((string) request()->userAgent(), 0, 255),
                ]);

                Participant::where('id', $this->participant->id)
                    ->increment('vote_count');
            });

            $this->count++;
            $this->hasVoted = true;
            $this->dispatch('vote-cast', participantId: $this->participant->id);
        } catch (\Illuminate\Database\QueryException $e) {
            // Violation de contrainte UNIQUE → vote déjà enregistré
            if ($e->getCode() === '23000') {
                $this->hasVoted = true;
                return;
            }
            throw $e;
        }
    }

    protected function checkAlreadyVoted(): bool
    {
        return Vote::where('participant_id', $this->participant->id)
            ->where(function ($q) {
                $q->where('ip_address', request()->ip())
                  ->orWhere('session_id', hash('sha256', session()->getId()));
            })->exists();
    }

    protected function fingerprint(): string
    {
        return hash('sha256', implode('|', [
            request()->ip(),
            request()->userAgent(),
            request()->header('Accept-Language'),
            session()->getId(),
        ]));
    }

    public function render()
    {
        return view('livewire.vote-button');
    }
}
