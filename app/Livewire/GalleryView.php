<?php

namespace App\Livewire;

use App\Livewire\VoteButton;
use App\Models\Participant;
use App\Models\Vote;
use App\Support\ContestSettings;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class GalleryView extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $sort = 'recent';

    #[Url]
    public string $tag = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingSort(): void
    {
        $this->resetPage();
    }

    public function updatingTag(): void
    {
        $this->resetPage();
    }

    public function selectTag(string $value): void
    {
        $this->tag = $this->tag === $value ? '' : $value;
        $this->resetPage();
    }

    protected function contestEnded(): bool
    {
        return now()->greaterThan(ContestSettings::endsAt());
    }

    protected function voterToken(): string
    {
        $cookie = (string) request()->cookie(VoteButton::COOKIE_NAME, '');
        if ($cookie !== '') {
            return $cookie;
        }
        $token = (string) Str::ulid() . bin2hex(random_bytes(8));
        Cookie::queue(VoteButton::COOKIE_NAME, $token, VoteButton::COOKIE_MINUTES);
        return $token;
    }

    protected function votedIds(): Collection
    {
        $token = (string) request()->cookie(VoteButton::COOKIE_NAME, '');
        if ($token === '') {
            return collect();
        }
        return Vote::where('voter_token', $token)->pluck('participant_id');
    }

    public function vote(int $participantId)
    {
        if ($this->contestEnded()) {
            $this->dispatch('toast', type: 'error', message: 'Le concours est terminé. Les votes sont clôturés.');
            return;
        }

        $token = $this->voterToken();
        if ($token === '') {
            $this->dispatch('toast', type: 'error', message: 'Impossible d\'enregistrer votre vote. Activez les cookies puis réessayez.');
            return;
        }

        $ipKey = 'vote:ip:' . request()->ip();
        if (RateLimiter::tooManyAttempts($ipKey, 60)) {
            $this->dispatch('toast', type: 'error', message: 'Trop de tentatives. Réessayez dans une minute.');
            return;
        }
        RateLimiter::hit($ipKey, 60);

        if (Vote::where('voter_token', $token)->exists()) {
            $this->dispatch('toast', type: 'warning', message: 'Vous avez déjà voté pour un participant. Un seul vote par concours.');
            return;
        }

        $participant = Participant::approved()->find($participantId);
        if (! $participant) {
            $this->dispatch('toast', type: 'error', message: 'Ce participant n\'est plus disponible.');
            return;
        }

        try {
            DB::transaction(function () use ($participantId, $token) {
                Vote::create([
                    'participant_id' => $participantId,
                    'voter_token'    => $token,
                    'ip_address'     => request()->ip(),
                    'session_id'     => hash('sha256', session()->getId()),
                    'user_agent'     => substr((string) request()->userAgent(), 0, 255),
                ]);

                Participant::where('id', $participantId)->increment('vote_count');
            });

            $this->dispatch('toast', type: 'success', message: 'Vote enregistré. Merci pour votre soutien !');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() !== '23000') {
                throw $e;
            }
            $this->dispatch('toast', type: 'warning', message: 'Vote déjà enregistré.');
        }
    }

    public function render()
    {
        $search = mb_substr(trim($this->search), 0, 50);
        $tag = mb_substr(trim($this->tag), 0, 50);

        $query = Participant::approved()
            ->with('media')
            ->when(mb_strlen($search) >= 2, fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            }))
            ->when($tag, fn ($q) => $q->where('city', 'like', "%{$tag}%"));

        $query = $this->sort === 'popular'
            ? $query->orderByDesc('vote_count')->orderByDesc('approved_at')
            : $query->orderByDesc('approved_at');

        $participants = $query->paginate(24);
        $votedIds = $this->votedIds()->flip();
        $hasVotedAnywhere = $votedIds->isNotEmpty();

        $cityTags = Participant::approved()
            ->select('city')
            ->groupBy('city')
            ->orderByRaw('COUNT(*) DESC')
            ->limit(8)
            ->pluck('city');

        return view('livewire.gallery-view', [
            'participants' => $participants,
            'cityTags' => $cityTags,
            'votedIds' => $votedIds,
            'hasVotedAnywhere' => $hasVotedAnywhere,
            'contestEndsAt' => ContestSettings::endsAt(),
            'contestEnded' => $this->contestEnded(),
        ]);
    }
}
