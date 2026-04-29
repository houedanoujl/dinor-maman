<div class="container mx-auto p-4">
    <div class="flex flex-col sm:flex-row gap-3 mb-6">
        <input type="search" wire:model.live.debounce.400ms="search"
               placeholder="Rechercher par nom ou ville..."
               class="input-dinor flex-1" />
        <select wire:model.live="sort" class="input-dinor sm:w-48">
            <option value="recent">Plus récents</option>
            <option value="popular">Plus aimés</option>
        </select>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 auto-rows-[200px] sm:auto-rows-[260px]">
        @forelse ($participants as $i => $p)
            <div class="relative rounded-2xl overflow-hidden shadow-md group
                        {{ $i % 7 === 0 ? 'row-span-2 col-span-2' : '' }}
                        {{ $i % 11 === 5 ? 'row-span-2' : '' }}">
                <img src="{{ $p->getFirstMediaUrl('photo', 'card') }}"
                     alt="{{ $p->full_name }}"
                     loading="lazy"
                     class="w-full h-full object-cover group-hover:scale-105 transition duration-500" />
                <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/80 to-transparent p-3 text-white">
                    <p class="font-bold truncate">{{ $p->full_name }}</p>
                    <p class="text-xs opacity-80 truncate">{{ $p->city }}</p>
                    <div class="mt-2">
                        <livewire:vote-button :participant="$p" :key="'vote-'.$p->id" />
                    </div>
                </div>
            </div>
        @empty
            <p class="col-span-full text-center text-gray-500 py-12">Aucun participant trouvé.</p>
        @endforelse
    </div>

    <div class="mt-8">{{ $participants->links() }}</div>
</div>
