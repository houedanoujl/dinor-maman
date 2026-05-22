<div class="container mx-auto max-w-md px-4 py-12">
    <div class="mb-6 text-center">
        <p class="text-xs font-bold uppercase tracking-wider text-dinor-gold">Connexion</p>
        <h1 class="mt-2 font-display text-3xl font-bold text-dinor-dark md:text-4xl">Bon retour !</h1>
        <p class="mt-2 text-gray-600">Connectez-vous avec votre numéro de téléphone.</p>
    </div>

    <form wire:submit="submit" class="space-y-5 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <div>
            <label class="block text-sm font-medium">Téléphone</label>
            <div class="mt-1 flex rounded-2xl border border-gray-200 bg-white focus-within:border-dinor-red focus-within:ring-2 focus-within:ring-dinor-red/20">
                <span class="inline-flex select-none items-center rounded-l-2xl border-r border-gray-200 bg-gray-50 px-3 text-sm font-semibold text-gray-600">
                    +225
                </span>
                <input type="tel"
                       wire:model="phone"
                       class="w-full rounded-r-2xl border-0 bg-transparent px-3 py-2 focus:outline-none focus:ring-0"
                       autocomplete="tel-national"
                       placeholder="XX XX XX XX XX"
                       inputmode="numeric"
                       maxlength="14"
                       pattern="[0-9 ]*"
                       x-data
                       x-on:input="
                           let digits = $event.target.value.replace(/\D/g, '').slice(0, 10);
                           let formatted = digits.match(/.{1,2}/g)?.join(' ') ?? '';
                           $event.target.value = formatted;
                           $wire.set('phone', digits, false);
                       " />
            </div>
            @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium">Mot de passe (reçu par SMS)</label>
            <input type="password" wire:model="password" class="input-dinor mt-1" autocomplete="current-password" inputmode="numeric" maxlength="8" />
            @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            <p class="mt-1 text-xs text-gray-500">Code à 8 chiffres envoyé par SMS lors de votre inscription.</p>
        </div>

        <label class="flex items-center gap-2 text-sm text-gray-600">
            <input type="checkbox" wire:model="remember" class="h-4 w-4 rounded border-gray-300 text-dinor-red focus:ring-dinor-red" />
            Rester connecté sur cet appareil
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
        </div>
    </form>
</div>
