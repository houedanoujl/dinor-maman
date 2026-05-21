<div class="container mx-auto max-w-xl px-4 py-12">

    {{-- Étape : vérification SMS (participant) --}}
    @if ($step === 'verify')
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
                <button type="button" wire:click="verifyCode" wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center rounded-full bg-dinor-red px-8 py-3 font-semibold text-white shadow-sm transition hover:bg-dinor-red/90 disabled:opacity-50">
                    <span wire:loading.remove wire:target="verifyCode">Valider le code</span>
                    <span wire:loading wire:target="verifyCode">Vérification…</span>
                </button>
                <button type="button" wire:click="resendCode" wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center rounded-full border border-gray-200 px-6 py-3 text-sm font-semibold text-gray-600 transition hover:border-dinor-red hover:text-dinor-red disabled:opacity-50">
                    <span wire:loading.remove wire:target="resendCode">Renvoyer le code</span>
                    <span wire:loading wire:target="resendCode">Envoi…</span>
                </button>
            </div>

            <p class="mt-4 text-xs text-gray-400">Code valable 10 minutes. Vérifiez aussi vos spams.</p>
        </div>

    {{-- Étape : confirmation participation --}}
    @elseif ($step === 'done')
        <div class="rounded-2xl border border-dinor-gold/30 bg-white p-8 text-center shadow-sm">
            <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-dinor-gold/10">
                <svg class="h-8 w-8 text-dinor-gold" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
            </div>
            <h2 class="font-display text-2xl font-bold text-dinor-dark">Participation envoyée</h2>
            <p class="mt-3 text-gray-600">
                Votre photo est en attente de validation par notre équipe.<br>
                Vous serez notifié(e) dès qu'elle sera approuvée.
            </p>
            <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-center">
                <a href="{{ route('contest.gallery') }}" class="inline-flex items-center justify-center rounded-full bg-dinor-red px-6 py-2.5 font-semibold text-white shadow-sm transition hover:bg-dinor-red/90">Voir la galerie</a>
            </div>
        </div>

    {{-- Étape : formulaire --}}
    @else
        <div class="mb-6 text-center">
            <p class="text-xs font-bold uppercase tracking-wider text-dinor-gold">Inscription</p>
            <h1 class="mt-2 font-display text-3xl font-bold text-dinor-dark md:text-4xl">
                @if ($role === 'participant') Participez au concours
                @else Créez votre compte
                @endif
            </h1>
            <p class="mt-2 text-gray-600">
                @if ($role === 'participant') Compte + photo en une étape.
                @else Pour voter ou participer.
                @endif
            </p>
        </div>

        @if ($role === 'participant' && $contestEnded)
            <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                Le concours est terminé. Les nouvelles participations sont clôturées.
            </div>
        @endif

        @if ($role === 'participant' && ! $uploadOpen && ! $contestEnded)
            <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                La phase d'upload est terminée. Vous pouvez encore créer un compte votant.
            </div>
        @endif

        <form wire:submit="submit" class="space-y-5 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">

            {{-- Switcher de rôle --}}
            <div>
                <label class="block text-sm font-semibold text-dinor-dark mb-3">Je m'inscris en tant que :</label>
                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="relative cursor-pointer">
                        <input type="radio" wire:model.live="role" value="voter" class="peer sr-only" {{ $isAuthed ? 'disabled' : '' }} />
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

            {{-- Téléphone (identifiant unique) --}}
            @unless ($isAuthed)
                <div>
                    <label class="block text-sm font-medium">Téléphone <span class="text-red-500">*</span></label>
                    <div class="mt-1 flex rounded-2xl border border-gray-200 bg-white focus-within:border-dinor-red focus-within:ring-2 focus-within:ring-dinor-red/20">
                        <span class="inline-flex select-none items-center rounded-l-2xl border-r border-gray-200 bg-gray-50 px-3 text-sm font-semibold text-gray-600">
                            +225
                        </span>
                        <input type="tel"
                               wire:model="phone"
                               class="w-full rounded-r-2xl border-0 bg-transparent px-3 py-2 focus:outline-none focus:ring-0"
                               placeholder="XX XX XX XX XX"
                               autocomplete="tel-national"
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
                    <p class="mt-1 text-xs text-gray-500">10 chiffres, format ivoirien. Votre numéro sert d'identifiant. Un mot de passe vous sera envoyé par SMS.</p>
                </div>

                <div class="rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">
                    <strong>⚠️ Important :</strong> Votre mot de passe sera envoyé <strong>une seule fois par SMS</strong>. Conservez-le précieusement.
                </div>
            @endunless

            {{-- Champs VOTANT --}}
            @if ($role === 'voter')
                <div>
                    <label class="block text-sm font-medium">Nom complet</label>
                    <input type="text" wire:model="name" class="input-dinor mt-1" autocomplete="name" />
                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            @endif

            {{-- Champs PARTICIPANT --}}
            @if ($role === 'participant')
                <div class="rounded-2xl border border-dinor-gold/30 bg-dinor-gold/5 p-4">
                    <p class="flex items-center gap-2 text-sm font-semibold text-dinor-dark">
                        <svg class="h-4 w-4 shrink-0 text-dinor-gold" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        Critères de sélection de la photo
                    </p>
                    <ul class="mt-3 space-y-1.5 text-sm text-gray-600">
                        <li class="flex items-start gap-2"><span class="mt-0.5 shrink-0 font-bold text-dinor-gold">-</span><span>Photo nette, bien éclairée</span></li>
                        <li class="flex items-start gap-2"><span class="mt-0.5 shrink-0 font-bold text-dinor-gold">-</span><span>Vous et votre maman clairement visibles</span></li>
                        <li class="flex items-start gap-2"><span class="mt-0.5 shrink-0 font-bold text-dinor-gold">-</span><span>Contexte de cuisine ou repas familial</span></li>
                        <li class="flex items-start gap-2"><span class="mt-0.5 shrink-0 font-bold text-dinor-gold">-</span><span>Contenu approprié</span></li>
                    </ul>
                </div>

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

                <div
                    x-data="{
                        commune: @entangle('commune'),
                        quartier: @entangle('quartier'),
                        map: {{ \Illuminate\Support\Js::from($quartiersMap) }},
                        get quartiers() { return this.map[this.commune] ?? []; },
                    }"
                    x-init="$watch('commune', () => { quartier = '' })"
                    class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium">Commune</label>
                        <select x-model="commune" class="input-dinor mt-1">
                            <option value="">— Sélectionnez —</option>
                            @foreach ($communes as $c)
                                <option value="{{ $c }}">{{ $c }}</option>
                            @endforeach
                        </select>
                        @error('commune') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Quartier</label>
                        <select x-model="quartier" class="input-dinor mt-1" :disabled="quartiers.length === 0">
                            <option value="" x-text="quartiers.length === 0 ? '— Choisir d\'abord une commune —' : '— Sélectionnez —'"></option>
                            <template x-for="q in quartiers" :key="q">
                                <option :value="q" x-text="q"></option>
                            </template>
                        </select>
                        @error('quartier') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div x-data="{ uploading: false, progress: 0 }"
                     x-on:livewire-upload-start="uploading = true"
                     x-on:livewire-upload-finish="uploading = false; progress = 0"
                     x-on:livewire-upload-cancel="uploading = false"
                     x-on:livewire-upload-error="uploading = false"
                     x-on:livewire-upload-progress="progress = $event.detail.progress">
                    <label class="block text-sm font-medium">Votre photo</label>

                    @if ($photo)
                        <div class="relative mt-2 overflow-hidden rounded-2xl border border-gray-200 bg-gray-50 shadow-sm">
                            <img src="{{ $photo->temporaryUrl() }}" alt="" class="w-full max-h-80 object-contain" />
                            <div class="absolute inset-x-0 bottom-0 flex items-center justify-between gap-3 bg-linear-to-t from-black/70 to-transparent px-4 py-3">
                                <span class="text-xs font-medium text-white/80">Photo sélectionnée</span>
                                <button type="button" wire:click="$set('photo', null)"
                                        class="inline-flex items-center gap-1.5 rounded-full bg-white/20 px-3 py-1.5 text-xs font-semibold text-white backdrop-blur transition hover:bg-red-500">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                    Changer
                                </button>
                            </div>
                        </div>
                    @else
                        <label for="photo-input"
                               class="mt-1 flex cursor-pointer flex-col items-center gap-3 rounded-2xl border-2 border-dashed border-gray-300 bg-gray-50 px-4 py-8 text-center transition hover:border-dinor-red hover:bg-dinor-red/5">
                            <svg class="h-10 w-10 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
                            <div>
                                <p class="text-sm font-semibold text-dinor-dark">Cliquez pour choisir une photo</p>
                                <p class="mt-1 text-xs text-gray-400">JPG ou PNG — max 4 Mo</p>
                            </div>
                            <input id="photo-input" type="file" wire:model="photo" accept="image/jpeg,image/png" class="sr-only" />
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

                <div
                    x-data="{
                        listening: false,
                        supported: ('webkitSpeechRecognition' in window) || ('SpeechRecognition' in window),
                        recognition: null,
                        toggle() {
                            if (!this.supported) { alert('Dictée vocale non supportée par ce navigateur. Utilisez Chrome ou Edge.'); return; }
                            if (this.listening) { this.recognition?.stop(); return; }
                            const Rec = window.SpeechRecognition || window.webkitSpeechRecognition;
                            this.recognition = new Rec();
                            this.recognition.lang = 'fr-FR';
                            this.recognition.continuous = true;
                            this.recognition.interimResults = false;
                            this.recognition.onstart = () => { this.listening = true; };
                            this.recognition.onend = () => { this.listening = false; };
                            this.recognition.onerror = () => { this.listening = false; };
                            this.recognition.onresult = (e) => {
                                let text = '';
                                for (let i = e.resultIndex; i < e.results.length; i++) {
                                    if (e.results[i].isFinal) text += e.results[i][0].transcript;
                                }
                                if (text.trim().length === 0) return;
                                const ta = $refs.anecdoteInput;
                                const merged = (ta.value ? ta.value.trim() + ' ' : '') + text.trim();
                                const trimmed = merged.slice(0, 500);
                                ta.value = trimmed;
                                $wire.set('anecdote', trimmed, false);
                            };
                            this.recognition.start();
                        }
                    }">
                    <div class="flex items-center justify-between">
                        <label class="block text-sm font-medium">
                            Anecdote ou message à votre maman <span class="text-gray-400">(facultatif)</span>
                        </label>
                        <button type="button" x-on:click="toggle()"
                                :class="listening ? 'bg-dinor-red text-white border-dinor-red animate-pulse' : 'border-gray-200 text-gray-600 hover:border-dinor-red hover:text-dinor-red'"
                                class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold transition"
                                x-show="supported"
                                title="Dicter le texte">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/>
                                <path d="M19 10v2a7 7 0 0 1-14 0v-2"/>
                                <line x1="12" y1="19" x2="12" y2="23"/>
                                <line x1="8" y1="23" x2="16" y2="23"/>
                            </svg>
                            <span x-text="listening ? 'Écoute…' : 'Dicter'"></span>
                        </button>
                    </div>
                    <textarea wire:model="anecdote" x-ref="anecdoteInput" maxlength="500" rows="4"
                              class="input-dinor mt-1 resize-none"
                              placeholder="Un souvenir, une pensée, un moment partagé…"></textarea>
                    <p class="mt-1 text-xs text-gray-400" x-show="!supported">Astuce : la dictée vocale est disponible sur Chrome et Edge.</p>
                    @error('anecdote') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            @endif

            {{-- Consentement --}}
            <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" wire:model="consent" class="mt-0.5 h-4 w-4 shrink-0 rounded border-gray-300 text-dinor-red focus:ring-dinor-red" />
                    <span class="text-sm text-gray-600">
                        J'accepte le
                        <a href="{{ route('reglement') }}" target="_blank" class="font-semibold text-dinor-red underline">règlement du concours</a>.
                    </span>
                </label>
                @error('consent') <p class="mt-2 text-sm text-red-600">Vous devez accepter les conditions.</p> @enderror
            </div>

            <button type="submit"
                    class="btn-dinor w-full py-3 text-base disabled:opacity-50"
                    wire:loading.attr="disabled"
                    @if($role === 'participant' && ($contestEnded || ! $uploadOpen)) disabled @endif>
                <span wire:loading.remove wire:target="submit">
                    @if ($role === 'participant') Envoyer ma participation
                    @else Créer mon compte
                    @endif
                </span>
                <span wire:loading wire:target="submit">Envoi…</span>
            </button>

            @unless ($isAuthed)
                <p class="text-center text-sm text-gray-500">
                    Déjà inscrit ?
                    <a href="{{ route('login') }}" class="font-semibold text-dinor-red hover:underline">Se connecter</a>
                </p>
            @endunless
        </form>
    @endif
</div>
