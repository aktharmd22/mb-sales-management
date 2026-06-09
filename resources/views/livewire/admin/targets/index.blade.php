<div>
    <x-page-header title="Targets" subtitle="Set monthly visit and revenue goals per salesperson.">
        <x-slot:actions>
            <select wire:model.live="period" class="input w-44">
                @foreach ($periodOptions as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
            <button wire:click="save" class="btn-primary"><x-icon name="check" class="w-4 h-4" /> Save targets</button>
        </x-slot:actions>
    </x-page-header>

    <div class="card overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-ink-700/60 border-b border-ink-100">
                    <th class="font-medium px-5 py-3.5">Salesperson</th>
                    <th class="font-medium px-3 py-3.5">Visits target</th>
                    <th class="font-medium px-3 py-3.5">Revenue target (RM)</th>
                    <th class="font-medium px-5 py-3.5">Progress this month</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-100">
                @foreach ($salespeople as $sp)
                    @php
                        $vt = $rows[$sp->id]['visits'] ?? null;
                        $rt = $rows[$sp->id]['revenue'] ?? null;
                        $pv = $progress[$sp->id]['visits'];
                        $pr = $progress[$sp->id]['revenue'];
                    @endphp
                    <tr>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <span class="grid place-items-center h-9 w-9 rounded-full bg-primary/10 text-primary font-semibold text-xs shrink-0">
                                    {{ \Illuminate\Support\Str::of($sp->name)->explode(' ')->map(fn($p) => mb_substr($p,0,1))->take(2)->implode('') }}
                                </span>
                                <span class="font-semibold text-ink-900">{{ $sp->name }}</span>
                            </div>
                        </td>
                        <td class="px-3 py-3">
                            <input type="number" min="0" wire:model="rows.{{ $sp->id }}.visits" class="input tnum w-28" placeholder="—" />
                        </td>
                        <td class="px-3 py-3">
                            <div class="relative w-40">
                                <span class="absolute inset-y-0 left-3 grid place-items-center text-ink-700/50 text-xs font-semibold">RM</span>
                                <input type="number" min="0" step="0.01" wire:model="rows.{{ $sp->id }}.revenue" class="input tnum pl-9" placeholder="—" />
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            <div class="space-y-1.5 max-w-xs">
                                <div>
                                    <div class="flex justify-between text-[11px] text-ink-700/60 mb-0.5">
                                        <span>{{ $pv }} / {{ $vt ?: '—' }} visits</span>
                                        <span class="tnum">{{ $vt ? pct($pv, $vt) : 0 }}%</span>
                                    </div>
                                    <div class="h-1.5 rounded-full bg-ink-50 overflow-hidden">
                                        <div class="h-full rounded-full bg-primary" style="width: {{ $vt ? pct($pv, $vt) : 0 }}%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between text-[11px] text-ink-700/60 mb-0.5">
                                        <span>{{ rm_compact($pr) }} / {{ $rt ? rm_compact($rt) : '—' }}</span>
                                        <span class="tnum">{{ $rt ? pct($pr, $rt) : 0 }}%</span>
                                    </div>
                                    <div class="h-1.5 rounded-full bg-ink-50 overflow-hidden">
                                        <div class="h-full rounded-full bg-accent" style="width: {{ $rt ? pct($pr, $rt) : 0 }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <p class="mt-3 text-xs text-ink-700/50">Leave both fields blank to clear a salesperson's target for this month.</p>
</div>
