<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Address;
use App\Models\Cart;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@velora.test'],
            [
                'name' => 'Maya Prawira',
                'password' => Hash::make('password'),
                'phone' => '+62 811 2000 901',
                'role' => Role::Admin,
                'email_verified_at' => now(),
            ],
        );

        $demoCustomer = User::updateOrCreate(
            ['email' => 'customer@velora.test'],
            [
                'name' => 'Rani Lestari',
                'password' => Hash::make('password'),
                'phone' => '+62 812 3344 5566',
                'role' => Role::Customer,
                'email_verified_at' => now(),
            ],
        );

        $customers = User::factory()->count(24)->create()->push($demoCustomer);

        foreach ($customers as $customer) {
            Cart::firstOrCreate(['user_id' => $customer->id]);
            Wishlist::firstOrCreate(['user_id' => $customer->id], ['name' => 'My wishlist']);

            if ($customer->addresses()->doesntExist()) {
                Address::factory()
                    ->count(random_int(1, 2))
                    ->for($customer)
                    ->create();

                $customer->addresses()->first()?->makeDefault();
            }
        }

        $this->command->info("Seeded {$customers->count()} customers plus one admin account.");
        $this->command->line("  admin@velora.test / customer@velora.test — password: password");
    }
}
