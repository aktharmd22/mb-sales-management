@props(['label', 'value', 'icon' => null, 'tone' => 'ink', 'sub' => null])

@php
    $toneClass = [
        'ink' => 'text-ink-900',
        'primary' => 'text-primary',
        'accent' => 'text-accent-600',
        'danger' => 'text-danger',
        'won' => 'text-stage-won',
    ][$tone] ?? 'text-ink-900';
    $iconBg = [
        'ink' => 'bg-ink-50 text-ink-700',
        'primary' => 'bg-primary/10 text-primary',
        'accent' => 'bg-accent/10 text-accent-600',
        'danger' => 'bg-danger/10 text-danger',
        'won' => 'bg-stage-won/10 text-stage-won',
    ][$tone] ?? 'bg-ink-50 text-ink-700';
@endphp

<div class="card p-4 sm:p-5">
    <div class="flex items-start justify-between gap-2">
        <p class="text-xs sm:text-sm text-ink-700/60">{{ $label }}</p>
        @if ($icon)
            <span class="grid place-items-center h-8 w-8 rounded-lg {{ $iconBg }} shrink-0">
                <x-icon :name="$icon" class="w-4 h-4" />
            </span>
        @endif
    </div>
    <p class="kpi-number text-2xl sm:text-3xl font-bold {{ $toneClass }} mt-2 leading-none">{{ $value }}</p>
    @if ($sub)
        <p class="text-xs text-ink-700/50 mt-1.5">{{ $sub }}</p>
    @endif
</div>
