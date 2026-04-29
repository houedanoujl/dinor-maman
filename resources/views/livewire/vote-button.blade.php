<button wire:click="toggleVote"
        wire:loading.attr="disabled"
        @disabled($hasVoted)
        class="flex items-center gap-1.5 px-3 py-1.5 rounded-full backdrop-blur
               {{ $hasVoted ? 'bg-dinor-red text-white' : 'bg-white/90 text-gray-800 hover:bg-white' }}
               transition disabled:cursor-default">
    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
        <path d="M12 21s-7.5-4.6-9.5-9.1C1.1 8.6 3.4 5 7 5c2 0 3.6 1 5 2.6C13.4 6 15 5 17 5c3.6 0 5.9 3.6 4.5 6.9C19.5 16.4 12 21 12 21z" />
    </svg>
    <span class="font-bold text-sm">{{ $count }}</span>
</button>
