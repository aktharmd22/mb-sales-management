<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    public function definition(): array
    {
        $suffixes = ['Sdn Bhd', 'Enterprise', 'Trading', 'Resources', 'Holdings', 'Marketing', '& Sons'];
        $cities = ['Kuala Lumpur', 'Petaling Jaya', 'Shah Alam', 'Johor Bahru', 'Penang', 'Ipoh', 'Melaka', 'Kuching', 'Kota Kinabalu', 'Seremban'];
        $categories = ['Retail', 'F&B', 'Wholesale', 'Manufacturing', 'Services', 'Construction', 'Logistics', 'Education', 'Healthcare', 'Automotive'];

        return [
            'business_name' => fake()->company() . ' ' . fake()->randomElement($suffixes),
            'contact_person' => fake()->name(),
            'contact_phone' => '+60' . fake()->numerify('1#-###-####'),
            'address' => fake()->buildingNumber() . ', Jalan ' . fake()->streetName() . ', ' . fake()->randomElement($cities),
            'category' => fake()->randomElement($categories),
            'assigned_to' => User::factory(),
            'created_by' => fn (array $attrs) => $attrs['assigned_to'],
            'pipeline_stage' => 'cold',
            'status' => 'active',
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
