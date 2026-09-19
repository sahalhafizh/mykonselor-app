<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'nim' => fake()->unique()->numerify('############'),
            'no_telp' => '628'.fake()->numerify('##########'),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'fakultas' => 'Fakultas Ilmu Komputer',
            'program_studi' => 'Teknik Informatika',
            'role' => 'mahasiswa',
            'status' => 'aktif',
            'data_consent_at' => now(),
            'identity_verified_at' => now(),
            'remember_token' => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn () => ['role' => 'admin']);
    }
}
