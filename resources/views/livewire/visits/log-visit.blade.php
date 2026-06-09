<div class="max-w-2xl mx-auto">
    <x-page-header title="Client Visit" subtitle="Capture what happened — it takes under a minute." />

    <form wire:submit="save" class="space-y-5 pb-24">

        {{-- ============ 1. Which business? ============ --}}
        <div class="card p-5">
            <p class="label">Business</p>

            @if ($clientId)
                <div class="flex items-center justify-between gap-3 rounded-xl bg-primary/5 border border-primary/15 px-4 py-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="grid place-items-center h-10 w-10 rounded-xl bg-primary/15 text-primary shrink-0">
                            <x-icon name="building" class="w-5 h-5" />
                        </span>
                        <div class="min-w-0">
                            <p class="font-semibold text-ink-900 truncate">{{ $clientLabel }}</p>
                            <p class="text-xs text-ink-700/60">{{ $contact_phone ?: 'No phone on file' }}</p>
                        </div>
                    </div>
                    <button type="button" wire:click="clearClient" class="text-sm font-medium text-ink-700/70 hover:text-danger shrink-0">Change</button>
                </div>
            @else
                <div x-data="{ open: true }">
                    @if (! $quickAdd)
                        <label class="relative block">
                            <span class="absolute inset-y-0 left-3 grid place-items-center text-ink-700/40"><x-icon name="search" class="w-4 h-4" /></span>
                            <input type="text" wire:model.live.debounce.250ms="clientSearch" class="input pl-9" placeholder="Search your businesses…" />
                        </label>

                        <div class="mt-2 rounded-xl border border-ink-100 divide-y divide-ink-100 overflow-hidden">
                            @forelse ($this->matchingClients as $c)
                                <button type="button" wire:click="selectClient({{ $c->id }})"
                                        class="w-full flex items-center justify-between gap-3 px-4 py-3 hover:bg-canvas text-left transition">
                                    <span class="min-w-0">
                                        <span class="block font-medium text-ink-900 truncate">{{ $c->business_name }}</span>
                                        <span class="block text-xs text-ink-700/60 truncate">{{ $c->contact_person ?: 'No contact' }}{{ $c->contact_phone ? ' · '.$c->contact_phone : '' }}</span>
                                    </span>
                                    <x-stage-badge :stage="$c->pipeline_stage" class="shrink-0" />
                                </button>
                            @empty
                                <p class="px-4 py-4 text-sm text-ink-700/60 text-center">
                                    {{ $clientSearch ? 'No match. Add it as a new business below.' : 'Start typing, or add a new business.' }}
                                </p>
                            @endforelse
                        </div>

                        <button type="button" wire:click="$set('quickAdd', true)" class="btn-ghost w-full mt-2">
                            <x-icon name="plus" class="w-4 h-4" /> New business
                        </button>
                    @else
                        {{-- Quick-add inline --}}
                        <div class="space-y-3">
                            <div>
                                <label class="label">Business name</label>
                                <input type="text" wire:model="newBusinessName" class="input" placeholder="e.g. Sunrise Mart" />
                                @error('newBusinessName') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="label">Phone <span class="text-ink-700/40">(optional)</span></label>
                                <input type="text" wire:model="newPhone" class="input" placeholder="+6012-345-6789" />
                            </div>
                            <div class="flex gap-2">
                                <button type="button" wire:click="$set('quickAdd', false)" class="btn-ghost flex-1">Back</button>
                                <button type="button" wire:click="createQuickClient" class="btn-primary flex-1">Add & select</button>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
            @error('clientId') <p class="mt-2 text-xs text-danger">Pick a business first.</p> @enderror
        </div>

        {{-- The rest only makes sense once a business is chosen --}}
        <div @class(['space-y-5 transition', 'opacity-40 pointer-events-none' => ! $clientId])>

            {{-- ============ 2. Stage ============ --}}
            <div class="card p-5">
                <p class="label">Visit stage</p>
                <div class="flex gap-2 overflow-x-auto no-scrollbar -mx-1 px-1 py-1">
                    @foreach ($stages as $key => $label)
                        @php $c = \App\Support\Pipeline::color($key); @endphp
                        <button type="button" wire:click="$set('visit_level', '{{ $key }}')"
                                @class([
                                    'shrink-0 rounded-xl px-3.5 py-2 text-sm font-semibold border transition',
                                    "bg-stage-{$c}/10 border-stage-{$c} text-stage-{$c}" => $visit_level === $key,
                                    'bg-surface border-ink-100 text-ink-700/70 hover:border-ink-200' => $visit_level !== $key,
                                ])>
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- ============ 3. Outcome toggles ============ --}}
            <div class="card p-5 space-y-4">
                <x-visit-toggle field="decision_maker_met" :value="$decision_maker_met" label="Met the decision maker?" />
                <x-visit-toggle field="interested" :value="$interested" label="Are they interested?" />
                <x-visit-toggle field="follow_up_done" :value="$follow_up_done" label="Follow-up done?" />
            </div>

            {{-- ============ 4. Details ============ --}}
            <div class="card p-5 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="label">Person met</label>
                        <input type="text" wire:model="person_met" class="input" placeholder="Who you spoke to" />
                    </div>
                    <div>
                        <label class="label">Phone</label>
                        <input type="text" wire:model="contact_phone" class="input" placeholder="Contact number" />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="label">Revenue potential (RM)</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-3 grid place-items-center text-ink-700/50 text-sm font-semibold">RM</span>
                            <input type="number" step="0.01" min="0" wire:model="revenue_potential" class="input pl-10 tnum" placeholder="0" />
                        </div>
                        @error('revenue_potential') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label">Visit date</label>
                        <input type="date" wire:model="visit_date" max="{{ today()->toDateString() }}" class="input tnum" />
                        @error('visit_date') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="label">Notes <span class="text-ink-700/40">(optional)</span></label>
                    <textarea wire:model="notes" rows="2" class="input" placeholder="What was discussed, next steps…"></textarea>
                </div>

                <div>
                    <label class="label">Photo <span class="text-ink-700/40">(optional)</span></label>
                    <input type="file" wire:model="photo" accept="image/*"
                           class="block w-full text-sm text-ink-700 file:mr-3 file:rounded-lg file:border-0 file:bg-ink-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-ink-700 hover:file:bg-ink-100" />
                    <div wire:loading wire:target="photo" class="mt-1 text-xs text-ink-700/60">Uploading…</div>
                    @error('photo') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    @if ($photo)
                        <img src="{{ $photo->temporaryUrl() }}" class="mt-2 h-24 rounded-xl object-cover" alt="preview" />
                    @endif
                </div>
            </div>

            {{-- ============ 5. Schedule a follow-up ============ --}}
            <div class="card p-5">
                <label class="flex items-center justify-between gap-3 cursor-pointer">
                    <span>
                        <span class="block font-medium text-ink-900">Schedule a follow-up</span>
                        <span class="block text-xs text-ink-700/60">So it never goes cold.</span>
                    </span>
                    <input type="checkbox" wire:model.live="scheduleFollowUp" class="rounded border-ink-100 text-primary focus:ring-primary h-5 w-5" />
                </label>

                @if ($scheduleFollowUp)
                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4 animate-fade-in-up">
                        <div>
                            <label class="label">Due date</label>
                            <input type="date" wire:model="followUpDate" min="{{ today()->toDateString() }}" class="input tnum" />
                            @error('followUpDate') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="label">Note</label>
                            <input type="text" wire:model="followUpNote" class="input" placeholder="e.g. Send proposal" />
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- ============ Sticky save bar ============ --}}
        <div class="fixed bottom-16 lg:bottom-0 inset-x-0 z-30 bg-surface/90 backdrop-blur border-t border-ink-100 px-4 sm:px-6 lg:pl-72 py-3">
            <div class="max-w-2xl mx-auto flex items-center gap-3">
                <a href="{{ url()->previous() }}" class="btn-ghost">Cancel</a>
                <button type="submit" class="btn-primary flex-1" wire:loading.attr="disabled" wire:target="save" @disabled(! $clientId)>
                    <span wire:loading.remove wire:target="save"><x-icon name="check" class="w-4 h-4" /> Save visit</span>
                    <span wire:loading wire:target="save">Saving…</span>
                </button>
            </div>
        </div>
    </form>
</div>
