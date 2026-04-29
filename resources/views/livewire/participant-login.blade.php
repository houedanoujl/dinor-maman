<div>
<section class="container mx-auto max-w-md px-4 py-12">
    <div class="rounded-3xl border border-gray-100 bg-white p-8 shadow-sm">
        <div class="mb-6 text-center">
            <p class="text-xs font-bold uppercase tracking-wider text-dinor-gold">Connexion</p>
            <h1 class="mt-2 font-display text-3xl font-bold text-dinor-dark">Espace participant</h1>
            <p class="mt-2 text-sm text-gray-600">
                Saisissez votre numéro de téléphone — nous vous enverrons votre lien d'accès personnel.
            </p>
        </div>

        @if($sent)
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 text-center">
                <svg class="mx-auto mb-3 h-10 w-10 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                <p class="text-sm font-semibold text-emerald-800">Si ce numéro correspond à une participation,<br />un lien de connexion vient d'être envoyé par SMS{{ $maskedPhone ? ' au '.$maskedPhone : '' }}.</p>
                <p class="mt-3 text-xs text-emerald-700">Pensez à vérifier vos messages dans les minutes qui viennent.</p>
                <button type="button" wire:click="$set('sent', false)" class="mt-4 text-sm font-semibold text-dinor-red hover:underline">
                    Renvoyer pour un autre numéro
                </button>
            </div>
        @else
            <form wire:submit.prevent="submit" class="space-y-4">
                <div>
                    <label for="phone" class="mb-1 block text-sm font-semibold text-dinor-dark">Numéro de téléphone</label>
                    <input id="phone" type="tel" wire:model="phone" autocomplete="tel"
                           class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm focus:border-dinor-red focus:outline-none focus:ring-2 focus:ring-dinor-red/20"
                           placeholder="+225 07 00 00 00 00" />
                    @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <button type="submit" wire:loading.attr="disabled"
                        class="btn-dinor w-full justify-center py-3 text-base disabled:opacity-50">
                    <span wire:loading.remove>Recevoir mon lien</span>
                    <span wire:loading>Envoi en cours…</span>
                </button>
            </form>

            <div class="mt-6 border-t border-gray-100 pt-5 text-center text-sm text-gray-600">
                Pas encore inscrit ?
                @if($uploadPhase)
                    <a href="{{ route('contest.form') }}" class="font-semibold text-dinor-red hover:underline">Participer au concours</a>
                @else
                    <span class="text-gray-400">Les inscriptions sont closes.</span>
                @endif
            </div>
        @endif
    </div>
</section>
</div>
