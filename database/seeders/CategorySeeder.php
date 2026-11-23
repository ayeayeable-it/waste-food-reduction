<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Bakery & Pastries', 'description' => 'Fresh bread, rolls, croissants, and baked goods.'],
            ['name' => 'Dairy & Chilled', 'description' => 'Milk, yogurt, cheese, and chilled ready meals.'],
            ['name' => 'Fresh Produce', 'description' => 'Fruits, vegetables, and salad packs.'],
            ['name' => 'Sandwiches & Snacks', 'description' => 'Grab-and-go sandwiches, wraps, and savoury snacks.'],
            ['name' => 'Pantry & Dry Goods', 'description' => 'Packaged goods, cereals, and ambient items near best-before date.'],
            ['name' => 'Beverages', 'description' => 'Juices, smoothies, and bottled drinks.'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => Str::slug($category['name'])],
                [
                    'name' => $category['name'],
                    'description' => $category['description'],
                ]
            );
        }
    }
}
