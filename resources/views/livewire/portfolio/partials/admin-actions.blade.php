{{-- Admin controls for a portfolio item. Edit opens the Alpine modal prefilled. --}}
<div class="flex items-center gap-1 shrink-0" wire:key="actions-{{ $item->id }}">
    <button type="button" @click="edit({{ \Illuminate\Support\Js::from($item->only(['id','type','title','description','url','credentials'])) }})"
            class="grid place-items-center h-8 w-8 rounded-lg text-ink-700/60 hover:bg-ink-50 hover:text-ink-900" title="Edit">
        <x-icon name="edit" class="w-4 h-4" />
    </button>
    <button type="button" wire:click="toggleActive({{ $item->id }})"
            class="grid place-items-center h-8 w-8 rounded-lg text-ink-700/60 hover:bg-ink-50" title="{{ $item->is_active ? 'Hide from team' : 'Show to team' }}">
        <x-icon name="{{ $item->is_active ? 'check-circle' : 'info' }}" class="w-4 h-4" />
    </button>
    <button type="button" wire:click="delete({{ $item->id }})" wire:confirm="Remove this item for everyone?"
            class="grid place-items-center h-8 w-8 rounded-lg text-ink-700/60 hover:bg-danger/10 hover:text-danger" title="Delete">
        <x-icon name="trash" class="w-4 h-4" />
    </button>
</div>
