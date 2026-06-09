@props(['periods', 'current', 'field' => 'period'])

<div class="inline-flex rounded-xl bg-ink-50 p-1 text-sm">
    @foreach ($periods as $key => $label)
        <button type="button" wire:click="$set('{{ $field }}', '{{ $key }}')"
                @class([
                    'px-3 sm:px-4 py-1.5 rounded-lg font-semibold transition',
                    'bg-surface text-ink-900 shadow-sm' => $current === $key,
                    'text-ink-700/60 hover:text-ink-900' => $current !== $key,
                ])>
            {{ $label }}
        </button>
    @endforeach
</div>
