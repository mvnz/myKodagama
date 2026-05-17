<?php

namespace Database\Factories;

use App\Models\Pengaturan;
use Illuminate\Database\Eloquent\Factories\Factory;

class PengaturanFactory extends Factory
{
    protected $model = Pengaturan::class;

    public function definition(): array
    {
        return [
            'kunci' => fake()->unique()->slug(2),
            'nilai' => fake()->sentence(),
            'grup'  => fake()->randomElement(['profil', 'kontak', 'statistik']),
            'label' => fake()->words(2, true),
            'tipe'  => 'text',
        ];
    }
}
