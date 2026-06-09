<?php

namespace App\Livewire\FollowUps;

use App\Models\Client;
use App\Models\FollowUp;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Title('Follow-ups')]
#[Layout('layouts.app')]
class Index extends Component
{
    #[Url(history: true)]
    public string $assigned = '';

    public bool $showDone = false;

    // Add follow-up modal
    public bool $adding = false;
    public ?int $clientId = null;
    public string $clientSearch = '';
    public string $dueDate = '';
    public string $note = '';

    public function mount(): void
    {
        $this->dueDate = today()->addWeek()->toDateString();
    }

    public function getClientOptionsProperty()
    {
        return Client::query()
            ->forUser(auth()->user())
            ->when($this->clientSearch, fn ($q) => $q->where('business_name', 'like', '%' . $this->clientSearch . '%'))
            ->orderBy('business_name')
            ->limit(8)
            ->get(['id', 'business_name', 'assigned_to']);
    }

    public function complete(int $id): void
    {
        $fu = $this->scoped()->findOrFail($id);
        $fu->update(['status' => 'done', 'completed_at' => now()]);
        $this->dispatch('toast', message: 'Follow-up done. Nice.', type: 'success');
    }

    public function reopen(int $id): void
    {
        $fu = $this->scoped()->findOrFail($id);
        $fu->update(['status' => 'pending', 'completed_at' => null]);
    }

    public function startAdd(): void
    {
        $this->reset(['clientId', 'clientSearch', 'note']);
        $this->dueDate = today()->addWeek()->toDateString();
        $this->resetValidation();
        $this->adding = true;
    }

    public function pickClient(int $id): void
    {
        $this->clientId = $id;
        $this->clientSearch = Client::forUser(auth()->user())->find($id)?->business_name ?? '';
    }

    public function saveFollowUp(): void
    {
        $this->validate([
            'clientId' => ['required', 'integer', 'exists:clients,id'],
            'dueDate' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $client = Client::forUser(auth()->user())->findOrFail($this->clientId);

        FollowUp::create([
            'client_id' => $client->id,
            'user_id' => $client->assigned_to,
            'due_date' => $this->dueDate,
            'note' => $this->note ?: 'Follow up',
            'status' => 'pending',
        ]);

        $this->adding = false;
        $this->dispatch('toast', message: 'Follow-up scheduled.', type: 'success');
    }

    private function scoped()
    {
        $user = auth()->user();

        return FollowUp::query()
            ->when(! $user->isAdmin(), fn ($q) => $q->where('user_id', $user->id))
            ->when($user->isAdmin() && $this->assigned, fn ($q) => $q->where('user_id', $this->assigned));
    }

    public function render()
    {
        $base = fn () => $this->scoped()->with(['client:id,business_name,pipeline_stage', 'salesperson:id,name']);

        $overdue = $base()->where('status', 'pending')->whereDate('due_date', '<', today())->orderBy('due_date')->get();
        $today = $base()->where('status', 'pending')->whereDate('due_date', today())->orderBy('due_date')->get();
        $upcoming = $base()->where('status', 'pending')->whereDate('due_date', '>', today())->orderBy('due_date')->get();
        $done = $this->showDone ? $base()->where('status', 'done')->latest('completed_at')->limit(30)->get() : collect();

        return view('livewire.follow-ups.index', [
            'overdue' => $overdue,
            'dueToday' => $today,
            'upcoming' => $upcoming,
            'done' => $done,
            'isAdmin' => auth()->user()->isAdmin(),
            'salespeople' => auth()->user()->isAdmin()
                ? User::where('role', User::ROLE_SALESPERSON)->orderBy('name')->get(['id', 'name'])
                : collect(),
            'isEmpty' => $overdue->isEmpty() && $today->isEmpty() && $upcoming->isEmpty(),
        ]);
    }
}
