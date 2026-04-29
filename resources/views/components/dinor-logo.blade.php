@props(['compact' => false])

<a href="{{ route('home') }}" {{ $attributes->merge(['class' => 'flex items-center gap-2 group']) }}>
    {{-- Icône cœur + couverts croisés --}}
    <span class="relative inline-flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-full bg-dinor-red text-white shadow-dinor transition group-hover:scale-105">
        <svg viewBox="0 0 32 32" class="h-7 w-7" fill="currentColor" aria-hidden="true">
            <!-- cœur -->
            <path d="M16 27 C16 27 4 19.5 4 11.5 C4 7.9 6.9 5 10.5 5 C12.6 5 14.5 6.1 16 7.8 C17.5 6.1 19.4 5 21.5 5 C25.1 5 28 7.9 28 11.5 C28 19.5 16 27 16 27Z" fill="white" opacity="0.25"/>
            <!-- fourchette (gauche) -->
            <line x1="11" y1="8" x2="10" y2="24" stroke="white" stroke-width="1.6" stroke-linecap="round"/>
            <line x1="9.5" y1="8" x2="9.5" y2="13" stroke="white" stroke-width="1.3" stroke-linecap="round"/>
            <line x1="11" y1="8" x2="11" y2="13" stroke="white" stroke-width="1.3" stroke-linecap="round"/>
            <line x1="12.5" y1="8" x2="12.5" y2="13" stroke="white" stroke-width="1.3" stroke-linecap="round"/>
            <!-- cuillère (droite) -->
            <ellipse cx="21" cy="10" rx="2" ry="2.5" fill="white"/>
            <line x1="21" y1="12.5" x2="21" y2="24" stroke="white" stroke-width="1.6" stroke-linecap="round"/>
        </svg>
    </span>
    <span class="leading-snug">
        <span class="block font-display text-base font-bold leading-tight text-dinor-dark">Un moment de</span>
        <span class="block font-display text-base font-bold leading-tight text-dinor-dark">cuisine avec maman</span>
    </span>
</a>
