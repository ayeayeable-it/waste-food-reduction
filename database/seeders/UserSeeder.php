<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'System Administrator',
                'email' => 'admin@rescuebite.local',
                'password' => Hash::make('Password123!'),
                'role' => 'admin',
                'phone' => '+44 20 7946 0001',
                'is_active' => true,
            ],
            [
                'name' => 'GreenMart Seller',
                'email' => 'seller1@rescuebite.local',
                'password' => Hash::make('Password123!'),
                'role' => 'seller',
                'phone' => '+44 20 7946 0101',
                'is_active' => true,
            ],
            [
                'name' => 'CornerFresh Seller',
                'email' => 'seller2@rescuebite.local',
                'password' => Hash::make('Password123!'),
                'role' => 'seller',
                'phone' => '+44 20 7946 0102',
                'is_active' => true,
            ],
            [
                'name' => 'Alice Buyer',
                'email' => 'buyer1@rescuebite.local',
                'password' => Hash::make('Password123!'),
                'role' => 'buyer',
                'phone' => '+44 20 7946 0201',
                'is_active' => true,
            ],
            [
                'name' => 'Bob Buyer',
                'email' => 'buyer2@rescuebite.local',
                'password' => Hash::make('Password123!'),
                'role' => 'buyer',
                'phone' => '+44 20 7946 0202',
                'is_active' => true,
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(['email' => $user['email']], $user);
        }
    }
}
