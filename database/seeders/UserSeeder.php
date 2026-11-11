<?php

namespace Database\Seeders;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@ecom.test'],
            [
                'name' => 'System Admin',
                'phone' => '+8801700000001',
                'password' => Hash::make('password'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $admin->syncRoles(['admin']);

        $owners = [
            [
                'user' => [
                    'name' => 'Rahim Uddin',
                    'email' => 'owner@ecom.test',
                    'phone' => '+8801700000002',
                    'password' => Hash::make('password'),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ],
                'shop' => [
                    'name' => 'Rahim Super Shop',
                    'slug' => 'rahim-super-shop',
                    'description' => 'Everyday grocery, household and personal care products at fair prices.',
                    'phone' => '+8801700000002',
                    'address' => 'House 12, Road 5, Dhanmondi, Dhaka',
                    'is_active' => true,
                ],
            ],
            [
                'user' => [
                    'name' => 'Karim Ahmed',
                    'email' => 'owner2@ecom.test',
                    'phone' => '+8801700000003',
                    'password' => Hash::make('password'),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ],
                'shop' => [
                    'name' => 'Karim Traders',
                    'slug' => 'karim-traders',
                    'description' => 'Electronics, accessories and lifestyle products for modern homes.',
                    'phone' => '+8801700000003',
                    'address' => 'Shop 4, Bashundhara City, Panthapath, Dhaka',
                    'is_active' => true,
                ],
            ],
            [
                'user' => [
                    'name' => 'Nusrat Jahan',
                    'email' => 'owner3@ecom.test',
                    'phone' => '+8801700000004',
                    'password' => Hash::make('password'),
                    'is_active' => false,
                    'email_verified_at' => now(),
                ],
                'shop' => [
                    'name' => 'Nusrat Fashion House',
                    'slug' => 'nusrat-fashion-house',
                    'description' => 'Fashion and apparel store (account currently inactive).',
                    'phone' => '+8801700000004',
                    'address' => 'Level 3, Jamuna Future Park, Dhaka',
                    'is_active' => false,
                ],
            ],
        ];

        foreach ($owners as $data) {
            $owner = User::updateOrCreate(['email' => $data['user']['email']], $data['user']);
            $owner->syncRoles(['shop-owner']);

            Shop::updateOrCreate(
                ['slug' => $data['shop']['slug']],
                array_merge($data['shop'], ['user_id' => $owner->id])
            );
        }
    }
}
