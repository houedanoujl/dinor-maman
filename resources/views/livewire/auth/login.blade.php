<div class="container mx-auto max-w-md px-4 py-12">
    <div class="mb-6 text-center">
        <p class="text-xs font-bold uppercase tracking-wider text-dinor-gold">Connexion</p>
        <h1 class="mt-2 font-display text-3xl font-bold text-dinor-dark md:text-4xl">Bon retour !</h1>
        <p class="mt-2 text-gray-600">Connectez-vous pour voter ou participer.</p>
    </div>

    <form wire:submit="submit" class="space-y-5 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <div>
            <label class="block text-sm font-medium">Email</label>
            <input type="email" wire:model="email" class="input-dinor mt-1" autocomplete="email" />
            @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium">Mot de passe</label>
            <input type="password" wire:model="password" class="input-dinor mt-1" autocomplete="current-password" />
            @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <label class="flex items-center gap-2 text-sm text-gray-600">
            <input type="checkbox" wire:model="remember" class="h-4 w-4 rounded border-gray-300 text-dinor-red focus:ring-dinor-red" />
            Se souvenir de moi
        </label>

        <button type="submit" class="btn-dinor w-full py-3 text-base disabled:opacity-50" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="submit">Se connecter</span>
            <span wire:loading wire:target="submit">Connexion…</span>
        </button>

        <div class="flex flex-col gap-2 text-center text-sm text-gray-500">
            <span>
                Pas encore inscrit ?
                <a href="{{ route('register') }}" class="font-semibold text-dinor-red hover:underline">Créer un compte</a>
            </span>
            <a href="{{ route('participant.login') }}" class="text-xs text-gray-400 hover:text-dinor-red">
                J'ai participé sans compte (connexion par SMS)
            </a>
        </div>
    </form>
</div>
