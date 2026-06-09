@php
    $children = $children ?? [];
@endphp

@if (! empty($children))
    {{-- Expandable group (e.g. Portfolio submenu) --}}
    @php
        $groupActive = request()->routeIs($route);
        $curTab = request('tab', $children[0]['params']['tab'] ?? null);
    @endphp
    <div x-data="{ open: {{ $groupActive ? 'true' : 'false' }} }">
        <button type="button" @click="open = ! open"
                class="w-full group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition
                       {{ $groupActive ? 'text-white' : 'text-white/60 hover:bg-white/5 hover:text-white' }}">
            <x-icon :name="$icon" class="w-5 h-5 {{ $groupActive ? 'text-primary-300' : '' }}" />
            <span class="flex-1 text-left">{{ $label }}</span>
            <x-icon name="chevron-right" class="w-4 h-4 transition-transform" ::class="open && 'rotate-90'" />
        </button>
        <div x-show="open" x-collapse.duration.200ms x-cloak class="mt-1 ml-4 pl-3 border-l border-white/10 space-y-0.5">
            @foreach ($children as $child)
                @php $childActive = $groupActive && (string) $curTab === (string) ($child['params']['tab'] ?? ''); @endphp
                <a href="{{ route($child['route'], $child['params'] ?? []) }}" wire:navigate @click="sidebarOpen = false"
                   class="block rounded-lg px-3 py-2 text-sm transition {{ $childActive ? 'bg-white/10 text-white font-medium' : 'text-white/50 hover:text-white hover:bg-white/5' }}">
                    {{ $child['label'] }}
                </a>
            @endforeach
        </div>
    </div>
@else
    @php
        $active = request()->routeIs($route) || request()->routeIs($route . '.*');
        $base = \Illuminate\Support\Str::beforeLast($route, '.');
        if (! $active && $base && request()->routeIs($base . '.*')) {
            $active = true;
        }
    @endphp

    <a href="{{ route($route) }}" wire:navigate
       @click="sidebarOpen = false"
       class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition
              {{ $active ? 'bg-white/10 text-white' : 'text-white/60 hover:bg-white/5 hover:text-white' }}">
        <span class="relative">
            <x-icon :name="$icon" class="w-5 h-5 {{ $active ? 'text-primary-300' : '' }}" />
        </span>
        <span class="flex-1">{{ $label }}</span>
        @if (($badge ?? 0) > 0)
            <span class="badge bg-accent text-ink-900 px-2 py-0.5 text-[11px] font-bold">{{ $badge }}</span>
        @endif
    </a>
@endif
