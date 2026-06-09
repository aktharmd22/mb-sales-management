@php $user = auth()->user(); @endphp

<div class="border-t border-white/5 p-3">
    <div x-data="{ open: false }" class="relative">
        <button @click="open = !open" @click.outside="open = false"
                class="w-full flex items-center gap-3 rounded-xl px-2 py-2 text-left hover:bg-white/5 transition">
            <span class="grid place-items-center h-9 w-9 rounded-full bg-primary/20 text-primary-200 font-semibold text-sm shrink-0">
                {{ \Illuminate\Support\Str::of($user->name)->explode(' ')->map(fn($p) => mb_substr($p, 0, 1))->take(2)->implode('') }}
            </span>
            <span class="flex-1 min-w-0">
                <span class="block text-sm font-medium text-white truncate">{{ $user->name }}</span>
                <span class="block text-[11px] text-white/40 capitalize">{{ $user->role }}</span>
            </span>
            <x-icon name="cog" class="w-4 h-4 text-white/40" />
        </button>

        <div x-show="open" x-cloak x-transition
             class="absolute bottom-full left-0 right-0 mb-2 rounded-xl bg-ink-800 border border-white/10 shadow-pop overflow-hidden">
            <a href="{{ route('profile') }}" wire:navigate
               class="flex items-center gap-2 px-4 py-2.5 text-sm text-white/70 hover:bg-white/5 hover:text-white">
                <x-icon name="user" class="w-4 h-4" /> My profile
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-white/70 hover:bg-white/5 hover:text-white">
                    <x-icon name="logout" class="w-4 h-4" /> Sign out
                </button>
            </form>
        </div>
    </div>
</div>
