<div class="container mx-auto px-4 py-10">

    {{-- Barre de recherche + tri --}}
    <div class="mb-4 flex flex-col gap-3 sm:flex-row">
        <div class="relative flex-1">
            <svg class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"
                 viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="7" /><path d="m21 21-4.3-4.3" />
            </svg>
            <input type="search" wire:model.live.debounce.400ms="search"
                   placeholder="Rechercher par nom ou ville..."
                   class="w-full rounded-full border border-gray-200 bg-white px-4 py-3 pl-11 text-sm shadow-sm
                          focus:border-dinor-red focus:outline-none focus:ring-2 focus:ring-dinor-red/20 transition" />
        </div>
        <div class="relative sm:w-48">
            <select wire:model.live="sort"
                    class="w-full appearance-none rounded-full border border-gray-200 bg-white px-4 py-3 pr-10 text-sm shadow-sm
                           focus:border-dinor-red focus:outline-none focus:ring-2 focus:ring-dinor-red/20 transition cursor-pointer">
                <option value="recent">Plus récents</option>
                <option value="popular">Plus aimés</option>
            </select>
            <svg class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"
                 viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m6 9 6 6 6-6"/>
            </svg>
        </div>
    </div>

    <div class="mb-4 flex items-center justify-between gap-3">
        <div x-data="countdown('{{ $contestEndsAt }}')" class="inline-flex items-center gap-2 rounded-xl border border-dinor-gold/30 bg-dinor-gold/10 px-3 py-1.5 text-xs text-dinor-dark">
            <span class="font-semibold">Fin:</span>
            <span class="font-bold" x-text="label"></span>
        </div>
        @if ($contestEnded)
            <span class="rounded-full bg-red-50 px-3 py-1 text-xs font-semibold text-red-700">Votes clôturés</span>
        @endif
    </div>

    {{-- Tags de filtre rapide --}}
    <div class="mb-6 flex flex-wrap gap-2">
        @foreach ($cityTags as $city)
            @php $isActive = $tag === $city; @endphp
            <button wire:click="selectTag('{{ $city }}')" type="button"
                    class="inline-flex items-center gap-1.5 rounded-full border px-4 py-2 text-sm font-medium transition
                           {{ $isActive
                               ? 'border-dinor-red bg-dinor-red text-white shadow-sm'
                               : 'border-gray-200 bg-white text-gray-700 hover:border-dinor-red hover:text-dinor-red' }}">
                <svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/>
                    <circle cx="12" cy="9" r="2.5"/>
                </svg>
                {{ $city }}
            </button>
        @endforeach
    </div>

    {{-- Carousel de cards --}}
    @if ($participants->count())
        <div
            x-data="{
                scroll(direction) {
                    const track = this.$refs.track;

                    track.scrollBy({
                        left: direction * Math.max(track.clientWidth * 0.88, 280),
                        behavior: 'smooth'
                    });
                }
            }"
            class="relative"
        >
            <div class="mb-3 flex items-center justify-between gap-3">
                <p class="text-sm font-semibold text-gray-500">
                    {{ $participants->firstItem() }}-{{ $participants->lastItem() }} sur {{ $participants->total() }}
                </p>
                <div class="flex items-center gap-2">
                    <button type="button"
                            x-on:click="scroll(-1)"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 bg-white text-dinor-dark shadow-sm transition hover:border-dinor-red hover:text-dinor-red"
                            aria-label="Précédent">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <button type="button"
                            x-on:click="scroll(1)"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 bg-white text-dinor-dark shadow-sm transition hover:border-dinor-red hover:text-dinor-red"
                            aria-label="Suivant">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
            </div>

            <div x-ref="track"
                 class="-mx-4 flex snap-x snap-mandatory gap-4 overflow-x-auto px-4 pb-4 scroll-smooth [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                @foreach ($participants as $i => $p)
                    @php
                        $initials = strtoupper(substr($p->first_name, 0, 1) . substr($p->last_name, 0, 1));
                        $img = $p->getFirstMediaUrl('photo', 'card');
                        $hasVoted = isset($votedIds[$p->id]);
                    @endphp
                    <article wire:key="gallery-carousel-{{ $p->id }}"
                             class="flex min-w-0 shrink-0 basis-[86%] snap-center overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition hover:shadow-dinor sm:basis-[48%] lg:basis-[32%] xl:basis-[24%]">
                        <div class="flex w-full flex-col">
                            <a href="{{ route('participant.show', $p) }}" class="relative block aspect-4/5 w-full overflow-hidden bg-linear-to-br from-dinor-red to-dinor-gold">
                                @if ($img)
                                    <img src="{{ $img }}" alt="{{ $p->full_name }}"
                                         loading="lazy"
                                         class="h-full w-full object-cover transition duration-500 hover:scale-105" />
                                @else
                                    <div class="flex h-full w-full items-center justify-center text-5xl font-extrabold text-white">
                                        {{ $initials }}
                                    </div>
                                @endif

                                @if ($sort === 'popular')
                                    <span class="absolute right-3 top-3 inline-flex items-center rounded-md bg-dinor-red px-2.5 py-1 text-xs font-bold text-white shadow">
                                        #{{ $participants->firstItem() + $i }}
                                    </span>
                                @else
                                    <span class="absolute right-3 top-3 flex h-9 w-9 items-center justify-center rounded-md bg-dinor-red text-xs font-bold uppercase text-white shadow">
                                        {{ $initials }}
                                    </span>
                                @endif
                            </a>

                            <div class="flex flex-1 flex-col px-4 pb-4 pt-3">
                                <a href="{{ route('participant.show', $p) }}"
                                   class="block truncate text-base font-semibold text-dinor-dark transition hover:text-dinor-red">
                                    {{ $p->full_name }}
                                </a>
                                <p class="truncate text-sm text-gray-500">{{ $p->city }}</p>

                                <div class="mt-3 flex items-center justify-between gap-3">
                                    <button wire:click="vote({{ $p->id }})"
                                            @if($hasVoted || $contestEnded) disabled @endif
                                            type="button"
                                            class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-sm transition disabled:cursor-default
                                                   {{ $hasVoted
                                                       ? 'border-dinor-red bg-dinor-red/5 text-dinor-red font-semibold'
                                                       : ($contestEnded
                                                            ? 'border-gray-200 bg-gray-100 text-gray-400'
                                                            : 'border-gray-200 bg-white text-gray-600 hover:border-dinor-red hover:text-dinor-red') }}">
                                        <svg class="h-4 w-4 {{ $hasVoted ? 'fill-dinor-red' : 'fill-none' }}"
                                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                                             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                                        </svg>
                                        <span class="font-semibold tabular-nums">{{ $p->vote_count }}</span>
                                    </button>

                                    <a href="{{ route('participant.show', $p) }}"
                                       class="inline-flex items-center gap-1 text-sm font-semibold text-dinor-red transition hover:underline">
                                        Voir
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    @else
        <p class="py-16 text-center text-gray-500">Aucune participation trouvée.</p>
    @endif

    <div class="mt-10">{{ $participants->links() }}</div>
</div>
