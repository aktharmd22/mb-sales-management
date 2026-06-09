@php $isDone = $fu->status === 'done'; @endphp
<div @class([
        'card p-3.5 flex items-center gap-3',
        'border-l-4 border-danger' => $tone === 'overdue',
        'border-l-4 border-accent' => $tone === 'today',
        'opacity-60' => $isDone,
     ])>
    <button wire:click="{{ $isDone ? 'reopen' : 'complete' }}({{ $fu->id }})"
            class="grid place-items-center h-6 w-6 rounded-lg border-2 shrink-0 transition
                   {{ $isDone ? 'bg-primary border-primary text-white' : 'border-ink-200 hover:border-primary' }}"
            title="{{ $isDone ? 'Reopen' : 'Mark done' }}">
        @if ($isDone) <x-icon name="check" class="w-4 h-4" /> @endif
    </button>

    <div class="min-w-0 flex-1">
        <div class="flex items-center gap-2">
            <a href="{{ route('clients.show', $fu->client) }}" wire:navigate class="font-semibold text-ink-900 hover:text-primary truncate">
                {{ $fu->client->business_name }}
            </a>
            <x-stage-badge :stage="$fu->client->pipeline_stage" class="hidden sm:inline-flex" />
        </div>
        <p class="text-sm text-ink-700/70 truncate {{ $isDone ? 'line-through' : '' }}">{{ $fu->note }}</p>
    </div>

    <div class="text-right shrink-0">
        <p class="text-sm font-medium tnum {{ $tone === 'overdue' ? 'text-danger' : 'text-ink-700' }}">{{ $fu->due_date->format('d M') }}</p>
        @if ($isAdmin ?? false)
            <p class="text-[11px] text-ink-700/50 truncate max-w-[100px]">{{ $fu->salesperson->name }}</p>
        @endif
    </div>
</div>
