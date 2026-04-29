<div class="max-w-xl mx-auto p-6">
    @if (session('success'))
        <div class="mb-6 rounded-xl bg-dinor-gold/10 border border-dinor-gold p-4 text-dinor-gold">
            {{ session('success') }}
        </div>
    @endif

    <h1 class="text-3xl font-bold text-dinor-red mb-2">Un moment de cuisine avec maman</h1>
    <p class="text-gray-600 mb-6">Partagez votre plus belle photo et tentez de gagner !</p>

    <form wire:submit="submit" class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium">Prénom</label>
                <input type="text" wire:model="first_name" class="input-dinor" />
                @error('first_name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium">Nom</label>
                <input type="text" wire:model="last_name" class="input-dinor" />
                @error('last_name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium">Téléphone</label>
            <input type="tel" wire:model="phone" class="input-dinor" placeholder="+225 ..." />
            @error('phone') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium">Ville / Quartier</label>
            <input type="text" wire:model="city" class="input-dinor" />
            @error('city') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium">Email (facultatif)</label>
            <input type="email" wire:model="email" class="input-dinor" />
            @error('email') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div x-data="{ uploading: false, progress: 0 }"
             x-on:livewire-upload-start="uploading = true"
             x-on:livewire-upload-finish="uploading = false; progress = 0"
             x-on:livewire-upload-cancel="uploading = false"
             x-on:livewire-upload-error="uploading = false"
             x-on:livewire-upload-progress="progress = $event.detail.progress">
            <label class="block text-sm font-medium">Votre photo</label>
            <input type="file" wire:model="photo" accept="image/*" class="block w-full" />
            <div x-show="uploading" class="mt-2 h-2 bg-gray-200 rounded">
                <div class="h-2 bg-dinor-red rounded transition-all" :style="`width:${progress}%`"></div>
            </div>
            @error('photo') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror

            @if ($photo)
                <img src="{{ $photo->temporaryUrl() }}" class="mt-3 rounded-xl max-h-64" />
            @endif
        </div>

        <button type="submit"
                class="w-full bg-dinor-red text-white font-bold py-3 rounded-xl hover:bg-dinor-red/90 transition disabled:opacity-50"
                wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="submit">Participer</span>
            <span wire:loading wire:target="submit">Envoi en cours...</span>
        </button>
    </form>
</div>
