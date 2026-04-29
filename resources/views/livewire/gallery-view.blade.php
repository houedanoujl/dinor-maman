<div class="container mx-auto px-4 py-10">
    <div class="mb-8 flex flex-col gap-2">
        <p class="text-xs font-bold uppercase tracking-wider text-dinor-gold">Galerie</p>
        <h1 class="font-display text-3xl font-bold text-dinor-dark md:text-4xl">Toutes les participations</h1>
    </div>

    <div class="mb-6 flex flex-col gap-3 sm:flex-row">
        <div class="relative flex-1">
            <svg class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-dinor-gold"
                 viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="7" /><path d="m21 21-4.3-4.3" />
            </svg>
            <input type="search" wire:model.live.debounce.400ms="search"
                   placeholder="Rechercher par nom ou ville..."
                   class="input-dinor !pl-11" />
        </div>
        <select wire:model.live="sort" class="input-dinor sm:w-56">
            <option value="recent">Plus récents</option>
            <option value="popular">Plus aimés</option>
        </select>
    </div>

    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 auto-rows-[200px] sm:auto-rows-[260px]">
        @forelse ($participants as $i => $p)
            <article class="relative overflow-hidden rounded-2xl bg-white shadow-sm group
                        {{ $i % 7 === 0 ? 'row-span-2 col-span-2' : '' }}
                        {{ $i % 11 === 5 ? 'row-span-2' : '' }}">
                <a href="{{ route('participant.show', $p) }}" class="block h-full w-full">
                    @if ($p->getFirstMediaUrl('photo', 'card'))
                        <img src="{{ $p->getFirstMediaUrl('photo', 'card') }}"
                             alt="{{ $p->full_name }}"
                             loading="lazy"
                             class="h-full w-full object-cover transition duration-500 group-hover:scale-105" />
                    @else
                        <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-dinor-red to-dinor-gold text-5xl font-extrabold text-white">
                            {{ strtoupper(substr($p->first_name, 0, 1) . substr($p->last_name, 0, 1)) }}
                        </div>
                    @endif
                </a>

                <span class="absolute right-3 top-3 rounded-md bg-white/95 px-2 py-1 text-[10px] font-bold uppercase tracking-wider text-dinor-gold">
                    {{ strtoupper(substr($p->first_name, 0, 1) . substr($p->last_name, 0, 1)) }}
                </span>

                <div class="pointer-events-none absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/85 via-black/40 to-transparent p-3 text-white">
                    <a href="{{ route('participant.show', $p) }}"
                       class="pointer-events-auto block truncate font-semibold hover:text-dinor-gold">
                        {{ $p->full_name }}
                    </a>
                    <p class="truncate text-xs opacity-80">{{ $p->city }}</p>
                    <div class="pointer-events-auto mt-2">
                        <livewire:vote-button :participant="$p" :key="'vote-'.$p->id" />
                    </div>
                </div>
            </article>
        @empty
            <p class="col-span-full py-16 text-center text-gray-500">Aucune participation trouvée.</p>
        @endforelse
    </div>

    <div class="mt-10">{{ $participants->links() }}</div>
</div>
