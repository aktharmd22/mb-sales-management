<div class="max-w-2xl mx-auto">
    <a href="{{ route('clients.index') }}" wire:navigate class="inline-flex items-center gap-1 text-sm text-ink-700/60 hover:text-ink-900 mb-4">
        <x-icon name="chevron-right" class="w-4 h-4 rotate-180" /> Clients
    </a>

    <x-page-header title="Import from Excel" subtitle="Bring your existing spreadsheet straight in — each row becomes a visit." />

    <div class="card p-6">
        <div class="rounded-xl bg-canvas p-4 text-sm text-ink-700/80">
            <p class="font-semibold text-ink-900 mb-1">How it works</p>
            <p>Each row is logged as a visit, and the <strong>Business</strong> column becomes a client (created once, reused across rows). Expected columns: Date, Business, Person Met, Client Phone, Visit Level, Decision Maker Met?, Interested?, Follow-up Done?, Revenue Potential (RM), Notes.</p>
            <button wire:click="downloadTemplate" class="btn-ghost mt-3 text-sm">
                <x-icon name="download" class="w-4 h-4" /> Download template
            </button>
        </div>

        <form wire:submit="import" class="mt-5 space-y-4">
            <div>
                <label class="label">Assign imported records to</label>
                <select wire:model="assignTo" class="input">
                    @foreach ($salespeople as $sp)
                        <option value="{{ $sp->id }}">{{ $sp->name }}</option>
                    @endforeach
                </select>
                @error('assignTo') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="label">Spreadsheet file (.xlsx, .xls, .csv)</label>
                <input type="file" wire:model="file" accept=".xlsx,.xls,.csv"
                       class="block w-full text-sm text-ink-700 file:mr-3 file:rounded-lg file:border-0 file:bg-ink-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-ink-700 hover:file:bg-ink-100" />
                <div wire:loading wire:target="file" class="mt-1 text-xs text-ink-700/60">Uploading…</div>
                @error('file') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="btn-primary w-full" wire:loading.attr="disabled" wire:target="import">
                <span wire:loading.remove wire:target="import"><x-icon name="upload" class="w-4 h-4" /> Import</span>
                <span wire:loading wire:target="import">Importing…</span>
            </button>
        </form>

        @if ($result)
            <div class="mt-5 rounded-xl border border-primary/20 bg-primary/5 p-4 animate-fade-in-up">
                <p class="font-semibold text-ink-900 flex items-center gap-1.5"><x-icon name="check-circle" class="w-5 h-5 text-primary" /> Import complete</p>
                <div class="mt-3 grid grid-cols-3 gap-3 text-center">
                    <div class="rounded-lg bg-surface py-2">
                        <p class="kpi-number text-xl font-bold text-primary">{{ $result['visits'] }}</p>
                        <p class="text-[11px] text-ink-700/60">Visits</p>
                    </div>
                    <div class="rounded-lg bg-surface py-2">
                        <p class="kpi-number text-xl font-bold text-ink-900">{{ $result['clients'] }}</p>
                        <p class="text-[11px] text-ink-700/60">New clients</p>
                    </div>
                    <div class="rounded-lg bg-surface py-2">
                        <p class="kpi-number text-xl font-bold text-ink-700/60">{{ $result['skipped'] }}</p>
                        <p class="text-[11px] text-ink-700/60">Skipped</p>
                    </div>
                </div>
                @if (! empty($result['errors']))
                    <ul class="mt-3 text-xs text-danger space-y-0.5">
                        @foreach ($result['errors'] as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                @endif
                <a href="{{ route('clients.index') }}" wire:navigate class="btn-primary w-full mt-4">View clients</a>
            </div>
        @endif
    </div>
</div>
