@props(['compact' => false])

<a href="{{ route('home') }}" {{ $attributes->merge(['class' => 'flex items-center gap-3 group']) }}>
    <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-dinor-red text-white shadow-dinor transition group-hover:scale-105">
        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
        </svg>
    </span>
    <span class="leading-tight">
        <span class="block font-display text-lg font-bold text-dinor-dark">Dinor</span>
        @unless($compact)
            <span class="block text-[11px] uppercase tracking-wider text-dinor-gold">cuisine avec maman</span>
        @endunless
    </span>
</a>
