<?php

namespace App\Livewire;

use App\Models\Participant;
use App\Models\Vote;
use App\Support\ContestSettings;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;

class VoteButton extends Component
{
    public const COOKIE_NAME = 'dinor_voter';
    public const COOKIE_MINUTES = 60 * 24 * 365; // 1 an

    public Participant $participant;
    public bool $hasVoted = false;
    public bool $hasVotedAnywhere = false;
    public int $count = 0;
    public string $voterToken = '';

    public function mount(Participant $participant): void
    {
        $this->participant = $participant;
        $this->count = $participant->vote_count;
        $this->voterToken = $this->resolveVoterToken();
        $this->refreshVoteState();
    }

    #[On('voter-token-set')]
    public function onVoterTokenSet(string $token): void
    {
        if ($token === '' || $token === $this->voterToken) {
            return;
        }
        $this->voterToken = $token;
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

        $token = $this->voterToken !== '' ? $this->voterToken : $this->resolveVoterToken();
        if ($token === '') {
            $this->dispatch('vote-error', message: 'Impossible d\'enregistrer votre vote. Activez les cookies puis réessayez.');
            return;
        }

        // Rate limit par IP (anti-spam)
        $ipKey = 'vote:ip:' . request()->ip();
        if (RateLimiter::tooManyAttempts($ipKey, 60)) {
            $this->dispatch('vote-error', message: 'Trop de tentatives. Réessayez dans une minute.');
            return;
        }
        RateLimiter::hit($ipKey, 60);

        if (Vote::where('voter_token', $token)->exists()) {
            $this->hasVotedAnywhere = true;
            $this->hasVoted = Vote::where('voter_token', $token)
                ->where('participant_id', $this->participant->id)->exists();
            $this->dispatch('vote-error', message: 'Vous avez déjà voté pour un participant. Un seul vote par concours.');
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

            Cookie::queue(self::COOKIE_NAME, $token, self::COOKIE_MINUTES);

            $this->count++;
            $this->hasVoted = true;
            $this->hasVotedAnywhere = true;
            $this->dispatch('vote-cast', participantId: $this->participant->id, token: $token);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                $this->hasVotedAnywhere = true;
                return;
            }
            throw $e;
        }
    }

    protected function resolveVoterToken(): string
    {
        $cookie = (string) request()->cookie(self::COOKIE_NAME, '');
        if ($cookie !== '') {
            return $cookie;
        }
        // Génère un nouveau token et le pousse en cookie pour les requêtes suivantes
        $token = (string) Str::ulid() . bin2hex(random_bytes(8));
        Cookie::queue(self::COOKIE_NAME, $token, self::COOKIE_MINUTES);
        return $token;
    }

    protected function refreshVoteState(): void
    {
        if ($this->voterToken === '') {
            $this->hasVoted = false;
            $this->hasVotedAnywhere = false;
            return;
        }

        $this->hasVotedAnywhere = Vote::where('voter_token', $this->voterToken)->exists();
        $this->hasVoted = $this->hasVotedAnywhere && Vote::where('voter_token', $this->voterToken)
            ->where('participant_id', $this->participant->id)->exists();
    }

    public function render()
    {
        return view('livewire.vote-button');
    }
}
