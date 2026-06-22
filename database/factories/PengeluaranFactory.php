<?php

namespace Database\Factories;

use App\Models\Koperasi\Pengeluaran;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PengeluaranFactory extends Factory
{
    protected $model = Pengeluaran::class;

    public function definition(): array
    {
        return [
            'tanggal'    => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'judul'      => fake()->sentence(4),
            'kategori'   => fake()->randomElement(array_keys(Pengeluaran::labelKategori())),
            'jumlah'     => fake()->numberBetween(50000, 10000000),
            'keterangan' => fake()->optional()->sentence(),
            'bukti'      => null,
            'user_id'    => User::factory(),
        ];
    }
}
