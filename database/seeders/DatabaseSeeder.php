<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Deal;
use App\Models\FollowUp;
use App\Models\Target;
use App\Models\User;
use App\Models\Visit;
use App\Support\Pipeline;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles -------------------------------------------------------
        $adminRole = Role::firstOrCreate(['name' => User::ROLE_ADMIN]);
        $salesRole = Role::firstOrCreate(['name' => User::ROLE_SALESPERSON]);

        // 2. Admin -------------------------------------------------------
        $admin = User::updateOrCreate(
            ['email' => 'admin@malayznbeat.com'],
            [
                'name' => 'Aisha Rahman',
                'phone' => '+6012-345-6789',
                'role' => User::ROLE_ADMIN,
                'is_active' => true,
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );
        $admin->syncRoles([$adminRole]);

        // 3. Salespeople -------------------------------------------------
        $people = [
            ['name' => 'Daniel Tan',    'email' => 'daniel@malayznbeat.com'],
            ['name' => 'Nurul Huda',    'email' => 'nurul@malayznbeat.com'],
            ['name' => 'Arjun Kumar',   'email' => 'arjun@malayznbeat.com'],
            ['name' => 'Siti Aminah',   'email' => 'siti@malayznbeat.com'],
        ];

        $salespeople = collect($people)->map(function ($p) use ($salesRole) {
            $user = User::updateOrCreate(
                ['email' => $p['email']],
                [
                    'name' => $p['name'],
                    'phone' => '+6013-' . fake()->numerify('###-####'),
                    'role' => User::ROLE_SALESPERSON,
                    'is_active' => true,
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                ]
            );
            $user->syncRoles([$salesRole]);

            return $user;
        });

        // 4. Targets — last 3 months + current month per salesperson ----
        $periods = collect(range(0, 3))
            ->map(fn ($i) => now()->copy()->subMonths($i)->format('Y-m'));

        foreach ($salespeople as $person) {
            foreach ($periods as $period) {
                Target::updateOrCreate(
                    ['user_id' => $person->id, 'period' => $period],
                    [
                        'visits_target' => fake()->randomElement([30, 40, 45, 50]),
                        'revenue_target' => fake()->randomElement([60000, 80000, 100000, 120000]),
                    ]
                );
            }
        }

        // 5. Clients + visits + follow-ups + deals ----------------------
        $stageKeys = array_keys(Pipeline::STAGES); // cold..proposal
        $clientCount = 40;

        for ($i = 0; $i < $clientCount; $i++) {
            $owner = $salespeople->random();

            // How far along the pipeline has this relationship progressed?
            // 0 = only cold, up to 4 = reached proposal. A few will close.
            $depth = fake()->randomElement([0, 1, 1, 2, 2, 3, 3, 4, 4]);
            $reachedStage = $stageKeys[$depth];

            // Spread the first visit across the past ~4 months.
            $firstVisit = Carbon::now()->subDays(fake()->numberBetween(20, 120));

            $client = Client::factory()->create([
                'assigned_to' => $owner->id,
                'created_by' => $owner->id,
                'created_at' => $firstVisit,
                'updated_at' => $firstVisit,
            ]);

            // Build one visit per stage reached, marching forward in time.
            $visitDate = $firstVisit->copy();
            $lastVisitDate = $visitDate->copy();

            for ($s = 0; $s <= $depth; $s++) {
                $level = $stageKeys[$s];
                $isLater = $s >= 2;
                $interested = $isLater ? fake()->boolean(80) : fake()->boolean(45);
                $revenue = $interested
                    ? fake()->randomElement([2000, 4000, 6000, 9000, 15000, 25000, 40000])
                    : 0;

                Visit::create([
                    'client_id' => $client->id,
                    'user_id' => $owner->id,
                    'visit_date' => $visitDate->format('Y-m-d'),
                    'person_met' => fake()->name(),
                    'contact_phone' => $client->contact_phone,
                    'visit_level' => $level,
                    'decision_maker_met' => $isLater ? fake()->boolean(70) : fake()->boolean(30),
                    'interested' => $interested,
                    'follow_up_done' => fake()->boolean(55),
                    'revenue_potential' => $revenue,
                    'notes' => fake()->optional(0.5)->sentence(),
                    'created_at' => $visitDate,
                    'updated_at' => $visitDate,
                ]);

                $lastVisitDate = $visitDate->copy();
                $visitDate->addDays(fake()->numberBetween(7, 25));
            }

            // Some proposal-stage clients close as won/lost.
            $finalStage = $reachedStage;
            if ($depth === 4 && fake()->boolean(60)) {
                $outcome = fake()->boolean(65) ? 'won' : 'lost';
                $closedAt = $lastVisitDate->copy()->addDays(fake()->numberBetween(3, 15));
                if ($closedAt->isFuture()) {
                    $closedAt = Carbon::now();
                }

                Deal::create([
                    'client_id' => $client->id,
                    'user_id' => $owner->id,
                    'outcome' => $outcome,
                    'actual_revenue' => $outcome === 'won'
                        ? fake()->randomElement([5000, 12000, 18000, 30000, 45000])
                        : 0,
                    'notes' => $outcome === 'won' ? 'Closed and onboarded.' : 'Went with a competitor.',
                    'closed_at' => $closedAt->format('Y-m-d'),
                ]);

                $finalStage = $outcome;
            }

            // Persist computed pipeline state on the client.
            $client->pipeline_stage = $finalStage;
            $client->last_visit_at = $lastVisitDate;
            $client->status = $lastVisitDate->lt(now()->subDays(Client::DORMANT_DAYS)) ? 'dormant' : 'active';
            $client->save();

            // Follow-ups: mix of overdue, due-today, upcoming, and done.
            $this->seedFollowUps($client, $owner);
        }

        $this->command?->info('Seeded: 1 admin, ' . $salespeople->count() . ' salespeople, ' . $clientCount . ' clients with visit history.');
    }

    private function seedFollowUps(Client $client, User $owner): void
    {
        // Only open (non-won/lost) clients get pending follow-ups.
        if (Pipeline::isTerminal($client->pipeline_stage)) {
            // closed clients keep a couple of historical done follow-ups
            if (fake()->boolean(50)) {
                FollowUp::factory()->done()->create([
                    'client_id' => $client->id,
                    'user_id' => $owner->id,
                    'due_date' => $client->last_visit_at?->copy()->subDays(3)->format('Y-m-d') ?? now()->subWeek()->format('Y-m-d'),
                ]);
            }

            return;
        }

        $roll = fake()->numberBetween(1, 100);

        if ($roll <= 25) {
            // Overdue
            FollowUp::factory()->create([
                'client_id' => $client->id,
                'user_id' => $owner->id,
                'due_date' => now()->subDays(fake()->numberBetween(1, 8))->format('Y-m-d'),
            ]);
        } elseif ($roll <= 50) {
            // Due today
            FollowUp::factory()->create([
                'client_id' => $client->id,
                'user_id' => $owner->id,
                'due_date' => today()->format('Y-m-d'),
            ]);
        } elseif ($roll <= 80) {
            // Upcoming
            FollowUp::factory()->create([
                'client_id' => $client->id,
                'user_id' => $owner->id,
                'due_date' => now()->addDays(fake()->numberBetween(1, 12))->format('Y-m-d'),
            ]);
        }

        // Most clients also have a historical, completed follow-up.
        if (fake()->boolean(60)) {
            FollowUp::factory()->done()->create([
                'client_id' => $client->id,
                'user_id' => $owner->id,
                'due_date' => now()->subDays(fake()->numberBetween(10, 40))->format('Y-m-d'),
            ]);
        }
    }
}
