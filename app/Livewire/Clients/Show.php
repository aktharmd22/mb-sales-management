<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use App\Models\Deal;
use App\Models\FollowUp;
use App\Support\Pipeline;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('layouts.app')]
class Show extends Component
{
    use AuthorizesRequests;

    public Client $client;

    // Quick follow-up
    public bool $addingFollowUp = false;
    public string $fuDate = '';
    public string $fuNote = '';

    // Close deal modal
    public bool $closingDeal = false;
    public string $dealOutcome = 'won';
    public ?string $dealRevenue = null;
    public string $dealNotes = '';

    public function mount(Client $client): void
    {
        $this->authorize('view', $client);
        $this->client = $client;
        $this->fuDate = today()->addWeek()->toDateString();
        $this->dealDate = today()->toDateString();
    }

    public string $dealDate = '';

    /* ---------------- Follow-ups ---------------- */

    public function startFollowUp(): void
    {
        $this->addingFollowUp = true;
        $this->fuDate = today()->addWeek()->toDateString();
        $this->fuNote = '';
        $this->resetValidation();
    }

    public function saveFollowUp(): void
    {
        $this->validate([
            'fuDate' => ['required', 'date'],
            'fuNote' => ['nullable', 'string', 'max:500'],
        ]);

        FollowUp::create([
            'client_id' => $this->client->id,
            'user_id' => $this->client->assigned_to,
            'due_date' => $this->fuDate,
            'note' => $this->fuNote ?: 'Follow up',
            'status' => 'pending',
        ]);

        $this->addingFollowUp = false;
        $this->reset('fuNote');
        $this->dispatch('toast', message: 'Follow-up scheduled.', type: 'success');
    }

    public function completeFollowUp(int $id): void
    {
        $fu = $this->client->followUps()->findOrFail($id);
        $fu->update(['status' => 'done', 'completed_at' => now()]);
        $this->dispatch('toast', message: 'Follow-up done. Nice.', type: 'success');
    }

    public function reopenFollowUp(int $id): void
    {
        $fu = $this->client->followUps()->findOrFail($id);
        $fu->update(['status' => 'pending', 'completed_at' => null]);
    }

    /* ---------------- Deals ---------------- */

    public function startDeal(string $outcome = 'won'): void
    {
        $this->dealOutcome = $outcome;
        $this->dealRevenue = null;
        $this->dealNotes = '';
        $this->dealDate = today()->toDateString();
        $this->resetValidation();
        $this->closingDeal = true;
    }

    public function saveDeal(): void
    {
        $this->validate([
            'dealOutcome' => ['required', 'in:won,lost'],
            'dealRevenue' => ['nullable', 'numeric', 'min:0'],
            'dealDate' => ['required', 'date'],
            'dealNotes' => ['nullable', 'string', 'max:500'],
        ]);

        Deal::create([
            'client_id' => $this->client->id,
            'user_id' => $this->client->assigned_to,
            'outcome' => $this->dealOutcome,
            'actual_revenue' => $this->dealOutcome === 'won' ? ($this->dealRevenue ?: 0) : 0,
            'notes' => $this->dealNotes ?: null,
            'closed_at' => $this->dealDate,
        ]);

        // Closing a deal sets the client's terminal stage.
        $this->client->update(['pipeline_stage' => $this->dealOutcome]);
        $this->client->refresh();

        $this->closingDeal = false;
        $this->dispatch('toast',
            message: $this->dealOutcome === 'won' ? 'Deal won! 🎉' : 'Marked as lost.',
            type: $this->dealOutcome === 'won' ? 'success' : 'info');
    }

    public function editClient(): void
    {
        $this->dispatch('open-client-form', clientId: $this->client->id);
    }

    public function deleteClient()
    {
        $this->authorize('delete', $this->client);
        $name = $this->client->business_name;
        $this->client->delete();

        session()->flash('flash', "“{$name}” deleted.");

        return $this->redirectRoute('clients.index', navigate: true);
    }

    #[On('client-saved')]
    public function refreshClient(): void
    {
        $this->client->refresh();
    }

    public function render()
    {
        $client = $this->client->loadCount('visits')
            ->load(['salesperson:id,name', 'visits.salesperson:id,name', 'deals' => fn ($q) => $q->latest('closed_at')]);

        $visits = $client->visits()->with('salesperson:id,name')->orderByDesc('visit_date')->orderByDesc('id')->get();

        $followUps = $client->followUps()->orderByRaw("status = 'done'")->orderBy('due_date')->get();

        $stats = [
            'visits' => $visits->count(),
            'revenue_potential' => (float) $visits->sum('revenue_potential'),
            'decision_makers' => $visits->where('decision_maker_met', true)->count(),
            'interested' => $visits->where('interested', true)->count(),
            'won_revenue' => (float) $client->deals->where('outcome', 'won')->sum('actual_revenue'),
        ];

        return view('livewire.clients.show', [
            'client' => $client,
            'visits' => $visits,
            'followUps' => $followUps,
            'stats' => $stats,
            'stageKeys' => array_keys(Pipeline::STAGES),
            'isTerminal' => Pipeline::isTerminal($client->pipeline_stage),
            'isAdmin' => auth()->user()->isAdmin(),
        ])->title($client->business_name . ' · Malayznbeat');
    }
}
