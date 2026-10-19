<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * Six venues matching the images already committed under public/images/.
 * Only amari.jpg and "grand hall mark hotel.jpg" have matching floor plans;
 * the rest fall back to the generic floorplan.jpg.
 */
class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $hotel = Category::where('name', 'Hotel')->value('id');
        $mall  = Category::where('name', 'Mall')->value('id');

        $products = [
            ['Amari Hotel',                 6.00,  'amari.jpg',                  'amari.jpg',                  $hotel],
            ['Grand Hallmark Hotel',        4.50,  'grand hall mark hotel.jpg',  'grand hall mark hotel.jpg',  $hotel],
            ['Miico Hotel',                 5.00,  'miico hotel.jpg',            'floorplan.jpg',              $hotel],
            ['KSL City Mall',               3.00,  'ksl.jpg',                    'floorplan.jpg',              $mall],
            ['Mid Valley Southkey',         3.50,  'mid valley southkey.jpg',    'floorplan.jpg',              $mall],
            ['Johor Bahru City Square',     4.00,  'city squae.jpg',             'floorplan.jpg',              $mall],
        ];

        foreach ($products as [$name, $price, $image, $floorPlan, $categoryID]) {
            Product::firstOrCreate(
                ['name' => $name],
                [
                    'price' => $price,
                    'image' => $image,
                    'floorPlan' => $floorPlan,
                    'categoryID' => (string) $categoryID,
                ]
            );
        }
    }
}
