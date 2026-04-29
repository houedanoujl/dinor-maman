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

    {{-- Grille de cards --}}
    <div class="columns-2 gap-3 sm:columns-2 lg:columns-3 xl:columns-4 [column-fill:balance]">
        @forelse ($participants as $i => $p)
            @php
                $initials = strtoupper(substr($p->first_name, 0, 1) . substr($p->last_name, 0, 1));
                $img = $p->getFirstMediaUrl('photo', 'card');
                $isFirst = ($i === 0);
            @endphp
            <article class="mb-3 break-inside-avoid overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition hover:shadow-dinor
                            {{ $isFirst ? 'col-span-2' : '' }}">
                {{-- Image --}}
                <a href="{{ route('participant.show', $p) }}" class="relative block w-full overflow-hidden
                            {{ $isFirst ? 'aspect-video' : 'aspect-4/3' }}">
                    @if ($img)
                        <img src="{{ $img }}" alt="{{ $p->full_name }}"
                             loading="lazy"
                             class="h-full w-full object-cover transition duration-500 hover:scale-105" />
                    @else
                        <div class="flex h-full w-full items-center justify-center bg-linear-to-br from-dinor-red to-dinor-gold text-4xl font-extrabold text-white">
                            {{ $initials }}
                        </div>
                    @endif
                    {{-- Badge initiales / rang --}}
                    @if ($sort === 'popular')
                        <span class="absolute right-2.5 top-2.5 inline-flex items-center rounded-md bg-dinor-red px-2 py-1 text-xs font-bold text-white shadow">
                            #{{ $participants->firstItem() + $i }}
                        </span>
                    @else
                        <span class="absolute right-2.5 top-2.5 flex h-8 w-8 items-center justify-center rounded-md bg-dinor-red text-xs font-bold uppercase text-white shadow">
                            {{ $initials }}
                        </span>
                    @endif
                </a>

                {{-- Infos sous l'image --}}
                <div class="px-3 pb-3 pt-2.5">
                    <a href="{{ route('participant.show', $p) }}"
                       class="block truncate text-sm font-semibold text-dinor-dark hover:text-dinor-red transition">
                        {{ $p->full_name }}
                    </a>
                    <p class="truncate text-xs text-gray-500">{{ $p->city }}</p>
                    <div class="mt-2">
                        @php $hasVoted = isset($votedIds[$p->id]); @endphp
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
                    </div>
                </div>
            </article>
        @empty
            <p class="col-span-full py-16 text-center text-gray-500">Aucune participation trouvée.</p>
        @endforelse
    </div>

    <div class="mt-10">{{ $participants->links() }}</div>
</div>
