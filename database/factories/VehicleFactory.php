<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Vehicle>
 */
class VehicleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'plate_number' => strtoupper(fake()->unique()->bothify('T ### ??#')),
            'owner_name' => fake()->company(),
            'capacity' => fake()->randomElement([10000, 15000, 20000, 28000, 30000, 34000]),
            'status' => 'active',
        ];
    }
}
