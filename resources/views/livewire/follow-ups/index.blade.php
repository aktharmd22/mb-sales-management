<div>
    <x-page-header title="Follow-ups" subtitle="Stay on top of what's due — nothing goes cold.">
        <x-slot:actions>
            @if ($isAdmin)
                <select wire:model.live="assigned" class="input w-44">
                    <option value="">Whole team</option>
                    @foreach ($salespeople as $sp)
                        <option value="{{ $sp->id }}">{{ $sp->name }}</option>
                    @endforeach
                </select>
            @endif
            <button wire:click="startAdd" class="btn-primary"><x-icon name="plus" class="w-4 h-4" /> Add follow-up</button>
        </x-slot:actions>
    </x-page-header>

    @if ($isEmpty)
        <x-empty-state icon="check-circle" title="No follow-ups due. Nice and clear."
            message="Schedule a follow-up when you log a visit, or add one here." />
    @else
        <div class="space-y-6">
            {{-- Overdue --}}
            @if ($overdue->isNotEmpty())
                <section>
                    <h2 class="flex items-center gap-2 text-sm font-bold uppercase tracking-wide text-danger mb-2">
                        Overdue <span class="badge bg-danger text-white">{{ $overdue->count() }}</span>
                    </h2>
                    <div class="space-y-2">
                        @foreach ($overdue as $fu)
                            @include('livewire.follow-ups.row', ['fu' => $fu, 'tone' => 'overdue'])
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- Due today --}}
            @if ($dueToday->isNotEmpty())
                <section>
                    <h2 class="flex items-center gap-2 text-sm font-bold uppercase tracking-wide text-accent-700 mb-2">
                        Due today <span class="badge bg-accent text-ink-900">{{ $dueToday->count() }}</span>
                    </h2>
                    <div class="space-y-2">
                        @foreach ($dueToday as $fu)
                            @include('livewire.follow-ups.row', ['fu' => $fu, 'tone' => 'today'])
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- Upcoming --}}
            @if ($upcoming->isNotEmpty())
                <section>
                    <h2 class="flex items-center gap-2 text-sm font-bold uppercase tracking-wide text-ink-700/60 mb-2">
                        Upcoming <span class="badge bg-ink-100 text-ink-700">{{ $upcoming->count() }}</span>
                    </h2>
                    <div class="space-y-2">
                        @foreach ($upcoming as $fu)
                            @include('livewire.follow-ups.row', ['fu' => $fu, 'tone' => 'upcoming'])
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    @endif

    {{-- Done toggle --}}
    <div class="mt-6">
        <button wire:click="$toggle('showDone')" class="text-sm font-medium text-ink-700/60 hover:text-ink-900">
            {{ $showDone ? 'Hide' : 'Show' }} completed
        </button>
        @if ($showDone)
            <div class="mt-3 space-y-2">
                @forelse ($done as $fu)
                    @include('livewire.follow-ups.row', ['fu' => $fu, 'tone' => 'done'])
                @empty
                    <p class="text-sm text-ink-700/50">No completed follow-ups yet.</p>
                @endforelse
            </div>
        @endif
    </div>

    {{-- ============ Add modal ============ --}}
    <x-modal-panel show="adding" maxWidth="max-w-md">
        <form wire:submit="saveFollowUp" class="p-6">
            <h2 class="text-lg font-bold text-ink-900">Schedule a follow-up</h2>

            <div class="mt-4 space-y-4">
                <div x-data="{ open: false }">
                    <label class="label">Business</label>
                    <input type="text" wire:model.live.debounce.250ms="clientSearch" @focus="open = true" @click="open = true"
                           class="input" placeholder="Search a business…" autocomplete="off" />
                    @error('clientId') <p class="mt-1 text-xs text-danger">Pick a business.</p> @enderror
                    <div x-show="open" @click.outside="open = false" class="mt-1 rounded-xl border border-ink-100 divide-y divide-ink-100 overflow-hidden max-h-52 overflow-y-auto">
                        @forelse ($this->clientOptions as $opt)
                            <button type="button" wire:click="pickClient({{ $opt->id }})" @click="open = false"
                                    class="w-full text-left px-3 py-2 text-sm hover:bg-canvas {{ $clientId === $opt->id ? 'bg-primary/5 text-primary font-medium' : 'text-ink-900' }}">
                                {{ $opt->business_name }}
                            </button>
                        @empty
                            <p class="px-3 py-2 text-sm text-ink-700/50">No businesses found.</p>
                        @endforelse
                    </div>
                </div>

                <div>
                    <label class="label">Due date</label>
                    <input type="date" wire:model="dueDate" min="{{ today()->toDateString() }}" class="input tnum" />
                    @error('dueDate') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label">Note</label>
                    <input type="text" wire:model="note" class="input" placeholder="e.g. Send the proposal" />
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <button type="button" wire:click="$set('adding', false)" class="btn-ghost">Cancel</button>
                <button type="submit" class="btn-primary">Schedule</button>
            </div>
        </form>
    </x-modal-panel>
</div>
