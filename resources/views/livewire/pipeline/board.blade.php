<div>
    <x-page-header title="Pipeline" subtitle="See and move every client through the funnel.">
        <x-slot:actions>
            @if ($isAdmin)
                <select wire:model.live="focus" class="input w-44">
                    <option value="">Whole team</option>
                    @foreach ($salespeople as $sp)
                        <option value="{{ $sp->id }}">{{ $sp->name }}</option>
                    @endforeach
                </select>
            @endif
            <div class="inline-flex rounded-xl bg-ink-50 p-1 text-sm">
                <button wire:click="$set('view', 'kanban')" @class(['px-4 py-1.5 rounded-lg font-semibold transition', 'bg-surface text-ink-900 shadow-sm' => $view==='kanban', 'text-ink-700/60' => $view!=='kanban'])>Board</button>
                <button wire:click="$set('view', 'funnel')" @class(['px-4 py-1.5 rounded-lg font-semibold transition', 'bg-surface text-ink-900 shadow-sm' => $view==='funnel', 'text-ink-700/60' => $view!=='funnel'])>Funnel</button>
            </div>
        </x-slot:actions>
    </x-page-header>

    @if ($view === 'funnel')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <div class="card p-5 lg:col-span-2">
                <h2 class="font-bold text-ink-900 mb-2">Clients per stage</h2>
                <x-chart :options="$funnelChart" :chart-key="'pipe-funnel-'.$chartKeySuffix" />
            </div>
            <div class="card p-5">
                <h2 class="font-bold text-ink-900 mb-3">Stage-to-stage conversion</h2>
                <div class="space-y-3">
                    @foreach ($funnel['conversion'] as $i => $cv)
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-ink-700/70">{{ $funnel['labels'][$i] }} → {{ $funnel['labels'][$i+1] }}</span>
                                <span class="tnum font-semibold text-ink-900">{{ $cv }}%</span>
                            </div>
                            <div class="h-2 rounded-full bg-ink-50 overflow-hidden">
                                <div class="h-full rounded-full bg-primary" style="width: {{ min($cv, 100) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-5 pt-4 border-t border-ink-100 flex items-center justify-between text-sm">
                    <span class="text-ink-700/60">Won</span>
                    <span class="badge bg-stage-won/10 text-stage-won">{{ $funnel['won'] }}</span>
                </div>
                <div class="mt-2 flex items-center justify-between text-sm">
                    <span class="text-ink-700/60">Lost</span>
                    <span class="badge bg-danger/10 text-danger">{{ $funnel['lost'] }}</span>
                </div>
            </div>
        </div>
    @else
        {{-- Kanban --}}
        <div x-data="kanban" class="flex gap-3 overflow-x-auto no-scrollbar pb-4">
            @foreach ($columns as $key => $col)
                <div class="w-72 shrink-0">
                    <div class="flex items-center gap-2 mb-2 px-1">
                        <span class="h-2.5 w-2.5 rounded-full" style="background: {{ $col['hex'] }}"></span>
                        <h3 class="font-semibold text-ink-900 text-sm">{{ $col['label'] }}</h3>
                        <span class="badge bg-ink-50 text-ink-700">{{ $col['clients']->count() }}</span>
                    </div>
                    <div data-stage-column="{{ $key }}"
                         class="space-y-2 min-h-[120px] rounded-2xl bg-ink-50/50 p-2 transition">
                        @foreach ($col['clients'] as $client)
                            <div data-client-id="{{ $client->id }}" wire:key="pc-{{ $client->id }}"
                                 class="card p-3 cursor-grab active:cursor-grabbing hover:shadow-card-hover transition">
                                <a href="{{ route('clients.show', $client) }}" wire:navigate class="font-semibold text-sm text-ink-900 hover:text-primary block truncate">
                                    {{ $client->business_name }}
                                </a>
                                <div class="mt-2 flex items-center justify-between gap-2">
                                    <span class="text-xs text-primary font-semibold tnum">{{ rm_compact($client->revenue_potential_sum) }}</span>
                                    @if ($client->status === 'dormant')
                                        <span class="badge bg-danger/10 text-danger text-[10px]"><x-icon name="fire" class="w-3 h-3" /></span>
                                    @endif
                                </div>
                                @if ($isAdmin)
                                    <p class="mt-1.5 text-[11px] text-ink-700/50 truncate">{{ $client->salesperson->name }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
        <p class="mt-2 text-xs text-ink-700/50">Drag a card to a new stage to update its pipeline position.</p>
    @endif
</div>
