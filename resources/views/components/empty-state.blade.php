@props(['icon' => 'sparkles', 'title', 'message' => null])

<div class="card flex flex-col items-center text-center px-6 py-14">
    <span class="grid place-items-center h-14 w-14 rounded-2xl bg-primary/10 text-primary mb-4">
        <x-icon :name="$icon" class="w-7 h-7" />
    </span>
    <h3 class="text-lg font-semibold text-ink-900">{{ $title }}</h3>
    @if ($message)
        <p class="mt-1 text-sm text-ink-700/70 max-w-sm">{{ $message }}</p>
    @endif
    @if (isset($action))
        <div class="mt-5">{{ $action }}</div>
    @endif
</div>
