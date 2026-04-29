<div class="container mx-auto max-w-xl px-4 py-12">
    <div class="mb-8 text-center">
        <p class="text-xs font-bold uppercase tracking-wider text-dinor-gold">Participer</p>
        <h1 class="mt-2 font-display text-3xl font-bold text-dinor-dark md:text-4xl">Partagez votre photo</h1>
        <p class="mt-2 text-gray-600">Une photo, vos coordonnées, et c'est parti.</p>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-2xl border border-dinor-gold/40 bg-dinor-gold/10 p-4 text-sm text-dinor-gold">
            {{ session('success') }}
        </div>
    @endif

    <form wire:submit="submit" class="space-y-4 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label class="block text-sm font-medium">Prénom</label>
                <input type="text" wire:model="first_name" class="input-dinor mt-1" autocomplete="given-name" />
                @error('first_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium">Nom</label>
                <input type="text" wire:model="last_name" class="input-dinor mt-1" autocomplete="family-name" />
                @error('last_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium">Téléphone</label>
            <input type="tel" wire:model="phone" class="input-dinor mt-1" placeholder="+225 ..." autocomplete="tel" />
            @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium">Ville / Quartier</label>
            <input type="text" wire:model="city" class="input-dinor mt-1" autocomplete="address-level2" />
            @error('city') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium">Email <span class="text-gray-400">(facultatif)</span></label>
            <input type="email" wire:model="email" class="input-dinor mt-1" autocomplete="email" />
            @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div x-data="{ uploading: false, progress: 0 }"
             x-on:livewire-upload-start="uploading = true"
             x-on:livewire-upload-finish="uploading = false; progress = 0"
             x-on:livewire-upload-cancel="uploading = false"
             x-on:livewire-upload-error="uploading = false"
             x-on:livewire-upload-progress="progress = $event.detail.progress">
            <label class="block text-sm font-medium">Votre photo</label>
            <input type="file" wire:model="photo" accept="image/jpeg,image/png,image/webp"
                   class="mt-1 block w-full rounded-xl border border-dashed border-gray-300 p-3 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-dinor-red file:px-3 file:py-1.5 file:text-white" />
            <div x-show="uploading" class="mt-2 h-2 rounded bg-gray-200">
                <div class="h-2 rounded bg-dinor-red transition-all" :style="`width:${progress}%`"></div>
            </div>
            @error('photo') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror

            @if ($photo)
                <img src="{{ $photo->temporaryUrl() }}" alt="Aperçu" class="mt-3 max-h-64 rounded-xl" />
            @endif
        </div>

        <button type="submit"
                class="btn-dinor w-full py-3 text-base disabled:opacity-50"
                wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="submit">Envoyer ma participation</span>
            <span wire:loading wire:target="submit">Envoi en cours...</span>
        </button>
    </form>
</div>
