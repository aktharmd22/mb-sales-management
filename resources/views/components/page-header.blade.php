@props(['title', 'subtitle' => null])

<div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-ink-900">{{ $title }}</h1>
        @if ($subtitle)
            <p class="mt-1 text-sm text-ink-700/70">{{ $subtitle }}</p>
        @endif
    </div>
    @if (isset($actions))
        <div class="flex items-center gap-2 flex-wrap">
            {{ $actions }}
        </div>
    @endif
</div>
