<?php

namespace App\Livewire\Visits;

use App\Models\Client;
use App\Models\FollowUp;
use App\Models\Visit;
use App\Support\Pipeline;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Title('Log Visit')]
#[Layout('layouts.app')]
class LogVisit extends Component
{
    use AuthorizesRequests, WithFileUploads;

    /** Pre-selected client via ?client=ID */
    #[Url(as: 'client')]
    public ?int $client = null;

    public ?int $clientId = null;
    public string $clientLabel = '';

    // Client picker
    public string $clientSearch = '';
    public bool $picking = false;

    // Quick-add new business
    public bool $quickAdd = false;
    public string $newBusinessName = '';
    public string $newPhone = '';

    // Visit fields
    public string $visit_date;
    public string $visit_level = 'cold';
    public string $person_met = '';
    public string $contact_phone = '';
    public bool $decision_maker_met = false;
    public bool $interested = false;
    public bool $follow_up_done = false;
    public ?string $revenue_potential = null;
    public string $notes = '';
    public $photo = null;

    // Optional follow-up scheduling
    public bool $scheduleFollowUp = false;
    public string $followUpDate = '';
    public string $followUpNote = '';

    public function mount(): void
    {
        $this->visit_date = today()->toDateString();
        $this->followUpDate = today()->addWeek()->toDateString();

        if ($this->client) {
            $client = Client::forUser(auth()->user())->find($this->client);
            if ($client) {
                $this->selectClient($client->id);
            }
        }
    }

    public function getMatchingClientsProperty()
    {
        return Client::query()
            ->forUser(auth()->user())
            ->when($this->clientSearch, fn ($q) => $q->where('business_name', 'like', '%' . $this->clientSearch . '%'))
            ->orderByRaw('last_visit_at IS NULL, last_visit_at DESC')
            ->limit(8)
            ->get(['id', 'business_name', 'contact_person', 'contact_phone', 'pipeline_stage']);
    }

    public function selectClient(int $id): void
    {
        $client = Client::forUser(auth()->user())->findOrFail($id);

        $this->clientId = $client->id;
        $this->clientLabel = $client->business_name;
        $this->person_met = $client->contact_person ?? '';
        $this->contact_phone = $client->contact_phone ?? '';
        // Default the stage to the next sensible step (their current stage).
        $this->visit_level = Pipeline::isTerminal($client->pipeline_stage) ? 'opportunity' : $client->pipeline_stage;
        $this->picking = false;
        $this->quickAdd = false;
        $this->clientSearch = '';
    }

    public function clearClient(): void
    {
        $this->reset(['clientId', 'clientLabel', 'person_met', 'contact_phone']);
        $this->visit_level = 'cold';
    }

    public function createQuickClient(): void
    {
        $this->validate([
            'newBusinessName' => ['required', 'string', 'max:160'],
            'newPhone' => ['nullable', 'string', 'max:40'],
        ]);

        $client = Client::create([
            'business_name' => $this->newBusinessName,
            'contact_phone' => $this->newPhone ?: null,
            'assigned_to' => auth()->id(),
            'created_by' => auth()->id(),
            'pipeline_stage' => 'cold',
            'status' => 'active',
        ]);

        $this->reset(['newBusinessName', 'newPhone']);
        $this->selectClient($client->id);
        $this->dispatch('toast', message: 'Business added.', type: 'success');
    }

    protected function rules(): array
    {
        return [
            'clientId' => ['required', 'integer', 'exists:clients,id'],
            'visit_date' => ['required', 'date', 'before_or_equal:today'],
            'visit_level' => ['required', 'string', 'in:' . implode(',', array_keys(Pipeline::all()))],
            'person_met' => ['nullable', 'string', 'max:120'],
            'contact_phone' => ['nullable', 'string', 'max:40'],
            'revenue_potential' => ['nullable', 'numeric', 'min:0', 'max:100000000'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'photo' => ['nullable', 'image', 'max:5120'],
            'followUpDate' => ['required_if:scheduleFollowUp,true', 'nullable', 'date', 'after_or_equal:today'],
            'followUpNote' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function save()
    {
        $data = $this->validate();

        // Owning salesperson is whoever owns the client (admins may log on behalf).
        $client = Client::findOrFail($this->clientId);
        $this->authorize('view', $client);

        $photoPath = $this->photo
            ? $this->photo->store('visits', 'public')
            : null;

        $visit = Visit::create([
            'client_id' => $client->id,
            'user_id' => $client->assigned_to,
            'visit_date' => $this->visit_date,
            'person_met' => $this->person_met ?: null,
            'contact_phone' => $this->contact_phone ?: null,
            'visit_level' => $this->visit_level,
            'decision_maker_met' => $this->decision_maker_met,
            'interested' => $this->interested,
            'follow_up_done' => $this->follow_up_done,
            'revenue_potential' => $this->revenue_potential ?: 0,
            'notes' => $this->notes ?: null,
            'photo_path' => $photoPath,
        ]);

        // Keep the client's pipeline stage / dormant flag in sync.
        $client->refreshPipeline();

        if ($this->scheduleFollowUp && $this->followUpDate) {
            FollowUp::create([
                'client_id' => $client->id,
                'visit_id' => $visit->id,
                'user_id' => $client->assigned_to,
                'due_date' => $this->followUpDate,
                'note' => $this->followUpNote ?: 'Follow up after visit',
                'status' => 'pending',
            ]);
        }

        session()->flash('flash', 'Visit logged.');

        return $this->redirectRoute('clients.show', $client, navigate: true);
    }

    public function render()
    {
        return view('livewire.visits.log-visit', [
            'stages' => Pipeline::all(),
        ]);
    }
}
