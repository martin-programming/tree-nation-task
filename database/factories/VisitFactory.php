<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Visit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Visit>
 */
class VisitFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'visited_at' => fake()->dateTimeBetween('-7 days', 'now'),
        ];
    }
}
