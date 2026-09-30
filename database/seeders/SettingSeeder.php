<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'store.name', 'value' => 'VELORA', 'group' => 'general'],
            ['key' => 'store.tagline', 'value' => 'Considered clothing for every day', 'group' => 'general'],
            ['key' => 'store.email', 'value' => 'hello@velora.test', 'group' => 'general'],
            ['key' => 'store.phone', 'value' => '+62 811 2000 900', 'group' => 'general'],
            // Digits only, country code first — this is what wa.me expects.
            ['key' => 'store.whatsapp', 'value' => '6281120009000', 'group' => 'general'],
            ['key' => 'store.address', 'value' => 'Jl. Senopati No. 42, Kebayoran Baru, Jakarta Selatan 12190', 'group' => 'general'],
            ['key' => 'store.currency', 'value' => 'IDR', 'group' => 'general'],
            ['key' => 'shipping.free_threshold', 'value' => '500000', 'group' => 'shipping', 'type' => 'integer'],
            ['key' => 'inventory.low_stock_threshold', 'value' => '5', 'group' => 'inventory', 'type' => 'integer'],
            ['key' => 'orders.auto_cancel_hours', 'value' => '24', 'group' => 'orders', 'type' => 'integer'],
            ['key' => 'reviews.auto_approve', 'value' => '0', 'group' => 'reviews', 'type' => 'boolean'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
