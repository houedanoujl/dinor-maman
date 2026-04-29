<div class="container mx-auto max-w-xl px-4 py-12">
    @if ($submitted)
        <div class="rounded-2xl border border-dinor-gold/30 bg-white p-8 text-center shadow-sm">
            <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-dinor-gold/10">
                <svg class="h-8 w-8 text-dinor-gold" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
            </div>
            <h2 class="font-display text-2xl font-bold text-dinor-dark">Participation envoyee</h2>
            <p class="mt-3 text-gray-600">
                Votre photo est en attente de validation par notre equipe.<br>
                Vous serez notifie(e) des qu'elle sera approuvee.
            </p>
            <div class="mt-6 rounded-xl bg-dinor-cream p-4 text-left text-sm text-gray-600">
                <p class="font-semibold text-dinor-dark">Ce que nous verifions :</p>
                <ul class="mt-2 space-y-1">
                    <li class="flex items-start gap-2"><span class="mt-0.5 text-dinor-gold">?</span> Photo claire et nette</li>
                    <li class="flex items-start gap-2"><span class="mt-0.5 text-dinor-gold">?</span> Vous et votre maman visibles</li>
                    <li class="flex items-start gap-2"><span class="mt-0.5 text-dinor-gold">?</span> Contexte de cuisine / repas familial</li>
                    <li class="flex items-start gap-2"><span class="mt-0.5 text-dinor-gold">?</span> Contenu approprie</li>
                </ul>
            </div>
            <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-center">
                <a href="{{ route('contest.gallery') }}" class="inline-flex items-center justify-center rounded-full bg-dinor-red px-6 py-2.5 font-semibold text-white shadow-sm transition hover:bg-dinor-red/90">Voir la galerie</a>
                <button wire:click="$set('submitted', false)" type="button" class="inline-flex items-center justify-center rounded-full border border-gray-200 px-6 py-2.5 font-semibold text-dinor-dark transition hover:border-dinor-red hover:text-dinor-red">Soumettre une autre photo</button>
            </div>
        </div>
    @else
        <div class="mb-6 text-center">
            <p class="text-xs font-bold uppercase tracking-wider text-dinor-gold">Participer</p>
            <h1 class="mt-2 font-display text-3xl font-bold text-dinor-dark md:text-4xl">Partagez votre photo</h1>
            <p class="mt-2 text-gray-600">Une photo, vos coordonnees, et c'est parti.</p>
        </div>

        @if ($contestEnded)
            <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                Le concours est termine. Les nouvelles participations sont cloturees.
            </div>
        @endif

        <div class="mb-6 rounded-2xl border border-dinor-gold/30 bg-dinor-gold/5 p-4">
            <p class="flex items-center gap-2 text-sm font-semibold text-dinor-dark">
                <svg class="h-4 w-4 shrink-0 text-dinor-gold" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                Criteres de selection de la photo
            </p>
            <ul class="mt-3 space-y-1.5 text-sm text-gray-600">
                <li class="flex items-start gap-2"><span class="mt-0.5 shrink-0 font-bold text-dinor-gold">-</span><span>Photo nette, bien eclairee</span></li>
                <li class="flex items-start gap-2"><span class="mt-0.5 shrink-0 font-bold text-dinor-gold">-</span><span>Vous et votre maman (ou figure maternelle) clairement visibles</span></li>
                <li class="flex items-start gap-2"><span class="mt-0.5 shrink-0 font-bold text-dinor-gold">-</span><span>Contexte de cuisine ou repas familial</span></li>
                <li class="flex items-start gap-2"><span class="mt-0.5 shrink-0 font-bold text-dinor-gold">-</span><span>Contenu approprie</span></li>
            </ul>
        </div>

        <form wire:submit="submit" class="space-y-4 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium">Prenom</label>
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
                <label class="block text-sm font-medium">Telephone</label>
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
                <input type="file" wire:model="photo" accept="image/jpeg,image/png,image/webp" class="mt-1 block w-full rounded-xl border border-dashed border-gray-300 p-3 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-dinor-red file:px-3 file:py-1.5 file:text-white" />
                <div x-show="uploading" class="mt-2 h-2 rounded bg-gray-200">
                    <div class="h-2 rounded bg-dinor-red transition-all" :style="`width:${progress}%`"></div>
                </div>
                @error('photo') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror

                @if ($photo)
                    <img src="{{ $photo->temporaryUrl() }}" alt="Apercu" class="mt-3 max-h-64 rounded-xl" />
                @endif
            </div>

            <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" wire:model="consent" class="mt-0.5 h-4 w-4 shrink-0 rounded border-gray-300 text-dinor-red focus:ring-dinor-red" />
                    <span class="text-sm text-gray-600">
                        J'accepte le
                        <a href="{{ route('reglement') }}" target="_blank" class="font-semibold text-dinor-red underline hover:text-dinor-red/80">reglement du concours</a>
                        et j'autorise l'utilisation de ma photo dans le cadre de l'evenement.
                    </span>
                </label>
                @error('consent') <p class="mt-2 text-sm text-red-600">Vous devez accepter le reglement pour participer.</p> @enderror
            </div>

            <button type="submit" class="btn-dinor w-full py-3 text-base disabled:opacity-50" wire:loading.attr="disabled" @if($contestEnded) disabled @endif>
                <span wire:loading.remove wire:target="submit">Envoyer ma participation</span>
                <span wire:loading wire:target="submit">Envoi en cours...</span>
            </button>
        </form>
    @endif
</div>
