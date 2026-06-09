<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\FollowUp>
 */
class FollowUpFactory extends Factory
{
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'user_id' => User::factory(),
            'due_date' => fake()->dateTimeBetween('-1 week', '+2 weeks')->format('Y-m-d'),
            'note' => fake()->randomElement([
                'Call to confirm meeting',
                'Send proposal document',
                'Follow up on quote',
                'Drop off samples',
                'Check decision status',
                'Schedule product demo',
            ]),
            'status' => 'pending',
        ];
    }

    public function done(): static
    {
        return $this->state(fn () => [
            'status' => 'done',
            'completed_at' => now(),
        ]);
    }
}
