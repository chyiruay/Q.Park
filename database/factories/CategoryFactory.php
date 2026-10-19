<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            // categories.name has no unique constraint, so no unique() here —
            // it would throw once the list is exhausted.
            'name' => fake()->randomElement(['Hotel', 'Mall', 'Airport', 'Stadium', 'Hospital']),
        ];
    }
}
