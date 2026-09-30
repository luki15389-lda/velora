<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AddressFactory extends Factory
{
    public function definition(): array
    {
        $places = [
            ['DKI Jakarta', 'Jakarta Selatan', 'Kebayoran Baru', '12190'],
            ['DKI Jakarta', 'Jakarta Pusat', 'Menteng', '10310'],
            ['Jawa Barat', 'Bandung', 'Coblong', '40132'],
            ['Jawa Tengah', 'Semarang', 'Banyumanik', '50264'],
            ['Jawa Tengah', 'Pati', 'Pati Kota', '59114'],
            ['Jawa Timur', 'Surabaya', 'Gubeng', '60281'],
            ['Bali', 'Denpasar', 'Denpasar Barat', '80119'],
            ['DI Yogyakarta', 'Sleman', 'Depok', '55281'],
        ];

        [$province, $city, $district, $postal] = fake()->randomElement($places);

        return [
            'user_id' => User::factory(),
            'label' => fake()->randomElement(['Home', 'Office', 'Parents']),
            'recipient_name' => fake()->name(),
            'phone' => '+62 8'.fake()->numerify('## #### ####'),
            'province' => $province,
            'city' => $city,
            'district' => $district,
            'postal_code' => $postal,
            'street_address' => 'Jl. '.fake()->randomElement([
                'Melati', 'Cendana', 'Kenanga', 'Diponegoro', 'Gajah Mada', 'Pemuda', 'Merdeka',
            ]).' No. '.fake()->numberBetween(1, 180),
            'notes' => fake()->optional(0.3)->randomElement([
                'Leave with the security desk.',
                'Ring the bell twice.',
                'Green gate, second house from the corner.',
            ]),
            'is_default' => false,
        ];
    }
}
