<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\User;
use App\Support\Pipeline;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Visit>
 */
class VisitFactory extends Factory
{
    public function definition(): array
    {
        $level = fake()->randomElement(array_keys(Pipeline::STAGES));
        $interested = fake()->boolean(60);

        return [
            'client_id' => Client::factory(),
            'user_id' => User::factory(),
            'visit_date' => fake()->dateTimeBetween('-4 months', 'now')->format('Y-m-d'),
            'person_met' => fake()->name(),
            'contact_phone' => '+60' . fake()->numerify('1#-###-####'),
            'visit_level' => $level,
            'decision_maker_met' => fake()->boolean(45),
            'interested' => $interested,
            'follow_up_done' => fake()->boolean(50),
            'revenue_potential' => $interested ? fake()->randomElement([1500, 3000, 5000, 8000, 12000, 20000, 35000]) : 0,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
