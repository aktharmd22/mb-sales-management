<div>
    <x-page-header title="Team" subtitle="Create and manage your salespeople.">
        <x-slot:actions>
            <button wire:click="create" class="btn-primary">
                <x-icon name="user-plus" class="w-4 h-4" /> Add salesperson
            </button>
        </x-slot:actions>
    </x-page-header>

    @if ($salespeople->isEmpty())
        <x-empty-state icon="team" title="No salespeople yet"
            message="Add your first salesperson so they can start logging visits.">
            <x-slot:action>
                <button wire:click="create" class="btn-primary">
                    <x-icon name="user-plus" class="w-4 h-4" /> Add salesperson
                </button>
            </x-slot:action>
        </x-empty-state>
    @else
        {{-- Roster: cards on mobile, table on desktop --}}
        <div class="grid gap-3 sm:hidden">
            @foreach ($salespeople as $person)
                <div class="card p-4 {{ $person->is_active ? '' : 'opacity-60' }}">
                    <div class="flex items-start gap-3">
                        <span class="grid place-items-center h-11 w-11 rounded-full bg-primary/10 text-primary font-semibold shrink-0">
                            {{ \Illuminate\Support\Str::of($person->name)->explode(' ')->map(fn($p) => mb_substr($p,0,1))->take(2)->implode('') }}
                        </span>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="font-semibold text-ink-900 truncate">{{ $person->name }}</p>
                                @unless ($person->is_active)
                                    <span class="badge bg-ink-100 text-ink-700">Inactive</span>
                                @endunless
                            </div>
                            <p class="text-sm text-ink-700/60 truncate">{{ $person->email }}</p>
                            @if ($person->phone)
                                <p class="text-xs text-ink-700/50 mt-0.5">{{ $person->phone }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-2 mt-4 text-center">
                        <div class="rounded-xl bg-canvas py-2">
                            <p class="kpi-number text-lg font-bold text-ink-900">{{ $person->clients_count }}</p>
                            <p class="text-[11px] text-ink-700/60">Clients</p>
                        </div>
                        <div class="rounded-xl bg-canvas py-2">
                            <p class="kpi-number text-lg font-bold text-ink-900">{{ $person->visits_this_month_count }}</p>
                            <p class="text-[11px] text-ink-700/60">Visits / mo</p>
                        </div>
                        <div class="rounded-xl bg-canvas py-2">
                            <p class="kpi-number text-lg font-bold text-ink-900">{{ $person->open_followups_count }}</p>
                            <p class="text-[11px] text-ink-700/60">Open tasks</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 mt-4">
                        <button wire:click="edit({{ $person->id }})" class="btn-ghost flex-1 text-sm">
                            <x-icon name="edit" class="w-4 h-4" /> Edit
                        </button>
                        <button wire:click="toggleActive({{ $person->id }})"
                                class="btn flex-1 text-sm {{ $person->is_active ? 'bg-danger/10 text-danger' : 'bg-primary/10 text-primary' }}">
                            {{ $person->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="hidden sm:block card overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-ink-700/60 border-b border-ink-100">
                        <th class="font-medium px-5 py-3.5">Salesperson</th>
                        <th class="font-medium px-3 py-3.5 text-center">Clients</th>
                        <th class="font-medium px-3 py-3.5 text-center">Visits this month</th>
                        <th class="font-medium px-3 py-3.5 text-center">Open tasks</th>
                        <th class="font-medium px-3 py-3.5 text-center">Status</th>
                        <th class="font-medium px-5 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-100">
                    @foreach ($salespeople as $person)
                        <tr class="hover:bg-canvas/60 transition {{ $person->is_active ? '' : 'opacity-60' }}">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <span class="grid place-items-center h-9 w-9 rounded-full bg-primary/10 text-primary font-semibold text-xs shrink-0">
                                        {{ \Illuminate\Support\Str::of($person->name)->explode(' ')->map(fn($p) => mb_substr($p,0,1))->take(2)->implode('') }}
                                    </span>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-ink-900 truncate">{{ $person->name }}</p>
                                        <p class="text-ink-700/60 truncate">{{ $person->email }}{{ $person->phone ? ' · '.$person->phone : '' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 py-3.5 text-center tnum font-semibold">{{ $person->clients_count }}</td>
                            <td class="px-3 py-3.5 text-center tnum font-semibold">{{ $person->visits_this_month_count }}</td>
                            <td class="px-3 py-3.5 text-center tnum font-semibold">{{ $person->open_followups_count }}</td>
                            <td class="px-3 py-3.5 text-center">
                                @if ($person->is_active)
                                    <span class="badge bg-primary/10 text-primary">Active</span>
                                @else
                                    <span class="badge bg-ink-100 text-ink-700">Inactive</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button wire:click="edit({{ $person->id }})"
                                            class="grid place-items-center h-9 w-9 rounded-lg text-ink-700 hover:bg-ink-50" title="Edit">
                                        <x-icon name="edit" class="w-4 h-4" />
                                    </button>
                                    <button wire:click="toggleActive({{ $person->id }})"
                                            class="text-xs font-semibold px-3 py-2 rounded-lg {{ $person->is_active ? 'text-danger hover:bg-danger/10' : 'text-primary hover:bg-primary/10' }}">
                                        {{ $person->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- ===================== Add / Edit modal ===================== --}}
    <x-modal-panel :show="'showForm'">
        <form wire:submit="save" class="p-6">
            <h2 class="text-lg font-bold text-ink-900">
                {{ $editingId ? 'Edit salesperson' : 'Add salesperson' }}
            </h2>
            <p class="text-sm text-ink-700/60 mt-1">
                {{ $editingId ? 'Update their details or reset the password.' : 'They can log in immediately with the password you set.' }}
            </p>

            <div class="mt-5 space-y-4">
                <div>
                    <label class="label">Full name</label>
                    <input type="text" wire:model="name" class="input" placeholder="e.g. Daniel Tan" />
                    @error('name') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label">Email</label>
                    <input type="email" wire:model="email" class="input" placeholder="name@malayznbeat.com" />
                    @error('email') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label">Phone <span class="text-ink-700/40">(optional)</span></label>
                    <input type="text" wire:model="phone" class="input" placeholder="+6012-345-6789" />
                    @error('phone') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label">{{ $editingId ? 'New password (leave blank to keep current)' : 'Temporary password' }}</label>
                    <input type="text" wire:model="password" class="input" placeholder="min. 6 characters" />
                    @error('password') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>
                <label class="flex items-center gap-2.5 cursor-pointer">
                    <input type="checkbox" wire:model="is_active" class="rounded border-ink-100 text-primary focus:ring-primary" />
                    <span class="text-sm text-ink-700">Account is active</span>
                </label>
            </div>

            <div class="mt-6 flex items-center justify-end gap-2">
                <button type="button" wire:click="$set('showForm', false)" class="btn-ghost">Cancel</button>
                <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="save">{{ $editingId ? 'Save changes' : 'Add salesperson' }}</span>
                    <span wire:loading wire:target="save">Saving…</span>
                </button>
            </div>
        </form>
    </x-modal-panel>
</div>
