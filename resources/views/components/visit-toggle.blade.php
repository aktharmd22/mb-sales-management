@props(['field', 'value' => false, 'label'])

<div class="flex items-center justify-between gap-4">
    <span class="text-sm font-medium text-ink-900">{{ $label }}</span>
    <div class="inline-flex rounded-xl bg-ink-50 p-1 shrink-0">
        <button type="button" wire:click="$set('{{ $field }}', true)"
                @class([
                    'px-4 py-1.5 rounded-lg text-sm font-semibold transition',
                    'bg-primary text-white shadow-sm' => $value,
                    'text-ink-700/60 hover:text-ink-900' => ! $value,
                ])>Yes</button>
        <button type="button" wire:click="$set('{{ $field }}', false)"
                @class([
                    'px-4 py-1.5 rounded-lg text-sm font-semibold transition',
                    'bg-ink-900 text-white shadow-sm' => ! $value,
                    'text-ink-700/60 hover:text-ink-900' => $value,
                ])>No</button>
    </div>
</div>
