<div>
    <x-modal-panel show="show" maxWidth="max-w-xl">
        <form wire:submit="save" class="p-6 max-h-[85vh] overflow-y-auto">
            <h2 class="text-lg font-bold text-ink-900">
                {{ $clientId ? 'Edit client' : 'Add a business' }}
            </h2>
            <p class="text-sm text-ink-700/60 mt-1">
                {{ $clientId ? 'Keep this business record up to date.' : 'A business is a lasting record — every visit attaches to it.' }}
            </p>

            <div class="mt-5 space-y-4">
                <div>
                    <label class="label">Business name</label>
                    <input type="text" wire:model.live.debounce.400ms="business_name" class="input" placeholder="e.g. Sunrise Mart Sdn Bhd" autofocus />
                    @error('business_name') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>

                {{-- Duplicate warning --}}
                @if (! $clientId && $this->duplicates->isNotEmpty())
                    <div class="rounded-xl border border-accent-200 bg-accent-50 p-3">
                        <p class="text-sm font-semibold text-accent-800 flex items-center gap-1.5">
                            <x-icon name="sparkles" class="w-4 h-4" /> Possible duplicate{{ $this->duplicates->count() > 1 ? 's' : '' }}
                        </p>
                        <ul class="mt-2 space-y-1">
                            @foreach ($this->duplicates as $dupe)
                                <li class="flex items-center justify-between gap-2 text-sm">
                                    <span class="text-ink-700 truncate">{{ $dupe->business_name }}<span class="text-ink-700/50"> · {{ $dupe->contact_phone ?: 'no phone' }}</span></span>
                                    <a href="{{ route('clients.show', $dupe) }}" wire:navigate class="text-primary font-medium shrink-0">Open</a>
                                </li>
                            @endforeach
                        </ul>
                        <p class="mt-2 text-xs text-accent-800/80">If this is a different business, carry on — you can still save.</p>
                    </div>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="label">Contact person</label>
                        <input type="text" wire:model="contact_person" class="input" placeholder="Who you deal with" />
                    </div>
                    <div>
                        <label class="label">Phone</label>
                        <input type="text" wire:model.live.debounce.400ms="contact_phone" class="input" placeholder="+6012-345-6789" />
                    </div>
                </div>

                <div>
                    <label class="label">Address</label>
                    <input type="text" wire:model="address" class="input" placeholder="Street, city" />
                </div>

                <div class="grid grid-cols-1 {{ $isAdmin ? 'sm:grid-cols-2' : '' }} gap-4">
                    <div>
                        <label class="label">Category <span class="text-ink-700/40">(optional)</span></label>
                        <input type="text" wire:model="category" class="input" placeholder="e.g. F&B, Retail" list="client-categories" />
                        <datalist id="client-categories">
                            @foreach (['Retail','F&B','Wholesale','Manufacturing','Services','Construction','Logistics','Education','Healthcare','Automotive'] as $cat)
                                <option value="{{ $cat }}"></option>
                            @endforeach
                        </datalist>
                    </div>
                    @if ($isAdmin)
                        <div>
                            <label class="label">Assigned to</label>
                            <select wire:model="assigned_to" class="input">
                                @foreach ($salespeople as $sp)
                                    <option value="{{ $sp->id }}">{{ $sp->name }}</option>
                                @endforeach
                            </select>
                            @error('assigned_to') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                        </div>
                    @endif
                </div>

                <div>
                    <label class="label">Notes <span class="text-ink-700/40">(optional)</span></label>
                    <textarea wire:model="notes" rows="2" class="input" placeholder="Anything worth remembering"></textarea>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-end gap-2">
                <button type="button" wire:click="$set('show', false)" class="btn-ghost">Cancel</button>
                <button type="submit" class="btn-primary" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save">{{ $clientId ? 'Save changes' : 'Add business' }}</span>
                    <span wire:loading wire:target="save">Saving…</span>
                </button>
            </div>
        </form>
    </x-modal-panel>
</div>
