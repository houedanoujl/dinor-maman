@php
    $token = session('participant_token');
    $currentParticipant = null;
    if ($token) {
        $currentParticipant = \App\Models\Participant::where('dashboard_token', $token)->first();
        if (! $currentParticipant) {
            session()->forget('participant_token');
        }
    }
@endphp

@if($currentParticipant)
    <div x-data="{ open: false }" class="relative">
        <button type="button"
                x-on:click="open = !open"
                x-on:click.outside="open = false"
                class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white px-3 py-1.5 text-sm font-semibold text-dinor-dark shadow-sm transition hover:border-dinor-red hover:text-dinor-red">
            @php($avatar = $currentParticipant->getFirstMediaUrl('photo', 'thumb'))
            @if($avatar)
                <img src="{{ $avatar }}" alt="" class="h-7 w-7 rounded-full object-cover" />
            @else
                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-dinor-red/10 text-xs font-bold text-dinor-red">
                    {{ strtoupper(substr($currentParticipant->first_name, 0, 1)) }}
                </span>
            @endif
            <span class="hidden sm:inline">{{ $currentParticipant->first_name }}</span>
            <svg class="h-4 w-4 text-gray-400" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <div x-show="open"
             x-transition.opacity
             x-cloak
             class="absolute right-0 mt-2 w-60 origin-top-right rounded-xl border border-gray-100 bg-white py-2 shadow-xl ring-1 ring-black/5">
            <div class="border-b border-gray-100 px-4 py-2">
                <p class="text-xs uppercase tracking-wide text-gray-400">Connecté en tant que</p>
                <p class="truncate text-sm font-semibold text-dinor-dark">{{ $currentParticipant->full_name }}</p>
                @if($currentParticipant->status === \App\Models\Participant::STATUS_APPROVED)
                    <span class="mt-1 inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">Approuvé</span>
                @elseif($currentParticipant->status === \App\Models\Participant::STATUS_PENDING)
                    <span class="mt-1 inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-semibold text-amber-700">En attente</span>
                @else
                    <span class="mt-1 inline-flex items-center gap-1 rounded-full bg-red-50 px-2 py-0.5 text-[10px] font-semibold text-red-700">Refusé</span>
                @endif
            </div>

            <a href="{{ $currentParticipant->dashboard_url }}"
               class="flex items-center gap-2 px-4 py-2 text-sm text-dinor-dark transition hover:bg-dinor-cream hover:text-dinor-red">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Mon espace
            </a>

            @if($currentParticipant->status === \App\Models\Participant::STATUS_APPROVED)
                <a href="{{ route('participant.show', $currentParticipant) }}"
                   class="flex items-center gap-2 px-4 py-2 text-sm text-dinor-dark transition hover:bg-dinor-cream hover:text-dinor-red">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    Ma page publique
                </a>
            @endif

            <form method="POST" action="{{ route('participant.logout') }}" class="border-t border-gray-100 mt-1 pt-1">
                @csrf
                <button type="submit"
                        class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-red-600 transition hover:bg-red-50">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Se déconnecter
                </button>
            </form>
        </div>
    </div>
@endif
