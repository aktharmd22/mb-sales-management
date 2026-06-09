<div>
    <a href="{{ route('clients.index') }}" wire:navigate class="inline-flex items-center gap-1 text-sm text-ink-700/60 hover:text-ink-900 mb-4">
        <x-icon name="chevron-right" class="w-4 h-4 rotate-180" /> All clients
    </a>

    {{-- ============ Header ============ --}}
    <div class="card p-5 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div class="flex items-start gap-4 min-w-0">
                <span class="grid place-items-center h-14 w-14 rounded-2xl bg-primary/10 text-primary shrink-0">
                    <x-icon name="building" class="w-7 h-7" />
                </span>
                <div class="min-w-0">
                    <h1 class="text-xl sm:text-2xl font-bold text-ink-900 truncate">{{ $client->business_name }}</h1>
                    <div class="mt-2 flex items-center gap-2 flex-wrap">
                        <x-stage-badge :stage="$client->pipeline_stage" />
                        @if ($client->status === 'dormant')
                            <span class="badge bg-danger/10 text-danger"><x-icon name="fire" class="w-3.5 h-3.5" /> Dormant</span>
                        @else
                            <span class="badge bg-primary/10 text-primary">Active</span>
                        @endif
                        @if ($isAdmin)
                            <span class="badge bg-ink-50 text-ink-700"><x-icon name="user" class="w-3 h-3" /> {{ $client->salesperson->name }}</span>
                        @endif
                    </div>
                    <div class="mt-3 flex items-center gap-x-5 gap-y-1 flex-wrap text-sm text-ink-700/70">
                        @if ($client->contact_person)
                            <span class="inline-flex items-center gap-1.5"><x-icon name="user" class="w-4 h-4 text-ink-700/40" /> {{ $client->contact_person }}</span>
                        @endif
                        @if ($client->contact_phone)
                            <a href="tel:{{ $client->contact_phone }}" class="inline-flex items-center gap-1.5 hover:text-primary"><x-icon name="phone" class="w-4 h-4 text-ink-700/40" /> {{ $client->contact_phone }}</a>
                        @endif
                        @if ($client->address)
                            <span class="inline-flex items-center gap-1.5"><x-icon name="visit" class="w-4 h-4 text-ink-700/40" /> {{ $client->address }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <button wire:click="editClient" class="btn-ghost"><x-icon name="edit" class="w-4 h-4" /> Edit</button>
                <a href="{{ route('visits.create', ['client' => $client->id]) }}" wire:navigate class="btn-primary">
                    <x-icon name="plus" class="w-4 h-4" /> Client Visit
                </a>
            </div>
        </div>

        {{-- Pipeline stepper --}}
        <div class="mt-6 flex items-center gap-1.5 overflow-x-auto no-scrollbar">
            @php $reached = \App\Support\Pipeline::rank($client->pipeline_stage); @endphp
            @foreach ($stageKeys as $i => $key)
                @php $c = \App\Support\Pipeline::color($key); $on = $i <= $reached && ! $isTerminal || ($isTerminal && $client->pipeline_stage === 'won'); @endphp
                <div class="flex-1 min-w-[64px]">
                    <div class="h-1.5 rounded-full {{ ($i <= $reached) ? "bg-stage-{$c}" : 'bg-ink-100' }}"></div>
                    <p class="mt-1.5 text-[11px] {{ ($i <= $reached) ? 'text-ink-700 font-medium' : 'text-ink-700/40' }} truncate">{{ \App\Support\Pipeline::label($key) }}</p>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ============ Stats ============ --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 mt-5">
        <div class="card p-4">
            <p class="text-xs text-ink-700/60">Visits</p>
            <p class="kpi-number text-2xl font-bold text-ink-900 mt-1">{{ $stats['visits'] }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs text-ink-700/60">Revenue potential</p>
            <p class="kpi-number text-2xl font-bold text-primary mt-1">{{ rm_compact($stats['revenue_potential']) }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs text-ink-700/60">Decision makers met</p>
            <p class="kpi-number text-2xl font-bold text-ink-900 mt-1">{{ $stats['decision_makers'] }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs text-ink-700/60">Interested visits</p>
            <p class="kpi-number text-2xl font-bold text-ink-900 mt-1">{{ $stats['interested'] }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs text-ink-700/60">Won revenue</p>
            <p class="kpi-number text-2xl font-bold text-stage-won mt-1">{{ rm_compact($stats['won_revenue']) }}</p>
        </div>
    </div>

    {{-- ============ Body ============ --}}
    <div class="grid lg:grid-cols-3 gap-5 mt-5">

        {{-- Visit timeline --}}
        <div class="lg:col-span-2 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-ink-900">Visit history</h2>
                <span class="text-sm text-ink-700/50">{{ $visits->count() }} {{ Str::plural('visit', $visits->count()) }}</span>
            </div>

            @if ($visits->isEmpty())
                <x-empty-state icon="visit" title="No visits logged yet"
                    message="Log the first visit to start building this relationship's history.">
                    <x-slot:action>
                        <a href="{{ route('visits.create', ['client' => $client->id]) }}" wire:navigate class="btn-primary"><x-icon name="plus" class="w-4 h-4" /> Client Visit</a>
                    </x-slot:action>
                </x-empty-state>
            @else
                <ol class="relative border-l-2 border-ink-100 ml-2 space-y-4">
                    @foreach ($visits as $visit)
                        @php $c = $visit->levelColor(); @endphp
                        <li class="ml-5">
                            <span class="absolute -left-[9px] grid place-items-center h-4 w-4 rounded-full bg-stage-{{ $c }} ring-4 ring-canvas"></span>
                            <div class="card p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <x-stage-badge :stage="$visit->visit_level" />
                                            <span class="text-sm font-semibold text-ink-900 tnum">{{ $visit->visit_date->format('d M Y') }}</span>
                                        </div>
                                        @if ($visit->person_met)
                                            <p class="text-sm text-ink-700/70 mt-1.5">Met <span class="font-medium text-ink-900">{{ $visit->person_met }}</span></p>
                                        @endif
                                    </div>
                                    @if ($visit->revenue_potential > 0)
                                        <span class="badge bg-primary/10 text-primary tnum shrink-0">{{ rm($visit->revenue_potential) }}</span>
                                    @endif
                                </div>

                                <div class="mt-3 flex items-center gap-2 flex-wrap">
                                    @if ($visit->decision_maker_met) <span class="badge bg-ink-50 text-ink-700"><x-icon name="check" class="w-3 h-3 text-primary" /> Decision maker</span> @endif
                                    @if ($visit->interested) <span class="badge bg-ink-50 text-ink-700"><x-icon name="check" class="w-3 h-3 text-primary" /> Interested</span> @endif
                                    @if ($visit->follow_up_done) <span class="badge bg-ink-50 text-ink-700"><x-icon name="check" class="w-3 h-3 text-primary" /> Follow-up done</span> @endif
                                    @if ($isAdmin) <span class="badge bg-ink-50 text-ink-700/70">by {{ $visit->salesperson->name }}</span> @endif
                                </div>

                                @if ($visit->notes)
                                    <p class="mt-3 text-sm text-ink-700/80 bg-canvas rounded-lg p-3">{{ $visit->notes }}</p>
                                @endif
                                @if ($visit->photo_path)
                                    <a href="{{ Storage::url($visit->photo_path) }}" target="_blank">
                                        <img src="{{ Storage::url($visit->photo_path) }}" class="mt-3 h-32 rounded-xl object-cover" alt="visit photo" />
                                    </a>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ol>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-5">

            {{-- Follow-ups --}}
            <div class="card p-5">
                <div class="flex items-center justify-between">
                    <h3 class="font-bold text-ink-900">Follow-ups</h3>
                    <button wire:click="startFollowUp" class="text-sm font-medium text-primary hover:text-primary-600">+ Add</button>
                </div>

                @if ($addingFollowUp)
                    <div class="mt-3 space-y-2 animate-fade-in-up">
                        <input type="date" wire:model="fuDate" min="{{ today()->toDateString() }}" class="input tnum" />
                        @error('fuDate') <p class="text-xs text-danger">{{ $message }}</p> @enderror
                        <input type="text" wire:model="fuNote" class="input" placeholder="What's the next step?" />
                        <div class="flex gap-2">
                            <button wire:click="$set('addingFollowUp', false)" class="btn-ghost flex-1 text-sm">Cancel</button>
                            <button wire:click="saveFollowUp" class="btn-primary flex-1 text-sm">Schedule</button>
                        </div>
                    </div>
                @endif

                <div class="mt-3 space-y-2">
                    @forelse ($followUps as $fu)
                        <div class="flex items-start gap-2.5 rounded-xl p-2.5 {{ $fu->status === 'done' ? 'opacity-50' : ($fu->isOverdue() ? 'bg-danger/5' : 'bg-canvas') }}">
                            <button wire:click="{{ $fu->status === 'done' ? 'reopenFollowUp' : 'completeFollowUp' }}({{ $fu->id }})"
                                    class="mt-0.5 grid place-items-center h-5 w-5 rounded-md border-2 shrink-0 transition
                                           {{ $fu->status === 'done' ? 'bg-primary border-primary text-white' : 'border-ink-200 hover:border-primary' }}">
                                @if ($fu->status === 'done') <x-icon name="check" class="w-3 h-3" /> @endif
                            </button>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm text-ink-900 {{ $fu->status === 'done' ? 'line-through' : '' }}">{{ $fu->note }}</p>
                                <p class="text-xs {{ $fu->isOverdue() ? 'text-danger font-medium' : 'text-ink-700/50' }} tnum">
                                    {{ $fu->isOverdue() ? 'Overdue · ' : '' }}{{ $fu->due_date->format('d M Y') }}
                                </p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-ink-700/50 py-2">No follow-ups. Nice and clear.</p>
                    @endforelse
                </div>
            </div>

            {{-- Deal --}}
            <div class="card p-5">
                <h3 class="font-bold text-ink-900">Deal</h3>
                @if ($client->deals->isNotEmpty())
                    @foreach ($client->deals as $deal)
                        <div class="mt-3 flex items-center justify-between gap-2 rounded-xl p-3 {{ $deal->outcome === 'won' ? 'bg-stage-won/10' : 'bg-danger/5' }}">
                            <div>
                                <p class="font-semibold {{ $deal->outcome === 'won' ? 'text-stage-won' : 'text-danger' }} capitalize">{{ $deal->outcome }}</p>
                                <p class="text-xs text-ink-700/50 tnum">{{ $deal->closed_at->format('d M Y') }}</p>
                            </div>
                            @if ($deal->outcome === 'won')
                                <span class="kpi-number text-lg font-bold text-stage-won">{{ rm($deal->actual_revenue) }}</span>
                            @endif
                        </div>
                    @endforeach
                @else
                    <p class="mt-2 text-sm text-ink-700/60">No deal closed yet.</p>
                    <div class="mt-3 flex gap-2">
                        <button wire:click="startDeal('won')" class="btn flex-1 bg-stage-won/10 text-stage-won text-sm"><x-icon name="handshake" class="w-4 h-4" /> Won</button>
                        <button wire:click="startDeal('lost')" class="btn flex-1 bg-danger/10 text-danger text-sm">Lost</button>
                    </div>
                @endif
            </div>

            {{-- Notes --}}
            @if ($client->notes)
                <div class="card p-5">
                    <h3 class="font-bold text-ink-900">Notes</h3>
                    <p class="mt-2 text-sm text-ink-700/80">{{ $client->notes }}</p>
                </div>
            @endif
        </div>
    </div>

    {{-- ============ Close deal modal ============ --}}
    <x-modal-panel show="closingDeal" maxWidth="max-w-md">
        <form wire:submit="saveDeal" class="p-6">
            <h2 class="text-lg font-bold text-ink-900">Close this deal</h2>
            <div class="mt-4 inline-flex rounded-xl bg-ink-50 p-1 w-full">
                <button type="button" wire:click="$set('dealOutcome', 'won')"
                        @class(['flex-1 py-2 rounded-lg text-sm font-semibold transition', 'bg-stage-won text-white' => $dealOutcome==='won', 'text-ink-700/60' => $dealOutcome!=='won'])>Won</button>
                <button type="button" wire:click="$set('dealOutcome', 'lost')"
                        @class(['flex-1 py-2 rounded-lg text-sm font-semibold transition', 'bg-danger text-white' => $dealOutcome==='lost', 'text-ink-700/60' => $dealOutcome!=='lost'])>Lost</button>
            </div>

            @if ($dealOutcome === 'won')
                <div class="mt-4">
                    <label class="label">Actual revenue (RM)</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 grid place-items-center text-ink-700/50 text-sm font-semibold">RM</span>
                        <input type="number" step="0.01" min="0" wire:model="dealRevenue" class="input pl-10 tnum" placeholder="0" />
                    </div>
                    @error('dealRevenue') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>
            @endif

            <div class="mt-4">
                <label class="label">Closed on</label>
                <input type="date" wire:model="dealDate" class="input tnum" />
            </div>
            <div class="mt-4">
                <label class="label">Notes <span class="text-ink-700/40">(optional)</span></label>
                <input type="text" wire:model="dealNotes" class="input" placeholder="Anything to remember" />
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <button type="button" wire:click="$set('closingDeal', false)" class="btn-ghost">Cancel</button>
                <button type="submit" class="btn-primary">Save deal</button>
            </div>
        </form>
    </x-modal-panel>

    <livewire:clients.form />
</div>
