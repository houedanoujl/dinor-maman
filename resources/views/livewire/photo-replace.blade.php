<div>
    @if(! $editing)
        <button type="button" wire:click="startEdit"
                class="inline-flex items-center gap-2 rounded-full border border-dinor-red px-4 py-2 text-sm font-semibold text-dinor-red transition hover:bg-dinor-red hover:text-white">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
            </svg>
            Changer ma photo
        </button>
    @else
        <form wire:submit.prevent="save" class="rounded-2xl border border-dinor-gold/30 bg-dinor-cream/40 p-4">
            <p class="mb-3 text-sm font-semibold text-dinor-dark">Choisissez une nouvelle photo</p>

            <input type="file" wire:model="photo" accept="image/jpeg,image/png,image/webp"
                   class="block w-full text-sm text-gray-700 file:mr-3 file:rounded-full file:border-0 file:bg-dinor-red file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-dinor-red/90" />

            @error('photo') <p class="mt-2 text-xs text-red-600">{{ $message }}</p> @enderror

            <div wire:loading wire:target="photo" class="mt-2 text-xs text-gray-500">Téléversement en cours…</div>

            @if($photo)
                <div class="mt-3">
                    <p class="mb-1 text-xs text-gray-500">Aperçu :</p>
                    <img src="{{ $photo->temporaryUrl() }}" alt="" class="h-40 w-40 rounded-xl object-cover" />
                </div>
            @endif

            <div class="mt-4 flex flex-col gap-2 sm:flex-row">
                <button type="submit" wire:loading.attr="disabled" wire:target="save,photo"
                        class="btn-dinor justify-center text-sm disabled:opacity-50">
                    <span wire:loading.remove wire:target="save">Enregistrer la nouvelle photo</span>
                    <span wire:loading wire:target="save">Enregistrement…</span>
                </button>
                <button type="button" wire:click="cancel"
                        class="rounded-full border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-600 hover:border-gray-400">
                    Annuler
                </button>
            </div>

            <p class="mt-3 text-xs text-gray-500">
                Une nouvelle photo repassera en attente de validation par l'équipe.
            </p>
        </form>
    @endif
</div>
