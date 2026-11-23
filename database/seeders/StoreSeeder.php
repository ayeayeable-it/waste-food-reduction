<?php

namespace Database\Seeders;

use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    public function run(): void
    {
        $seller1 = User::where('email', 'seller1@rescuebite.local')->firstOrFail();
        $seller2 = User::where('email', 'seller2@rescuebite.local')->firstOrFail();

        $stores = [
            [
                'user_id' => $seller1->id,
                'name' => 'GreenMart Express',
                'address' => '142 High Street, Central District',
                'latitude' => 51.5073509,
                'longitude' => -0.1277583,
                'phone' => '+44 20 7946 0101',
                'collection_instructions' => 'Ask at the main checkout counter and show your reservation reference.',
                'is_active' => true,
            ],
            [
                'user_id' => $seller2->id,
                'name' => 'CornerFresh Mini-Market',
                'address' => '48 Market Lane, Eastside',
                'latitude' => 51.5154190,
                'longitude' => -0.1410990,
                'phone' => '+44 20 7946 0102',
                'collection_instructions' => 'Collect from the chilled pickup shelf beside counter 2.',
                'is_active' => true,
            ],
        ];

        foreach ($stores as $store) {
            Store::updateOrCreate(['name' => $store['name']], $store);
        }
    }
}
