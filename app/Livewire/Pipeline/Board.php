<?php

namespace App\Livewire\Pipeline;

use App\Models\Client;
use App\Models\User;
use App\Support\ChartFactory;
use App\Support\Metrics;
use App\Support\Pipeline;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Title('Pipeline')]
#[Layout('layouts.app')]
class Board extends Component
{
    use AuthorizesRequests;

    #[Url(history: true)]
    public string $view = 'kanban'; // kanban | funnel

    #[Url(history: true)]
    public string $focus = '';

    /** Drag a client to a new stage. */
    public function moveClient(int $clientId, string $stage): void
    {
        if (! array_key_exists($stage, Pipeline::STAGES)) {
            return;
        }

        $client = Client::findOrFail($clientId);
        $this->authorize('update', $client);

        $client->update(['pipeline_stage' => $stage]);
        $this->dispatch('toast', message: "{$client->business_name} → " . Pipeline::label($stage), type: 'success');
    }

    public function render()
    {
        $user = auth()->user();
        $isAdmin = $user->isAdmin();
        $userId = $isAdmin ? ($this->focus ? (int) $this->focus : null) : $user->id;

        $clients = Client::query()
            ->when($userId, fn ($q) => $q->where('assigned_to', $userId))
            ->whereIn('pipeline_stage', array_keys(Pipeline::STAGES))
            ->with('salesperson:id,name')
            ->withSum('visits as revenue_potential_sum', 'revenue_potential')
            ->orderByRaw('last_visit_at IS NULL, last_visit_at DESC')
            ->get();

        $columns = [];
        foreach (Pipeline::STAGES as $key => $label) {
            $columns[$key] = [
                'label' => $label,
                'color' => Pipeline::color($key),
                'hex' => Pipeline::hex($key),
                'clients' => $clients->where('pipeline_stage', $key)->values(),
            ];
        }

        $metrics = new Metrics('yearly', $userId);
        $funnel = $metrics->funnel();

        return view('livewire.pipeline.board', [
            'isAdmin' => $isAdmin,
            'columns' => $columns,
            'funnel' => $funnel,
            'funnelChart' => ChartFactory::funnelBar($funnel['labels'], $funnel['data'], $funnel['colors'], 320),
            'salespeople' => $isAdmin ? User::where('role', User::ROLE_SALESPERSON)->orderBy('name')->get(['id', 'name']) : collect(),
            'chartKeySuffix' => $userId ?? 'team',
        ]);
    }
}
