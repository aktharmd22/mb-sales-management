@php $user = auth()->user(); @endphp

<header class="sticky top-0 z-20 bg-canvas/80 backdrop-blur border-b border-ink-100">
    <div class="flex items-center gap-3 h-16 px-4 sm:px-6 lg:px-8">

        {{-- Mobile: hamburger + brand --}}
        <button @click="sidebarOpen = true" class="lg:hidden -ml-1 p-2 text-ink-700 hover:text-ink-900">
            <x-icon name="menu" class="w-6 h-6" />
        </button>
        <a href="{{ route('dashboard') }}" wire:navigate class="lg:hidden flex items-center">
            <x-logo class="h-7 max-w-[150px]" />
        </a>

        {{-- Global search (desktop) --}}
        <form method="GET" action="{{ route('clients.index') }}" class="hidden md:flex items-center flex-1 max-w-md">
            <label class="relative w-full">
                <span class="absolute inset-y-0 left-3 grid place-items-center text-ink-700/50">
                    <x-icon name="search" class="w-4 h-4" />
                </span>
                <input type="search" name="search" placeholder="Search businesses…"
                       class="input pl-9 py-2 bg-surface" />
            </label>
        </form>

        <div class="flex-1 md:hidden"></div>

        {{-- Actions --}}
        <div class="flex items-center gap-2">
            <a href="{{ route('followups.index') }}" wire:navigate
               class="relative grid place-items-center h-10 w-10 rounded-xl text-ink-700 hover:bg-ink-50 transition"
               title="Follow-ups">
                <x-icon name="bell" class="w-5 h-5" />
                @php
                    $due = \App\Models\FollowUp::forUser($user)->pending()->whereDate('due_date', '<=', today())->count();
                @endphp
                @if ($due > 0)
                    <span class="absolute -top-0.5 -right-0.5 grid place-items-center min-w-[18px] h-[18px] px-1 rounded-full bg-danger text-white text-[10px] font-bold">{{ $due }}</span>
                @endif
            </a>

            <a href="{{ route('visits.create') }}" wire:navigate class="btn-primary hidden sm:inline-flex">
                <x-icon name="plus" class="w-4 h-4" />
                Client Visit
            </a>
        </div>
    </div>
</header>
