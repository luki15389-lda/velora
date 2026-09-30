<?php

namespace Database\Seeders;

use App\Enums\CouponType;
use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $coupons = [
            [
                'code' => 'VELORA10',
                'description' => '10% off your first order.',
                'type' => CouponType::Percentage,
                'value' => 10,
                'minimum_purchase' => 250_000,
                'maximum_discount' => 100_000,
                'usage_limit' => 500,
                'usage_limit_per_user' => 1,
                'starts_at' => now()->subDays(30),
                'ends_at' => now()->addDays(60),
            ],
            [
                'code' => 'NEWSEASON',
                'description' => '15% off the new arrivals drop.',
                'type' => CouponType::Percentage,
                'value' => 15,
                'minimum_purchase' => 500_000,
                'maximum_discount' => 250_000,
                'usage_limit' => 200,
                'usage_limit_per_user' => 2,
                'starts_at' => now()->subDays(7),
                'ends_at' => now()->addDays(30),
            ],
            [
                'code' => 'HEMAT50K',
                'description' => 'Rp50.000 off orders over Rp400.000.',
                'type' => CouponType::Fixed,
                'value' => 50_000,
                'minimum_purchase' => 400_000,
                'usage_limit' => null,
                'usage_limit_per_user' => 3,
                'starts_at' => now()->subDays(14),
                'ends_at' => now()->addDays(45),
            ],
            [
                'code' => 'GRATISONGKIR',
                'description' => 'Free shipping, no minimum.',
                'type' => CouponType::FreeShipping,
                'value' => 0,
                'minimum_purchase' => 0,
                'usage_limit' => 1000,
                'usage_limit_per_user' => 5,
                'starts_at' => now()->subDays(3),
                'ends_at' => now()->addDays(21),
            ],
            [
                'code' => 'LEBARAN25',
                'description' => 'Expired holiday promotion, kept for reporting.',
                'type' => CouponType::Percentage,
                'value' => 25,
                'minimum_purchase' => 300_000,
                'maximum_discount' => 300_000,
                'usage_limit' => 300,
                'usage_limit_per_user' => 1,
                'starts_at' => now()->subDays(120),
                'ends_at' => now()->subDays(60),
                'is_active' => false,
            ],
        ];

        foreach ($coupons as $coupon) {
            Coupon::updateOrCreate(['code' => $coupon['code']], $coupon + ['is_active' => true]);
        }

        $this->command->info('Seeded '.Coupon::count().' coupons.');
    }
}
