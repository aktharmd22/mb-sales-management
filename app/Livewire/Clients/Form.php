<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Reusable create/edit client modal. Open it from anywhere with:
 *   $dispatch('open-client-form')                  // create
 *   $dispatch('open-client-form', { clientId: 5 }) // edit
 * Emits `client-saved` (with the id) when done.
 */
class Form extends Component
{
    use AuthorizesRequests;

    public bool $show = false;

    public ?int $clientId = null;

    public string $business_name = '';
    public string $contact_person = '';
    public string $contact_phone = '';
    public string $address = '';
    public string $category = '';
    public string $notes = '';
    public ?int $assigned_to = null;

    public bool $ignoreDuplicates = false;

    public function rules(): array
    {
        return [
            'business_name' => ['required', 'string', 'max:160'],
            'contact_person' => ['nullable', 'string', 'max:120'],
            'contact_phone' => ['nullable', 'string', 'max:40'],
            'address' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:80'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'assigned_to' => ['required', 'integer', 'exists:users,id'],
        ];
    }

    #[On('open-client-form')]
    public function open(?int $clientId = null): void
    {
        $this->resetValidation();
        $this->reset(['business_name', 'contact_person', 'contact_phone', 'address', 'category', 'notes', 'ignoreDuplicates']);

        $user = auth()->user();

        if ($clientId) {
            $client = Client::findOrFail($clientId);
            $this->authorize('update', $client);

            $this->clientId = $client->id;
            $this->business_name = $client->business_name;
            $this->contact_person = $client->contact_person ?? '';
            $this->contact_phone = $client->contact_phone ?? '';
            $this->address = $client->address ?? '';
            $this->category = $client->category ?? '';
            $this->notes = $client->notes ?? '';
            $this->assigned_to = $client->assigned_to;
        } else {
            $this->clientId = null;
            // Salespeople own what they create; admins default to themselves but can reassign.
            $this->assigned_to = $user->id;
        }

        $this->show = true;
    }

    /** Live duplicate detection as the user types a business name / phone. */
    public function getDuplicatesProperty()
    {
        $name = trim($this->business_name);
        $phone = preg_replace('/\D+/', '', $this->contact_phone);

        if (Str::length($name) < 3 && Str::length($phone) < 5) {
            return collect();
        }

        return Client::query()
            ->forUser(auth()->user())
            ->when($this->clientId, fn ($q) => $q->whereKeyNot($this->clientId))
            ->where(function ($q) use ($name, $phone) {
                if (Str::length($name) >= 3) {
                    $q->orWhere('business_name', 'like', '%' . $name . '%');
                }
                if (Str::length($phone) >= 5) {
                    $q->orWhereRaw("REPLACE(REPLACE(REPLACE(contact_phone,' ',''),'-',''),'+','') like ?", ['%' . $phone . '%']);
                }
            })
            ->limit(4)
            ->get();
    }

    public function save(): void
    {
        $user = auth()->user();

        // Salespeople can only assign clients to themselves — force it before validating.
        if (! $user->isAdmin()) {
            $this->assigned_to = $user->id;
        }

        $data = $this->validate();

        if ($this->clientId) {
            $client = Client::findOrFail($this->clientId);
            $this->authorize('update', $client);
            $client->update($data);
            $message = 'Client updated.';
        } else {
            $this->authorize('create', Client::class);
            $client = Client::create($data + [
                'created_by' => $user->id,
                'pipeline_stage' => 'cold',
                'status' => 'active',
            ]);
            $message = 'Client added.';
        }

        $this->show = false;
        $this->dispatch('toast', message: $message, type: 'success');
        $this->dispatch('client-saved', clientId: $client->id);
    }

    public function render()
    {
        $salespeople = auth()->user()->isAdmin()
            ? User::where('role', User::ROLE_SALESPERSON)->where('is_active', true)->orderBy('name')->get(['id', 'name'])
            : collect();

        return view('livewire.clients.form', [
            'salespeople' => $salespeople,
            'isAdmin' => auth()->user()->isAdmin(),
        ]);
    }
}
