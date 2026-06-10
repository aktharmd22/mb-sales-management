<?php

namespace Tests\Feature;

use App\Livewire\Clients\Form as ClientForm;
use App\Livewire\Clients\Index as ClientsIndex;
use App\Livewire\Clients\Show as ClientShow;
use App\Livewire\Visits\LogVisit;
use App\Models\Client;
use App\Models\FollowUp;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ClientVisitFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $sales;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => User::ROLE_SALESPERSON]);
        Role::firstOrCreate(['name' => User::ROLE_ADMIN]);
        $this->sales = User::factory()->create(['role' => User::ROLE_SALESPERSON]);
    }

    public function test_logging_a_visit_advances_the_pipeline_and_sets_last_visit(): void
    {
        $client = Client::factory()->create([
            'assigned_to' => $this->sales->id,
            'pipeline_stage' => 'cold',
        ]);

        Livewire::actingAs($this->sales)
            ->test(LogVisit::class, ['client' => $client->id])
            ->assertSet('clientId', $client->id)
            ->set('visit_level', 'qualified')
            ->set('interested', true)
            ->set('revenue_potential', 5000)
            ->call('save')
            ->assertRedirect(route('clients.show', $client));

        $client->refresh();
        $this->assertSame('qualified', $client->pipeline_stage);
        $this->assertNotNull($client->last_visit_at);
        $this->assertDatabaseHas('visits', [
            'client_id' => $client->id,
            'visit_level' => 'qualified',
            'revenue_potential' => 5000,
        ]);
    }

    public function test_logging_a_visit_can_schedule_a_follow_up(): void
    {
        $client = Client::factory()->create(['assigned_to' => $this->sales->id]);

        Livewire::actingAs($this->sales)
            ->test(LogVisit::class, ['client' => $client->id])
            ->set('visit_level', 'warm')
            ->set('scheduleFollowUp', true)
            ->set('followUpDate', today()->addDays(3)->toDateString())
            ->set('followUpNote', 'Send quote')
            ->call('save');

        $this->assertDatabaseHas('follow_ups', [
            'client_id' => $client->id,
            'note' => 'Send quote',
            'status' => 'pending',
        ]);
    }

    public function test_duplicate_detection_flags_similar_business(): void
    {
        Client::factory()->create([
            'assigned_to' => $this->sales->id,
            'business_name' => 'Sunrise Mart Sdn Bhd',
        ]);

        Livewire::actingAs($this->sales)
            ->test(ClientForm::class)
            ->call('open')
            ->set('business_name', 'Sunrise Mart')
            ->assertSeeHtml('Possible duplicate');
    }

    public function test_creating_a_client_assigns_to_self_for_salesperson(): void
    {
        Livewire::actingAs($this->sales)
            ->test(ClientForm::class)
            ->call('open')
            ->set('business_name', 'Brand New Biz')
            ->set('assigned_to', 999999) // should be ignored / forced to self
            ->call('save')
            ->assertDispatched('client-saved');

        $this->assertDatabaseHas('clients', [
            'business_name' => 'Brand New Biz',
            'assigned_to' => $this->sales->id,
        ]);
    }

    public function test_owner_can_delete_client_and_its_visits_cascade(): void
    {
        $client = Client::factory()->create(['assigned_to' => $this->sales->id]);
        $visit = Visit::factory()->create(['client_id' => $client->id, 'user_id' => $this->sales->id]);

        Livewire::actingAs($this->sales)
            ->test(ClientsIndex::class)
            ->call('delete', $client->id)
            ->assertDispatched('toast');

        $this->assertDatabaseMissing('clients', ['id' => $client->id]);
        $this->assertDatabaseMissing('visits', ['id' => $visit->id]); // FK cascade
    }

    public function test_salesperson_cannot_delete_another_salespersons_client(): void
    {
        $other = User::factory()->create(['role' => User::ROLE_SALESPERSON]);
        $client = Client::factory()->create(['assigned_to' => $other->id]);

        Livewire::actingAs($this->sales)
            ->test(ClientsIndex::class)
            ->call('delete', $client->id)
            ->assertForbidden();

        $this->assertDatabaseHas('clients', ['id' => $client->id]);
    }

    public function test_salesperson_cannot_view_another_salespersons_client(): void
    {
        $other = User::factory()->create(['role' => User::ROLE_SALESPERSON]);
        $clientOfOther = Client::factory()->create(['assigned_to' => $other->id]);

        Livewire::actingAs($this->sales)
            ->test(ClientShow::class, ['client' => $clientOfOther])
            ->assertForbidden();
    }
}
