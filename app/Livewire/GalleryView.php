<?php

namespace App\Livewire;

use App\Models\Participant;
use App\Models\Vote;
use App\Support\ContestSettings;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
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

    protected function votedIds(): Collection
    {
        return Vote::where(function ($q) {
                $q->where('ip_address', request()->ip())
                  ->orWhere('session_id', hash('sha256', session()->getId()));
            })
            ->pluck('participant_id');
    }

    public function vote(int $participantId): void
    {
        if ($this->contestEnded()) {
            $this->dispatch('toast', type: 'error', message: 'Le concours est termin�. Les votes sont d�sormais cl�tur�s.');
            return;
        }

        $ipKey = 'vote:ip:' . request()->ip();
        if (RateLimiter::tooManyAttempts($ipKey, 30)) {
            $this->dispatch('toast', type: 'error', message: 'Trop de votes en peu de temps. R�essayez dans une minute.');
            return;
        }
        RateLimiter::hit($ipKey, 60);

        $actionKey = 'vote:action:' . request()->ip() . ':' . $participantId;
        if (RateLimiter::tooManyAttempts($actionKey, 1)) {
            $this->dispatch('toast', type: 'warning', message: 'Patientez quelques secondes avant de revoter.');
            return;
        }
        RateLimiter::hit($actionKey, 3);

        $participant = Participant::approved()->find($participantId);
        if (! $participant) {
            $this->dispatch('toast', type: 'error', message: 'Ce participant n\'est plus disponible.');
            return;
        }

        $alreadyVoted = Vote::where('participant_id', $participantId)
            ->where(function ($q) {
                $q->where('ip_address', request()->ip())
                  ->orWhere('session_id', hash('sha256', session()->getId()));
            })->exists();

        if ($alreadyVoted) {
            $this->dispatch('toast', type: 'warning', message: 'Vous avez d�j� vot� pour cette photo.');
            return;
        }

        try {
            DB::transaction(function () use ($participantId) {
                Vote::create([
                    'participant_id'     => $participantId,
                    'ip_address'         => request()->ip(),
                    'session_id'         => hash('sha256', session()->getId()),
                    'device_fingerprint' => hash('sha256', implode('|', [
                        request()->ip(),
                        request()->userAgent(),
                        request()->header('Accept-Language'),
                        session()->getId(),
                    ])),
                    'user_agent'         => substr((string) request()->userAgent(), 0, 255),
                ]);

                Participant::where('id', $participantId)->increment('vote_count');
            });

            $this->dispatch('toast', type: 'success', message: 'Vote enregistr� avec succ�s. Merci pour votre soutien.');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() !== '23000') {
                throw $e;
            }

            $this->dispatch('toast', type: 'warning', message: 'Vous avez d�j� vot� pour cette photo.');
        }
    }

    public function render()
    {
        $query = Participant::approved()
            ->with('media')
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('first_name', 'like', "%{$this->search}%")
                  ->orWhere('last_name', 'like', "%{$this->search}%")
                  ->orWhere('city', 'like', "%{$this->search}%");
            }))
            ->when($this->tag, fn ($q) => $q->where('city', 'like', "%{$this->tag}%"));

        $query = $this->sort === 'popular'
            ? $query->orderByDesc('vote_count')->orderByDesc('approved_at')
            : $query->orderByDesc('approved_at');

        $participants = $query->paginate(24);
        $votedIds = $this->votedIds()->flip();

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
            'contestEndsAt' => ContestSettings::endsAt(),
            'contestEnded' => $this->contestEnded(),
        ]);
    }
}
