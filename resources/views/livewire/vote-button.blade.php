<button wire:click="toggleVote"
        wire:loading.attr="disabled"
        @disabled($hasVoted)
        class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 backdrop-blur transition disabled:cursor-default
               {{ $hasVoted
                    ? 'bg-dinor-red text-white shadow-dinor'
                    : 'bg-white/95 text-dinor-dark ring-1 ring-gray-200 hover:bg-dinor-red hover:text-white hover:ring-dinor-red' }}"
        type="button"
        aria-label="{{ $hasVoted ? 'Vous avez déjà voté' : 'Voter pour ' . $participant->full_name }}">
    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
        <path d="M12 21s-7.5-4.6-9.5-9.1C1.1 8.6 3.4 5 7 5c2 0 3.6 1 5 2.6C13.4 6 15 5 17 5c3.6 0 5.9 3.6 4.5 6.9C19.5 16.4 12 21 12 21z" />
    </svg>
    <span class="text-sm font-bold tabular-nums">{{ $count }}</span>
</button>
