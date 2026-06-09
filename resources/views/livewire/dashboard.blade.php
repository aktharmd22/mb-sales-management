<div wire:loading.class="opacity-60" class="transition">
    {{-- ============ Header ============ --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-ink-900">
                {{ $isAdmin ? 'Team dashboard' : 'Welcome back, ' . str(auth()->user()->name)->before(' ') }}
            </h1>
            <p class="mt-1 text-sm text-ink-700/70">{{ $rangeLabel }}</p>
        </div>
        <div class="flex items-center gap-3 flex-wrap">
            @if ($isAdmin)
                <select wire:model.live="focus" class="input w-44">
                    <option value="">Whole team</option>
                    @foreach ($salespeople as $sp)
                        <option value="{{ $sp->id }}">{{ $sp->name }}</option>
                    @endforeach
                </select>
            @endif
            <x-period-toggle :periods="$periods" :current="$period" />
        </div>
    </div>

    {{-- ============ Momentum strip (signature hero) ============ --}}
    <div class="card overflow-hidden mb-5">
        <div class="grid lg:grid-cols-3">
            {{-- Big headline numbers --}}
            <div class="lg:col-span-2 p-5 sm:p-6 bg-ink-900 text-white">
                <p class="text-xs uppercase tracking-widest text-white/40">{{ \App\Support\Metrics::periodLabel($period) }} momentum</p>
                <div class="mt-4 grid grid-cols-2 sm:grid-cols-3 gap-y-5 gap-x-4">
                    <div>
                        <p class="kpi-number text-3xl sm:text-4xl font-bold leading-none">{{ number_format($kpis['visits']) }}</p>
                        <p class="text-sm text-white/50 mt-1">Businesses visited</p>
                    </div>
                    <div>
                        <p class="kpi-number text-3xl sm:text-4xl font-bold leading-none text-primary-300">{{ number_format($kpis['interested']) }}</p>
                        <p class="text-sm text-white/50 mt-1">Interested</p>
                    </div>
                    <div>
                        <p class="kpi-number text-3xl sm:text-4xl font-bold leading-none text-accent-300">{{ rm_compact($kpis['revenue_potential']) }}</p>
                        <p class="text-sm text-white/50 mt-1">Revenue potential</p>
                    </div>
                </div>
                <div class="mt-5 pt-4 border-t border-white/10 flex items-center gap-6 text-sm">
                    <span class="text-white/60">Won this period: <span class="font-semibold text-stage-won">{{ rm_compact($deals['won_revenue']) }}</span></span>
                    <span class="text-white/60">Deals won: <span class="font-semibold text-white">{{ $deals['won'] }}</span></span>
                </div>
            </div>

            {{-- Funnel snapshot OR target ring --}}
            <div class="p-5 sm:p-6">
                @if ($isAdmin)
                    <p class="text-sm font-semibold text-ink-900">Pipeline now</p>
                    <div class="mt-2 space-y-1.5">
                        @foreach ($funnel['labels'] as $i => $label)
                            @php $total = max(array_sum($funnel['data']), 1); $w = round(($funnel['data'][$i] / $total) * 100); @endphp
                            <div class="flex items-center gap-2">
                                <span class="text-[11px] text-ink-700/60 w-24 truncate">{{ $label }}</span>
                                <div class="flex-1 h-2 rounded-full bg-ink-50 overflow-hidden">
                                    <div class="h-full rounded-full" style="width: {{ max($w, 3) }}%; background: {{ $funnel['colors'][$i] }}"></div>
                                </div>
                                <span class="tnum text-xs font-semibold text-ink-900 w-6 text-right">{{ $funnel['data'][$i] }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm font-semibold text-ink-900">This month's target</p>
                    @if ($target['has_target'])
                        <div class="grid grid-cols-2 gap-2 mt-1">
                            <div class="text-center">
                                <x-chart :options="$visitsRing" :chart-key="'ring-visits-'.$chartKeySuffix" />
                                <p class="-mt-2 text-xs text-ink-700/60">{{ $target['visits_done'] }} / {{ $target['visits_target'] ?: '—' }} visits</p>
                            </div>
                            <div class="text-center">
                                <x-chart :options="$revenueRing" :chart-key="'ring-rev-'.$chartKeySuffix" />
                                <p class="-mt-2 text-xs text-ink-700/60">{{ rm_compact($target['revenue_done']) }} / {{ $target['revenue_target'] ? rm_compact($target['revenue_target']) : '—' }}</p>
                            </div>
                        </div>
                    @else
                        <div class="grid place-items-center h-40 text-center">
                            <p class="text-sm text-ink-700/50">No target set for this month yet.<br>Your manager can set one.</p>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>

    {{-- ============ KPI cards ============ --}}
    <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3 mb-5">
        <x-kpi label="Businesses visited" :value="number_format($kpis['visits'])" icon="visit" tone="ink" />
        <x-kpi label="Decision makers met" :value="number_format($kpis['decision_makers'])" icon="user" tone="ink" />
        <x-kpi label="Interested" :value="number_format($kpis['interested'])" icon="sparkles" tone="primary" />
        <x-kpi label="Follow-ups done" :value="number_format($kpis['follow_ups_done'])" icon="check-circle" tone="ink" />
        <x-kpi label="Proposals sent" :value="number_format($kpis['proposals'])" icon="reports" tone="accent" />
        <x-kpi label="Revenue potential" :value="rm_compact($kpis['revenue_potential'])" icon="cash" tone="primary" />
    </div>

    {{-- ============ Charts ============ --}}
    <div class="grid lg:grid-cols-3 gap-5 mb-5">
        <div class="card p-5 lg:col-span-2">
            <div class="flex items-center justify-between mb-2">
                <h2 class="font-bold text-ink-900">Visits over time</h2>
            </div>
            <x-chart :options="$visitsChart" :chart-key="'visits-'.$chartKeySuffix" />
        </div>
        <div class="card p-5">
            <h2 class="font-bold text-ink-900 mb-2">Pipeline funnel</h2>
            <x-chart :options="$funnelChart" :chart-key="'funnel-'.$chartKeySuffix" />
            @if (count($funnel['conversion']))
                <div class="mt-2 flex items-center gap-1 flex-wrap text-[11px] text-ink-700/50">
                    <span>Conversion:</span>
                    @foreach ($funnel['conversion'] as $cv)
                        <span class="badge bg-ink-50 text-ink-700">{{ $cv }}%</span>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="card p-5 mb-5">
        <h2 class="font-bold text-ink-900 mb-2">Revenue potential trend</h2>
        <x-chart :options="$revenueChart" :chart-key="'revenue-'.$chartKeySuffix" />
    </div>

    {{-- ============ Role-specific lower section ============ --}}
    @if ($isAdmin)
        <div class="grid lg:grid-cols-2 gap-5">
            {{-- Leaderboard --}}
            <div class="card p-5">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="font-bold text-ink-900">Leaderboard</h2>
                    <x-icon name="leaderboard" class="w-5 h-5 text-accent" />
                </div>
                <div class="space-y-2">
                    @foreach ($leaderboard as $i => $person)
                        <div class="flex items-center gap-3 p-2.5 rounded-xl {{ $i === 0 ? 'bg-accent/10' : 'hover:bg-canvas' }}">
                            <span @class([
                                'grid place-items-center h-7 w-7 rounded-full text-xs font-bold shrink-0',
                                'bg-accent text-ink-900' => $i === 0,
                                'bg-ink-100 text-ink-700' => $i !== 0,
                            ])>{{ $i + 1 }}</span>
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-ink-900 truncate">{{ $person->name }}</p>
                                <p class="text-xs text-ink-700/50 tnum">{{ $person->period_visits }} visits · {{ $person->period_interested }} interested</p>
                            </div>
                            <span class="kpi-number font-bold text-primary shrink-0">{{ rm_compact($person->period_revenue) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Activity feed --}}
            <div class="card p-5">
                <h2 class="font-bold text-ink-900 mb-3">Recent activity</h2>
                <div class="space-y-3">
                    @forelse ($activity as $visit)
                        <div class="flex items-start gap-3">
                            <span class="mt-0.5 h-2 w-2 rounded-full shrink-0" style="background: {{ \App\Support\Pipeline::hex($visit->visit_level) }}"></span>
                            <div class="min-w-0 flex-1 text-sm">
                                <p class="text-ink-900">
                                    <span class="font-semibold">{{ $visit->salesperson->name }}</span>
                                    logged a <span class="font-medium">{{ \App\Support\Pipeline::label($visit->visit_level) }}</span> at
                                    <a href="{{ route('clients.show', $visit->client_id) }}" wire:navigate class="text-primary hover:underline">{{ $visit->client->business_name }}</a>
                                </p>
                                <p class="text-xs text-ink-700/40 tnum">{{ $visit->visit_date->diffForHumans() }}{{ $visit->revenue_potential > 0 ? ' · '.rm($visit->revenue_potential) : '' }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-ink-700/50">No activity in this period.</p>
                    @endforelse
                </div>
            </div>
        </div>
    @else
        {{-- Follow-ups due --}}
        <div class="card p-5">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-bold text-ink-900">Follow-ups due</h2>
                <a href="{{ route('followups.index') }}" wire:navigate class="text-sm font-medium text-primary">View all</a>
            </div>
            @forelse ($dueFollowUps as $fu)
                <div class="flex items-center gap-3 py-2.5 {{ ! $loop->last ? 'border-b border-ink-50' : '' }}">
                    <span class="grid place-items-center h-8 w-8 rounded-lg shrink-0 {{ $fu->isOverdue() ? 'bg-danger/10 text-danger' : 'bg-accent/10 text-accent-600' }}">
                        <x-icon name="clock" class="w-4 h-4" />
                    </span>
                    <div class="flex-1 min-w-0">
                        <a href="{{ route('clients.show', $fu->client_id) }}" wire:navigate class="font-medium text-ink-900 hover:text-primary truncate block">{{ $fu->client->business_name }}</a>
                        <p class="text-xs text-ink-700/60 truncate">{{ $fu->note }}</p>
                    </div>
                    <span class="text-xs font-medium tnum shrink-0 {{ $fu->isOverdue() ? 'text-danger' : 'text-ink-700/60' }}">
                        {{ $fu->isOverdue() ? 'Overdue' : 'Today' }}
                    </span>
                </div>
            @empty
                <p class="text-sm text-ink-700/50 py-2">Nothing due. Nice and clear. ✨</p>
            @endforelse
        </div>
    @endif
</div>
