<?php

namespace Database\Factories;

use App\Models\Koperasi\Pendapatan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PendapatanFactory extends Factory
{
    protected $model = Pendapatan::class;

    public function definition(): array
    {
        return [
            'tanggal'    => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'judul'      => fake()->sentence(4),
            'kategori'   => fake()->randomElement(array_keys(Pendapatan::labelKategori())),
            'jumlah'     => fake()->numberBetween(100000, 5000000),
            'keterangan' => fake()->optional()->sentence(),
            'user_id'    => User::factory(),
        ];
    }
}
