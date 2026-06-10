<?php

namespace Tests\Feature;

use App\Livewire\Admin\Team\Index as TeamIndex;
use App\Models\Client;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TeamTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => User::ROLE_ADMIN]);
        Role::firstOrCreate(['name' => User::ROLE_SALESPERSON]);
    }

    public function test_admin_can_delete_a_salesperson_and_their_data_cascades(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN])->assignRole('admin');
        $sp = User::factory()->create(['role' => User::ROLE_SALESPERSON]);
        $client = Client::factory()->create(['assigned_to' => $sp->id]);
        $visit = Visit::factory()->create(['client_id' => $client->id, 'user_id' => $sp->id]);

        Livewire::actingAs($admin)
            ->test(TeamIndex::class)
            ->call('delete', $sp->id)
            ->assertDispatched('toast');

        $this->assertDatabaseMissing('users', ['id' => $sp->id]);
        $this->assertDatabaseMissing('clients', ['id' => $client->id]); // cascade
        $this->assertDatabaseMissing('visits', ['id' => $visit->id]);   // cascade
    }

    public function test_salesperson_cannot_open_team(): void
    {
        $sales = User::factory()->create(['role' => User::ROLE_SALESPERSON]);

        Livewire::actingAs($sales)
            ->test(TeamIndex::class)
            ->assertForbidden();
    }
}
