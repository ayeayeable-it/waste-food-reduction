<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Listing;
use App\Models\Store;
use Illuminate\Database\Seeder;

class ListingSeeder extends Seeder
{
    public function run(): void
    {
        $greenMart = Store::where('name', 'GreenMart Express')->firstOrFail();
        $cornerFresh = Store::where('name', 'CornerFresh Mini-Market')->firstOrFail();

        $bakery = Category::where('slug', 'bakery-pastries')->firstOrFail();
        $dairy = Category::where('slug', 'dairy-chilled')->firstOrFail();
        $sandwiches = Category::where('slug', 'sandwiches-snacks')->firstOrFail();

        $listings = [
            [
                'store_id' => $greenMart->id,
                'category_id' => $bakery->id,
                'food_name' => 'Assorted Sourdough & Croissant Bag',
                'description' => 'Freshly baked this morning: 1 sourdough loaf and 2 butter croissants.',
                'original_price' => 6.50,
                'discounted_price' => 2.50,
                'available_quantity' => 5,
                'expiry_label' => 'Best Before: Today 22:00',
                'storage_instructions' => 'Store in a cool, dry place. Consume within 24 hours.',
                'allergen_info' => 'Contains wheat (gluten), milk, and eggs.',
                'collection_deadline' => now()->addHours(6),
                'status' => 'active',
            ],
            [
                'store_id' => $greenMart->id,
                'category_id' => $sandwiches->id,
                'food_name' => 'Chicken & Avocado Wrap Pack (x2)',
                'description' => 'Two chilled deli wraps approaching end-of-day display window.',
                'original_price' => 7.00,
                'discounted_price' => 3.00,
                'available_quantity' => 4,
                'expiry_label' => 'Use By: Today',
                'storage_instructions' => 'Keep refrigerated below 5°C. Eat today.',
                'allergen_info' => 'Contains wheat (gluten), mustard, and egg.',
                'collection_deadline' => now()->addHours(4),
                'status' => 'active',
            ],
            [
                'store_id' => $cornerFresh->id,
                'category_id' => $dairy->id,
                'food_name' => 'Greek Yogurt & Berry Pots (Pack of 3)',
                'description' => 'Three 150g chilled Greek yogurt pots with mixed berry compote.',
                'original_price' => 5.40,
                'discounted_price' => 2.20,
                'available_quantity' => 6,
                'expiry_label' => 'Use By: Tomorrow',
                'storage_instructions' => 'Keep refrigerated at 2°C–5°C.',
                'allergen_info' => 'Contains milk.',
                'collection_deadline' => now()->addHours(8),
                'status' => 'active',
            ],
        ];

        foreach ($listings as $listing) {
            Listing::firstOrCreate(
                [
                    'store_id' => $listing['store_id'],
                    'food_name' => $listing['food_name'],
                ],
                $listing
            );
        }
    }
}
