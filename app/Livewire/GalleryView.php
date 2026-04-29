<?php

namespace App\Livewire;

use App\Models\Participant;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class GalleryView extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $sort = 'recent'; // recent | popular

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingSort(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Participant::approved()
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('first_name', 'like', "%{$this->search}%")
                  ->orWhere('last_name', 'like', "%{$this->search}%")
                  ->orWhere('city', 'like', "%{$this->search}%");
            }));

        $query = $this->sort === 'popular'
            ? $query->orderByDesc('vote_count')->orderByDesc('approved_at')
            : $query->orderByDesc('approved_at');

        return view('livewire.gallery-view', [
            'participants' => $query->paginate(24),
        ]);
    }
}
