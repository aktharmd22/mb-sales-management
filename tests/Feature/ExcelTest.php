<?php

namespace Tests\Feature;

use App\Imports\VisitsImport;
use App\Livewire\Reports\Index as ReportsIndex;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ExcelTest extends TestCase
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

    public function test_visits_import_creates_clients_and_visits_from_spreadsheet_rows(): void
    {
        $import = new VisitsImport($this->sales->id, $this->sales->id);

        $rows = new Collection([
            [
                'date' => '2026-06-01', 'business' => 'Sunrise Mart', 'person_met' => 'Mr Lim',
                'client_phone' => '+60123456789', 'visit_level' => 'Warm Visit',
                'decision_maker_met' => 'No', 'interested' => 'Yes', 'follow_up_done' => 'No',
                'revenue_potential_rm' => '5000', 'notes' => 'Keen',
            ],
            [
                'date' => '2026-06-05', 'business' => 'Sunrise Mart', 'person_met' => 'Mr Lim',
                'client_phone' => '+60123456789', 'visit_level' => 'Qualified Meeting',
                'decision_maker_met' => 'Yes', 'interested' => 'Yes', 'follow_up_done' => 'Yes',
                'revenue_potential_rm' => '8000', 'notes' => '',
            ],
        ]);

        $import->collection($rows);

        $this->assertSame(1, $import->clientsCreated); // same business reused
        $this->assertSame(2, $import->visitsCreated);

        $this->assertDatabaseHas('clients', [
            'business_name' => 'Sunrise Mart',
            'assigned_to' => $this->sales->id,
            'pipeline_stage' => 'qualified', // highest reached
        ]);
        $this->assertDatabaseCount('visits', 2);
    }

    public function test_report_export_downloads_a_spreadsheet(): void
    {
        Excel::fake();

        Livewire::actingAs($this->sales)
            ->test(ReportsIndex::class)
            ->call('export');

        Excel::assertDownloaded('malayznbeat-report-monthly-' . now()->format('Ymd') . '.xlsx');
    }
}
