<?php

namespace Database\Factories;

use App\Enums\Role;
use App\Enums\UserStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected static ?string $password = null;

    public function definition(): array
    {
        $first = fake()->randomElement([
            'Rani', 'Dimas', 'Alya', 'Bagas', 'Citra', 'Fajar', 'Gita', 'Hendra', 'Indah',
            'Jodi', 'Kirana', 'Lukman', 'Maya', 'Naufal', 'Olivia', 'Putra', 'Sari', 'Tirta',
        ]);

        $last = fake()->randomElement([
            'Lestari', 'Wijaya', 'Pratama', 'Anggraini', 'Nugroho', 'Halim', 'Saputra',
            'Kusuma', 'Maharani', 'Santoso', 'Permata', 'Hartono',
        ]);

        return [
            'name' => "{$first} {$last}",
            'email' => Str::lower("{$first}.{$last}").fake()->unique()->numberBetween(1, 9999).'@example.com',
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'phone' => '+62 8'.fake()->numerify('## #### ####'),
            'role' => Role::Customer,
            'status' => UserStatus::Active,
            'remember_token' => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn () => ['role' => Role::Admin]);
    }

    public function suspended(): static
    {
        return $this->state(fn () => ['status' => UserStatus::Suspended]);
    }
}
