<div class="container mx-auto max-w-xl px-4 py-12">
    @if ($step === 'verify')
        {{-- Écran vérification SMS --}}
        <div class="rounded-2xl border border-dinor-gold/30 bg-white p-8 text-center shadow-sm">
            <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-dinor-red/10">
                <svg class="h-8 w-8 text-dinor-red" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13.6 19.79 19.79 0 0 1 1.62 5a2 2 0 0 1 1.99-2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 10.91a16 16 0 0 0 6 6l.95-.95a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 21.73 18z"/>
                </svg>
            </div>
            <h2 class="font-display text-2xl font-bold text-dinor-dark">Vérifiez votre numéro</h2>
            <p class="mt-3 text-gray-600">
                Un code à 6 chiffres a été envoyé par SMS.<br>
                Saisissez-le ci-dessous pour valider votre participation.
            </p>

            @if ($sms_error)
                <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ $sms_error }}
                </div>
            @endif

            <div class="mt-6">
                <input type="text"
                       wire:model="sms_code_input"
                       wire:keydown.enter="verifyCode"
                       maxlength="6"
                       inputmode="numeric"
                       autocomplete="one-time-code"
                       placeholder="000000"
                       class="mx-auto block w-40 rounded-2xl border border-gray-200 bg-gray-50 px-4 py-4 text-center text-3xl font-bold tracking-[0.5em] text-dinor-dark focus:border-dinor-red focus:outline-none focus:ring-2 focus:ring-dinor-red/20" />
            </div>

            <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:justify-center">
                <button type="button"
                        wire:click="verifyCode"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center rounded-full bg-dinor-red px-8 py-3 font-semibold text-white shadow-sm transition hover:bg-dinor-red/90 disabled:opacity-50">
                    <span wire:loading.remove wire:target="verifyCode">Valider le code</span>
                    <span wire:loading wire:target="verifyCode">Vérification…</span>
                </button>
                <button type="button"
                        wire:click="resendCode"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center rounded-full border border-gray-200 px-6 py-3 text-sm font-semibold text-gray-600 transition hover:border-dinor-red hover:text-dinor-red disabled:opacity-50">
                    <span wire:loading.remove wire:target="resendCode">Renvoyer le code</span>
                    <span wire:loading wire:target="resendCode">Envoi…</span>
                </button>
            </div>

            <p class="mt-4 text-xs text-gray-400">Code valable 10 minutes. Vérifiez aussi vos spams.</p>
        </div>
    @elseif ($submitted)
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

                @if ($photo)
                    {{-- Preview avec overlay supprimer --}}
                    <div class="relative mt-2 overflow-hidden rounded-2xl border border-gray-200 bg-gray-50 shadow-sm">
                        <img src="{{ $photo->temporaryUrl() }}"
                             alt="Aperçu de votre photo"
                             class="w-full max-h-80 object-contain" />
                        <div class="absolute inset-x-0 bottom-0 flex items-center justify-between gap-3 bg-linear-to-t from-black/70 to-transparent px-4 py-3">
                            <span class="text-xs font-medium text-white/80">Photo sélectionnée</span>
                            <button type="button"
                                    wire:click="$set('photo', null)"
                                    class="inline-flex items-center gap-1.5 rounded-full bg-white/20 px-3 py-1.5 text-xs font-semibold text-white backdrop-blur transition hover:bg-red-500">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Changer
                            </button>
                        </div>
                    </div>
                @else
                    <label for="photo-input"
                           class="mt-1 flex cursor-pointer flex-col items-center gap-3 rounded-2xl border-2 border-dashed border-gray-300 bg-gray-50 px-4 py-8 text-center transition hover:border-dinor-red hover:bg-dinor-red/5">
                        <svg class="h-10 w-10 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                        </svg>
                        <div>
                            <p class="text-sm font-semibold text-dinor-dark">Cliquez pour choisir une photo</p>
                            <p class="mt-1 text-xs text-gray-400">JPG, PNG ou WebP — max 10 Mo</p>
                        </div>
                        <input id="photo-input" type="file" wire:model="photo"
                               accept="image/jpeg,image/png,image/webp"
                               class="sr-only" />
                    </label>
                @endif

                <div x-show="uploading" class="mt-2 h-2 rounded bg-gray-200">
                    <div class="h-2 rounded bg-dinor-red transition-all" :style="`width:${progress}%`"></div>
                </div>
                <div x-show="uploading" class="mt-1 text-center text-xs text-gray-400">
                    Envoi en cours… <span x-text="progress + '%'"></span>
                </div>
                @error('photo') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div x-data="{
                anecdoteLen: {{ strlen($anecdote ?? '') }},
                listening: false,
                supported: ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window),
                recognition: null,
                startDictation() {
                    const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
                    this.recognition = new SR();
                    this.recognition.lang = 'fr-FR';
                    this.recognition.continuous = false;
                    this.recognition.interimResults = false;
                    this.recognition.onstart = () => { this.listening = true; };
                    this.recognition.onend = () => { this.listening = false; };
                    this.recognition.onerror = () => { this.listening = false; };
                    this.recognition.onresult = (e) => {
                        const textarea = this.$refs.anecdoteField;
                        const transcript = e.results[0][0].transcript;
                        const cur = textarea.value;
                        textarea.value = cur ? cur + ' ' + transcript : transcript;
                        textarea.dispatchEvent(new Event('input'));
                        this.anecdoteLen = textarea.value.length;
                        textarea.dispatchEvent(new Event('change'));
                        @this.set('anecdote', textarea.value);
                    };
                    this.recognition.start();
                },
                stopDictation() {
                    if (this.recognition) this.recognition.stop();
                }
            }">
                <div class="flex items-center justify-between">
                    <label class="block text-sm font-medium">
                        Racontez l'anecdote derrière cette photo ou laissez un message à votre maman
                        <span class="text-gray-400">(facultatif)</span>
                    </label>
                    <button x-show="supported" type="button"
                            x-on:click="listening ? stopDictation() : startDictation()"
                            :class="listening
                                ? 'border-dinor-red bg-dinor-red/10 text-dinor-red'
                                : 'border-gray-200 bg-white text-gray-500 hover:border-dinor-red hover:text-dinor-red'"
                            class="ml-2 inline-flex shrink-0 items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-semibold transition">
                        <svg x-show="!listening" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/>
                            <path d="M19 10v2a7 7 0 0 1-14 0v-2"/>
                            <line x1="12" y1="19" x2="12" y2="23"/>
                            <line x1="8" y1="23" x2="16" y2="23"/>
                        </svg>
                        <span x-show="!listening" class="flex h-2 w-2 rounded-full bg-gray-300"></span>
                        <span x-show="listening" class="flex h-2 w-2 animate-pulse rounded-full bg-dinor-red"></span>
                        <span x-text="listening ? 'En écoute…' : 'Dicter'"></span>
                    </button>
                </div>
                <textarea wire:model="anecdote" maxlength="500" rows="4"
                          x-ref="anecdoteField"
                          x-on:input="anecdoteLen = $el.value.length"
                          class="input-dinor mt-1 resize-none"
                          placeholder="Un souvenir, une pensée, un moment partagé…"></textarea>
                <p class="mt-1 text-right text-xs text-gray-400"><span x-text="anecdoteLen"></span>/500</p>
                @error('anecdote') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
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
