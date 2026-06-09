<?php

namespace App\Livewire\Clients;

use App\Exports\ClientsExport;
use App\Models\Client;
use App\Models\User;
use App\Support\Pipeline;
use Maatwebsite\Excel\Facades\Excel;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Clients')]
#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'search', history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $stage = '';

    #[Url(history: true)]
    public string $status = '';

    #[Url(history: true)]
    public string $assigned = '';

    #[Url(history: true)]
    public string $sort = 'recent';

    public function updating($name): void
    {
        if (in_array($name, ['search', 'stage', 'status', 'assigned', 'sort'])) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'stage', 'status', 'assigned']);
        $this->sort = 'recent';
        $this->resetPage();
    }

    public function newClient(): void
    {
        $this->dispatch('open-client-form');
    }

    public function export()
    {
        return Excel::download(
            new ClientsExport(auth()->user()),
            'malayznbeat-clients-' . now()->format('Ymd') . '.xlsx'
        );
    }

    #[On('client-saved')]
    public function onClientSaved(): void
    {
        // Re-render with fresh data.
    }

    public function render()
    {
        $user = auth()->user();

        $clients = Client::query()
            ->forUser($user)
            ->with('salesperson:id,name')
            ->withCount('visits')
            ->withSum('visits as revenue_potential_sum', 'revenue_potential')
            ->when($this->search, function ($q) {
                $term = '%' . $this->search . '%';
                $q->where(fn ($w) => $w
                    ->where('business_name', 'like', $term)
                    ->orWhere('contact_person', 'like', $term)
                    ->orWhere('contact_phone', 'like', $term)
                    ->orWhere('address', 'like', $term));
            })
            ->when($this->stage, fn ($q) => $q->where('pipeline_stage', $this->stage))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($user->isAdmin() && $this->assigned, fn ($q) => $q->where('assigned_to', $this->assigned))
            ->when($this->sort === 'recent', fn ($q) => $q->orderByRaw('last_visit_at IS NULL, last_visit_at DESC'))
            ->when($this->sort === 'name', fn ($q) => $q->orderBy('business_name'))
            ->when($this->sort === 'revenue', fn ($q) => $q->orderByDesc('revenue_potential_sum'))
            ->when($this->sort === 'stage', fn ($q) => $q->orderByDesc('pipeline_stage'))
            ->paginate(12);

        return view('livewire.clients.index', [
            'clients' => $clients,
            'stages' => Pipeline::all(),
            'salespeople' => $user->isAdmin()
                ? User::where('role', User::ROLE_SALESPERSON)->orderBy('name')->get(['id', 'name'])
                : collect(),
            'isAdmin' => $user->isAdmin(),
            'hasFilters' => $this->search || $this->stage || $this->status || $this->assigned,
        ]);
    }
}
