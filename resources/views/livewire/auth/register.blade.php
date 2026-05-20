<div class="container mx-auto max-w-xl px-4 py-12">
    <div class="mb-6 text-center">
        <p class="text-xs font-bold uppercase tracking-wider text-dinor-gold">Inscription</p>
        <h1 class="mt-2 font-display text-3xl font-bold text-dinor-dark md:text-4xl">Créez votre compte</h1>
        <p class="mt-2 text-gray-600">Pour voter ou participer au concours.</p>
    </div>

    <form wire:submit="submit" class="space-y-5 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">

        {{-- Choix du rôle --}}
        <div>
            <label class="block text-sm font-semibold text-dinor-dark mb-3">Je m'inscris en tant que :</label>
            <div class="grid gap-3 sm:grid-cols-2">
                <label class="relative cursor-pointer">
                    <input type="radio" wire:model.live="role" value="voter" class="peer sr-only" />
                    <div class="rounded-2xl border-2 border-gray-200 p-4 transition peer-checked:border-dinor-red peer-checked:bg-dinor-red/5 hover:border-dinor-red/50">
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-dinor-red/10 text-dinor-red">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                            <div>
                                <p class="font-semibold text-dinor-dark">Votant</p>
                                <p class="text-xs text-gray-500">Voter pour les photos</p>
                            </div>
                        </div>
                    </div>
                </label>

                <label class="relative cursor-pointer">
                    <input type="radio" wire:model.live="role" value="participant" class="peer sr-only" />
                    <div class="rounded-2xl border-2 border-gray-200 p-4 transition peer-checked:border-dinor-red peer-checked:bg-dinor-red/5 hover:border-dinor-red/50">
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-dinor-gold/10 text-dinor-gold">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                            <div>
                                <p class="font-semibold text-dinor-dark">Participant</p>
                                <p class="text-xs text-gray-500">Soumettre une photo</p>
                            </div>
                        </div>
                    </div>
                </label>
            </div>
            @error('role') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium">Nom complet</label>
            <input type="text" wire:model="name" class="input-dinor mt-1" autocomplete="name" />
            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium">Email</label>
            <input type="email" wire:model="email" class="input-dinor mt-1" autocomplete="email" />
            @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="block text-sm font-medium">Mot de passe</label>
                <input type="password" wire:model="password" class="input-dinor mt-1" autocomplete="new-password" />
                @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium">Confirmation</label>
                <input type="password" wire:model="password_confirmation" class="input-dinor mt-1" autocomplete="new-password" />
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
            <label class="flex items-start gap-3 cursor-pointer">
                <input type="checkbox" wire:model="consent" class="mt-0.5 h-4 w-4 shrink-0 rounded border-gray-300 text-dinor-red focus:ring-dinor-red" />
                <span class="text-sm text-gray-600">
                    J'accepte les
                    <a href="{{ route('cgu') }}" target="_blank" class="font-semibold text-dinor-red underline">CGU</a>
                    et le
                    <a href="{{ route('reglement') }}" target="_blank" class="font-semibold text-dinor-red underline">règlement du concours</a>.
                </span>
            </label>
            @error('consent') <p class="mt-2 text-sm text-red-600">Vous devez accepter les conditions.</p> @enderror
        </div>

        <button type="submit" class="btn-dinor w-full py-3 text-base disabled:opacity-50" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="submit">Créer mon compte</span>
            <span wire:loading wire:target="submit">Création…</span>
        </button>

        <p class="text-center text-sm text-gray-500">
            Déjà inscrit ?
            <a href="{{ route('login') }}" class="font-semibold text-dinor-red hover:underline">Se connecter</a>
        </p>
    </form>
</div>
