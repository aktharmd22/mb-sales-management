<?php

namespace App\Livewire\Admin\Team;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Role;

#[Title('Team')]
#[Layout('layouts.app')]
class Index extends Component
{
    public bool $showForm = false;

    public ?int $editingId = null;

    // Form fields
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $password = '';
    public bool $is_active = true;

    public function mount(): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160', Rule::unique('users', 'email')->ignore($this->editingId)],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => $this->editingId
                ? ['nullable', 'string', 'min:6']
                : ['required', 'string', 'min:6'],
            'is_active' => ['boolean'],
        ];
    }

    public function create(): void
    {
        $this->reset(['editingId', 'name', 'email', 'phone', 'password']);
        $this->is_active = true;
        $this->resetValidation();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $user = User::where('role', User::ROLE_SALESPERSON)->findOrFail($id);
        $this->editingId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone ?? '';
        $this->password = '';
        $this->is_active = $user->is_active;
        $this->resetValidation();
        $this->showForm = true;
    }

    public function save(): void
    {
        $data = $this->validate();

        $salesRole = Role::firstOrCreate(['name' => User::ROLE_SALESPERSON]);

        if ($this->editingId) {
            $user = User::findOrFail($this->editingId);
            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->phone = $data['phone'] ?: null;
            $user->is_active = $data['is_active'];
            if (! empty($data['password'])) {
                $user->password = Hash::make($data['password']);
            }
            $user->save();
            $message = 'Salesperson updated.';
        } else {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?: null,
                'role' => User::ROLE_SALESPERSON,
                'is_active' => $data['is_active'],
                'email_verified_at' => now(),
                'password' => Hash::make($data['password']),
            ]);
            $message = 'Salesperson added.';
        }

        $user->syncRoles([$salesRole]);

        $this->showForm = false;
        $this->reset(['editingId', 'name', 'email', 'phone', 'password']);
        $this->dispatch('toast', message: $message, type: 'success');
    }

    public function toggleActive(int $id): void
    {
        $user = User::where('role', User::ROLE_SALESPERSON)->findOrFail($id);
        $user->is_active = ! $user->is_active;
        $user->save();

        $this->dispatch('toast',
            message: $user->is_active ? "{$user->name} reactivated." : "{$user->name} deactivated.",
            type: $user->is_active ? 'success' : 'info');
    }

    public function delete(int $id): void
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $user = User::where('role', User::ROLE_SALESPERSON)->findOrFail($id);
        $name = $user->name;

        // Their clients (and those clients' visits/follow-ups/deals), visits,
        // follow-ups, targets and deals cascade via foreign keys.
        $user->delete();

        $this->dispatch('toast', message: "{$name} deleted.", type: 'success');
    }

    public function render()
    {
        $salespeople = User::where('role', User::ROLE_SALESPERSON)
            ->withCount([
                'clients',
                'visits as visits_this_month_count' => fn ($q) => $q
                    ->whereYear('visit_date', now()->year)
                    ->whereMonth('visit_date', now()->month),
                'followUps as open_followups_count' => fn ($q) => $q->where('status', 'pending'),
            ])
            ->orderBy('name')
            ->get();

        return view('livewire.admin.team.index', [
            'salespeople' => $salespeople,
        ]);
    }
}
