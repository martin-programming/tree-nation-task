<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'external_id' => fake()->uuid(),
            'name' => fake()->name(),
            'last_visited_at' => null,
            'total_visits' => 0,
            'trees_planted' => 0,
        ];
    }
}
