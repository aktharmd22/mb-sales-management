<div>
    <x-page-header title="Clients" subtitle="Every business you're working — one lasting record each.">
        <x-slot:actions>
            @if (auth()->user()->isAdmin())
                <a href="{{ route('clients.import') }}" wire:navigate class="btn-ghost">
                    <x-icon name="upload" class="w-4 h-4" /> Import
                </a>
            @endif
            <button wire:click="export" class="btn-ghost" wire:loading.attr="disabled" wire:target="export">
                <x-icon name="download" class="w-4 h-4" /> Export
            </button>
            <button wire:click="newClient" class="btn-primary">
                <x-icon name="plus" class="w-4 h-4" /> Add business
            </button>
        </x-slot:actions>
    </x-page-header>

    {{-- Filter bar --}}
    <div class="card p-3 sm:p-4 mb-5">
        <div class="flex flex-col lg:flex-row lg:items-center gap-3">
            <label class="relative flex-1">
                <span class="absolute inset-y-0 left-3 grid place-items-center text-ink-700/40"><x-icon name="search" class="w-4 h-4" /></span>
                <input type="search" wire:model.live.debounce.300ms="search" class="input pl-9" placeholder="Search business, contact, phone…" />
            </label>

            <div class="grid grid-cols-2 sm:flex sm:items-center gap-2">
                <select wire:model.live="stage" class="input sm:w-44">
                    <option value="">All stages</option>
                    @foreach ($stages as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                <select wire:model.live="status" class="input sm:w-36">
                    <option value="">Any status</option>
                    <option value="active">Active</option>
                    <option value="dormant">Dormant</option>
                </select>
                @if ($isAdmin)
                    <select wire:model.live="assigned" class="input sm:w-44">
                        <option value="">All salespeople</option>
                        @foreach ($salespeople as $sp)
                            <option value="{{ $sp->id }}">{{ $sp->name }}</option>
                        @endforeach
                    </select>
                @endif
                <select wire:model.live="sort" class="input sm:w-40">
                    <option value="recent">Most recent</option>
                    <option value="name">Name (A–Z)</option>
                    <option value="revenue">Revenue potential</option>
                    <option value="stage">Pipeline stage</option>
                </select>
                @if ($hasFilters)
                    <button wire:click="clearFilters" class="btn-ghost sm:w-auto col-span-2">Clear</button>
                @endif
            </div>
        </div>
    </div>

    {{-- Results --}}
    @if ($clients->isEmpty())
        <x-empty-state icon="clients"
            title="{{ $hasFilters ? 'No clients match those filters' : 'No clients yet' }}"
            message="{{ $hasFilters ? 'Try widening your search or clearing the filters.' : 'Add your first business to start tracking visits and the pipeline.' }}">
            <x-slot:action>
                @if ($hasFilters)
                    <button wire:click="clearFilters" class="btn-ghost">Clear filters</button>
                @else
                    <button wire:click="newClient" class="btn-primary"><x-icon name="plus" class="w-4 h-4" /> Add business</button>
                @endif
            </x-slot:action>
        </x-empty-state>
    @else
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($clients as $client)
                <a href="{{ route('clients.show', $client) }}" wire:navigate
                   class="card p-4 hover:shadow-card-hover transition group block">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h3 class="font-semibold text-ink-900 truncate group-hover:text-primary transition">{{ $client->business_name }}</h3>
                            <p class="text-sm text-ink-700/60 truncate">
                                {{ $client->contact_person ?: 'No contact' }}{{ $client->category ? ' · '.$client->category : '' }}
                            </p>
                        </div>
                        @if ($client->status === 'dormant')
                            <span class="badge bg-danger/10 text-danger shrink-0" title="No visit in {{ \App\Models\Client::DORMANT_DAYS }}+ days">
                                <x-icon name="fire" class="w-3.5 h-3.5" /> Dormant
                            </span>
                        @endif
                    </div>

                    <div class="mt-3 flex items-center gap-2 flex-wrap">
                        <x-stage-badge :stage="$client->pipeline_stage" />
                        @if ($isAdmin)
                            <span class="badge bg-ink-50 text-ink-700">{{ $client->salesperson->name }}</span>
                        @endif
                    </div>

                    <div class="mt-4 pt-3 border-t border-ink-100 grid grid-cols-3 gap-2 text-center">
                        <div>
                            <p class="kpi-number text-base font-bold text-ink-900">{{ $client->visits_count }}</p>
                            <p class="text-[11px] text-ink-700/50">Visits</p>
                        </div>
                        <div>
                            <p class="kpi-number text-base font-bold text-primary">{{ rm_compact($client->revenue_potential_sum) }}</p>
                            <p class="text-[11px] text-ink-700/50">Potential</p>
                        </div>
                        <div>
                            <p class="kpi-number text-base font-bold text-ink-900">
                                {{ $client->last_visit_at ? $client->last_visit_at->diffForHumans(null, true) : '—' }}
                            </p>
                            <p class="text-[11px] text-ink-700/50">Last visit</p>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $clients->links() }}
        </div>
    @endif

    {{-- Shared create/edit modal --}}
    <livewire:clients.form />
</div>
