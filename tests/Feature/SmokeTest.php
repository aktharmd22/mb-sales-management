<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function admin(): User
    {
        return User::where('role', 'admin')->firstOrFail();
    }

    private function salesperson(): User
    {
        return User::where('role', 'salesperson')->where('is_active', true)->firstOrFail();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_salesperson_pages_load(): void
    {
        $routes = ['dashboard', 'clients.index', 'visits.create', 'followups.index', 'pipeline', 'reports', 'portfolio.index', 'about.index', 'profile'];
        foreach ($routes as $name) {
            $this->actingAs($this->salesperson())
                ->get(route($name))
                ->assertOk();
        }
    }

    public function test_admin_pages_load(): void
    {
        $routes = ['dashboard', 'clients.index', 'followups.index', 'pipeline', 'reports', 'portfolio.index', 'about.index', 'team.index', 'targets.index', 'clients.import'];
        foreach ($routes as $name) {
            $this->actingAs($this->admin())
                ->get(route($name))
                ->assertOk();
        }
    }

    public function test_salesperson_cannot_access_admin_team(): void
    {
        $this->actingAs($this->salesperson())
            ->get(route('team.index'))
            ->assertForbidden();
    }
}
