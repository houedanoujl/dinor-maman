@php
    use Illuminate\Support\Facades\Auth;
    use App\Models\Participant;

    $authUser = Auth::user();
    $token = session('participant_token');
    $currentParticipant = null;
    if ($token) {
        $currentParticipant = Participant::findByDashboardToken($token);
        if ($currentParticipant) {
            $currentParticipant->plainDashboardToken = $token;
        }
        if (! $currentParticipant) {
            session()->forget('participant_token');
        }
    }
    // Si user auth a un participant lié, l'utiliser en priorité
    if ($authUser && ! $currentParticipant) {
        $currentParticipant = Participant::where('user_id', $authUser->id)->first();
    }

    $displayName = $authUser?->name ?? $currentParticipant?->first_name ?? null;
    $isLogged = $authUser || $currentParticipant;
@endphp

@if($isLogged)
    <div x-data="{ open: false, confirmLogout: false }" class="relative">
        <button type="button"
                x-on:click="open = !open"
                x-on:click.outside="open = false"
                class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white px-3 py-1.5 text-sm font-semibold text-dinor-dark shadow-sm transition hover:border-dinor-red hover:text-dinor-red">
            @php($avatar = $currentParticipant?->getFirstMediaUrl('photo', 'thumb'))
            @if($avatar)
                <img src="{{ $avatar }}" alt="" class="h-7 w-7 rounded-full object-cover" />
            @else
                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-dinor-red/10 text-xs font-bold text-dinor-red">
                    {{ strtoupper(substr($displayName ?? '?', 0, 1)) }}
                </span>
            @endif
            <span class="hidden sm:inline">{{ $displayName }}</span>
            <svg class="h-4 w-4 text-gray-400" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <div x-show="open"
             x-transition.opacity
             x-cloak
             class="absolute right-0 mt-2 w-60 origin-top-right rounded-xl border border-gray-100 bg-white py-2 shadow-xl ring-1 ring-black/5">
            <div class="border-b border-gray-100 px-4 py-2">
                <p class="text-xs uppercase tracking-wide text-gray-400">Connecté</p>
                <p class="truncate text-sm font-semibold text-dinor-dark">{{ $authUser?->name ?? $currentParticipant?->full_name }}</p>
                @if($authUser)
                    <p class="truncate text-xs text-gray-500">{{ $authUser->email }}</p>
                    @if($authUser->isAdmin())
                        <span class="mt-1 inline-flex items-center gap-1 rounded-full bg-purple-50 px-2 py-0.5 text-[10px] font-semibold text-purple-700">Admin</span>
                    @elseif($authUser->isParticipant())
                        <span class="mt-1 inline-flex items-center gap-1 rounded-full bg-dinor-gold/10 px-2 py-0.5 text-[10px] font-semibold text-dinor-gold">Participant</span>
                    @else
                        <span class="mt-1 inline-flex items-center gap-1 rounded-full bg-dinor-red/10 px-2 py-0.5 text-[10px] font-semibold text-dinor-red">Votant</span>
                    @endif
                @endif
            </div>

            @if($authUser?->plain_password)
                <div class="border-b border-gray-100 px-4 py-2" x-data="{ show: false, copied: false }">
                    <p class="text-xs uppercase tracking-wide text-gray-400">Mot de passe</p>
                    <div class="mt-1 flex items-center gap-2">
                        <code class="flex-1 select-all rounded-md bg-gray-50 px-2 py-1 text-sm font-mono text-dinor-dark"
                              x-text="show ? @js($authUser->plain_password) : '••••••••'"></code>
                        <button type="button"
                                x-on:click="show = !show"
                                class="rounded-md p-1 text-gray-500 transition hover:bg-gray-100 hover:text-dinor-red"
                                :title="show ? 'Masquer' : 'Afficher'">
                            <svg x-show="!show" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg x-show="show" x-cloak class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.477 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                        </button>
                        <button type="button"
                                x-on:click="navigator.clipboard?.writeText(@js($authUser->plain_password)); copied = true; setTimeout(() => copied = false, 1500)"
                                class="rounded-md p-1 text-gray-500 transition hover:bg-gray-100 hover:text-dinor-red"
                                :title="copied ? 'Copié !' : 'Copier'">
                            <svg x-show="!copied" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            <svg x-show="copied" x-cloak class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </button>
                    </div>
                    <p class="mt-1 text-[10px] text-gray-400">Conservez-le pour vous reconnecter.</p>
                </div>
            @endif

            @if($authUser || $currentParticipant)
                <a href="{{ route('account') }}"
                   class="flex items-center gap-2 px-4 py-2 text-sm text-dinor-dark transition hover:bg-dinor-cream hover:text-dinor-red">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Mon espace
                </a>
            @endif

            @if($currentParticipant)
                @if($currentParticipant->status === Participant::STATUS_APPROVED)
                    <a href="{{ route('participant.show', $currentParticipant) }}"
                       class="flex items-center gap-2 px-4 py-2 text-sm text-dinor-dark transition hover:bg-dinor-cream hover:text-dinor-red">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        Ma page publique
                    </a>
                @endif
            @endif

            @if($authUser && $authUser->isVoter() && ! $currentParticipant)
                <a href="{{ route('register', ['role' => 'participant']) }}"
                   class="flex items-center gap-2 px-4 py-2 text-sm font-semibold text-dinor-gold transition hover:bg-dinor-cream">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Devenir participant
                </a>
            @endif

            @if($authUser?->isAdmin())
                <a href="/admin" class="flex items-center gap-2 px-4 py-2 text-sm text-dinor-dark transition hover:bg-dinor-cream hover:text-dinor-red">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    Administration
                </a>
            @endif

            <div class="border-t border-gray-100 mt-1 pt-1">
                <button type="button"
                        x-on:click="open = false; confirmLogout = true"
                        class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-red-600 transition hover:bg-red-50">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Se déconnecter
                </button>
            </div>
        </div>

        {{-- Warning modal --}}
        <div x-show="confirmLogout"
             x-cloak
             x-transition.opacity
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
             x-on:keydown.escape.window="confirmLogout = false">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl"
                 x-on:click.outside="confirmLogout = false">
                <div class="mb-4 flex items-start gap-3">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-amber-100">
                        <svg class="h-6 w-6 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-dinor-dark">Confirmer la déconnexion</h3>
                        <p class="mt-1 text-sm text-gray-600">
                            Vous allez être déconnecté. Pour vous reconnecter, vous aurez besoin de votre <strong>numéro de téléphone</strong> et du <strong>mot de passe reçu par SMS</strong> lors de votre inscription.
                        </p>
                        <p class="mt-2 text-sm font-semibold text-red-700">
                            ⚠️ Le mot de passe SMS n'est envoyé qu'une seule fois. Assurez-vous de le conserver avant de vous déconnecter.
                        </p>
                    </div>
                </div>
                <div class="mt-5 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <button type="button"
                            x-on:click="confirmLogout = false"
                            class="rounded-full border border-gray-200 px-5 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        Rester connecté
                    </button>
                    <form method="POST" action="{{ $authUser ? route('logout') : route('participant.logout') }}">
                        @csrf
                        <button type="submit"
                                class="w-full rounded-full bg-red-600 px-5 py-2 text-sm font-semibold text-white hover:bg-red-700 sm:w-auto">
                            Confirmer la déconnexion
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif
