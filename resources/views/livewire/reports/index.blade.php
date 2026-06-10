<div wire:loading.class="opacity-60" class="transition">
    <x-page-header title="Reports" subtitle="{{ $rangeLabel }}">
        <x-slot:actions>
            @if ($isAdmin)
                <select wire:model.live="focus" class="input w-44">
                    <option value="">Whole team</option>
                    @foreach ($salespeople as $sp)
                        <option value="{{ $sp->id }}">{{ $sp->name }}</option>
                    @endforeach
                </select>
            @endif
            <x-period-toggle :periods="$periods" :current="$period" />
            <button wire:click="export" class="btn-accent" wire:loading.attr="disabled" wire:target="export">
                <x-icon name="download" class="w-4 h-4" /> <span wire:loading.remove wire:target="export">Export Excel</span><span wire:loading wire:target="export">Preparing…</span>
            </button>
        </x-slot:actions>
    </x-page-header>

    {{-- KPI summary --}}
    <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3 mb-5">
        <x-kpi label="Businesses visited" :value="number_format($kpis['visits'])" tone="ink" />
        <x-kpi label="Decision makers met" :value="number_format($kpis['decision_makers'])" tone="ink" />
        <x-kpi label="Interested" :value="number_format($kpis['interested'])" tone="primary" />
        <x-kpi label="Follow-ups done" :value="number_format($kpis['follow_ups_done'])" tone="ink" />
        <x-kpi label="Proposals sent" :value="number_format($kpis['proposals'])" tone="accent" />
        <x-kpi label="Revenue potential" :value="rm_compact($kpis['revenue_potential'])" tone="primary" />
    </div>

    {{-- Potential vs actual --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-5">
        <x-kpi label="Revenue potential (period)" :value="rm($kpis['revenue_potential'])" icon="cash" tone="primary" />
        <x-kpi label="Closed won (actual)" :value="rm($deals['won_revenue'])" icon="handshake" tone="won" sub="{{ $deals['won'] }} deals won" />
        <x-kpi label="Deals lost" :value="number_format($deals['lost'])" icon="arrow-down" tone="danger" />
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">
        <div class="card p-5">
            <h2 class="font-bold text-ink-900 mb-2">Visits over time</h2>
            <x-chart :options="$visitsChart" :chart-key="'rep-visits-'.$chartKeySuffix" />
        </div>
        <div class="card p-5">
            <h2 class="font-bold text-ink-900 mb-2">Revenue potential trend</h2>
            <x-chart :options="$revenueChart" :chart-key="'rep-rev-'.$chartKeySuffix" />
        </div>
    </div>

    {{-- Per-salesperson breakdown (team view) --}}
    @if ($isAdmin && $breakdown->isNotEmpty())
        <div class="card overflow-hidden">
            <div class="px-5 py-4 border-b border-ink-100">
                <h2 class="font-bold text-ink-900">Per-salesperson breakdown</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm whitespace-nowrap">
                    <thead>
                        <tr class="text-left text-ink-700/60 border-b border-ink-100">
                            <th class="font-medium px-5 py-3">Salesperson</th>
                            <th class="font-medium px-3 py-3 text-right">Visits</th>
                            <th class="font-medium px-3 py-3 text-right">Decision makers</th>
                            <th class="font-medium px-3 py-3 text-right">Interested</th>
                            <th class="font-medium px-3 py-3 text-right">Follow-ups</th>
                            <th class="font-medium px-3 py-3 text-right">Proposals</th>
                            <th class="font-medium px-3 py-3 text-right">Revenue potential</th>
                            <th class="font-medium px-5 py-3 text-right">Won (actual)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100">
                        @foreach ($breakdown as $row)
                            <tr class="hover:bg-canvas/60">
                                <td class="px-5 py-3 font-semibold text-ink-900">{{ $row['name'] }}</td>
                                <td class="px-3 py-3 text-right tnum">{{ $row['kpis']['visits'] }}</td>
                                <td class="px-3 py-3 text-right tnum">{{ $row['kpis']['decision_makers'] }}</td>
                                <td class="px-3 py-3 text-right tnum">{{ $row['kpis']['interested'] }}</td>
                                <td class="px-3 py-3 text-right tnum">{{ $row['kpis']['follow_ups_done'] }}</td>
                                <td class="px-3 py-3 text-right tnum">{{ $row['kpis']['proposals'] }}</td>
                                <td class="px-3 py-3 text-right tnum text-primary font-semibold">{{ rm($row['kpis']['revenue_potential']) }}</td>
                                <td class="px-5 py-3 text-right tnum text-stage-won font-semibold">{{ rm($row['won_revenue']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
