@php $contestEnded = now()->greaterThan(config('contest.ends_at')); @endphp
<button wire:click="toggleVote"
        wire:loading.attr="disabled"
        @disabled($hasVoted || $contestEnded)
        class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-sm transition disabled:cursor-default
               {{ $hasVoted
                    ? 'border-dinor-red bg-dinor-red/5 text-dinor-red font-semibold'
                    : ($contestEnded
                        ? 'border-gray-200 bg-gray-100 text-gray-400'
                        : 'border-gray-200 bg-white text-gray-600 hover:border-dinor-red hover:text-dinor-red') }}"
        type="button"
        aria-label="{{ $hasVoted ? 'Vous avez déjà voté' : 'Voter pour ' . $participant->full_name }}">
    <svg class="h-4 w-4 {{ $hasVoted ? 'fill-dinor-red' : 'fill-none' }}"
         viewBox="0 0 24 24"
         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
         aria-hidden="true">
        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
    </svg>
    <span class="font-semibold tabular-nums">{{ $count }}</span>
</button>
