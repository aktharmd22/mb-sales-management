@php
    $active = request()->routeIs($route) || request()->routeIs(\Illuminate\Support\Str::beforeLast($route, '.') . '.*');
@endphp

<a href="{{ route($route) }}" wire:navigate
   class="relative flex flex-col items-center justify-center gap-0.5 text-[11px] font-medium transition
          {{ $active ? 'text-primary' : 'text-ink-700/60' }}">
    <span class="relative">
        <x-icon :name="$icon" class="w-6 h-6" />
        @if (($badge ?? 0) > 0)
            <span class="absolute -top-1 -right-2 grid place-items-center min-w-[16px] h-[16px] px-1 rounded-full bg-danger text-white text-[9px] font-bold">{{ $badge }}</span>
        @endif
    </span>
    {{ $label }}
</a>
