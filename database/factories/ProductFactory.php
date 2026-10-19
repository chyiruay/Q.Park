<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 *
 * `image` and `floorPlan` store bare filenames resolved against public/images
 * and public/floorPlan — views render them as asset('images/') . '/' . $product->image.
 * `categoryID` is a string column (see the products migration), hence the cast.
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->company() . ' Parking',
            'price' => fake()->randomFloat(2, 2, 25),
            'image' => 'parking_1.webp',
            'floorPlan' => 'floorplan.jpg',
            'categoryID' => fn () => (string) Category::factory()->create()->id,
        ];
    }
}
