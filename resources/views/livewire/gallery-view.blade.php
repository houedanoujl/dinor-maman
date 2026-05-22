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

    {{-- Grille style Instagram --}}
    @if ($participants->count())
        <div class="mb-4 flex items-center justify-between gap-3">
            <p class="text-sm font-semibold text-gray-500">
                {{ $participants->firstItem() }}-{{ $participants->lastItem() }} sur {{ $participants->total() }}
            </p>
        </div>

        <div class="grid grid-cols-2 gap-1 sm:gap-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
            @foreach ($participants as $i => $p)
                @php
                    $initials = strtoupper(substr($p->first_name, 0, 1) . substr($p->last_name, 0, 1));
                    $img = $p->getFirstMediaUrl('photo', 'card');
                    $hasVoted = isset($votedIds[$p->id]);
                    $globalRank = $participants->firstItem() + $i;
                @endphp

                <article wire:key="gallery-grid-{{ $p->id }}"
                         class="group relative aspect-square overflow-hidden bg-gray-100 sm:rounded-lg">

                    {{-- Badge classement (top 3 seulement) --}}
                    @if ($sort === 'popular' && $globalRank <= 3)
                        <span class="absolute left-2 top-2 z-10 flex h-7 w-7 items-center justify-center rounded-full {{ $globalRank === 1 ? 'bg-dinor-gold' : ($globalRank === 2 ? 'bg-gray-300' : 'bg-amber-700') }} text-xs font-bold text-white shadow-md">
                            {{ $globalRank }}
                        </span>
                    @endif

                    <a href="{{ route('participant.show', $p) }}" class="block h-full w-full">
                        @if ($img)
                            <img src="{{ $img }}" alt="{{ $p->full_name }}"
                                 loading="lazy"
                                 class="h-full w-full object-cover transition duration-500 group-hover:scale-105" />
                        @else
                            <div class="flex h-full w-full items-center justify-center bg-linear-to-br from-dinor-red to-dinor-gold text-4xl font-extrabold text-white">
                                {{ $initials }}
                            </div>
                        @endif
                    </a>

                    {{-- Overlay permanent : dégradé sous infos --}}
                    <div class="pointer-events-none absolute inset-x-0 bottom-0 flex flex-col justify-end bg-linear-to-t from-black/90 via-black/60 to-transparent pt-8">
                        <div class="pointer-events-auto px-3 pb-3 text-white">
                            <p class="truncate text-sm font-bold drop-shadow">{{ $p->full_name }}</p>
                            <p class="truncate text-xs text-white/80">{{ $p->city }}</p>
                            <div class="mt-2 flex items-center justify-between">
                                <span class="inline-flex items-center gap-1 text-sm font-semibold">
                                    <svg class="h-4 w-4 {{ $hasVoted ? 'fill-dinor-red text-dinor-red' : 'fill-white text-white' }}"
                                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                                         stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                                    </svg>
                                    {{ $p->vote_count }}
                                </span>
                                <button wire:click="vote({{ $p->id }})"
                                        @if($hasVoted || $contestEnded) disabled @endif
                                        type="button"
                                        class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-semibold shadow-md transition
                                               {{ $hasVoted
                                                   ? 'bg-dinor-red text-white'
                                                   : ($contestEnded
                                                        ? 'bg-white/20 text-white/50'
                                                        : 'bg-white text-dinor-dark hover:bg-dinor-red hover:text-white') }}">
                                    {{ $hasVoted ? 'Voté' : 'Voter' }}
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Indicateur "voté" toujours visible --}}
                    @if ($hasVoted)
                        <span class="absolute right-2 top-2 z-10 flex h-7 w-7 items-center justify-center rounded-full bg-dinor-red text-white shadow-md">
                            <svg class="h-3.5 w-3.5 fill-current" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                            </svg>
                        </span>
                    @endif
                </article>
            @endforeach
        </div>
    @else
        <p class="py-16 text-center text-gray-500">Aucune participation trouvée.</p>
    @endif

    <div class="mt-10">{{ $participants->links() }}</div>
</div>
