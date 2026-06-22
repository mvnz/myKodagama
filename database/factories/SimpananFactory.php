<?php

namespace Database\Factories;

use App\Models\Koperasi\Simpanan;
use App\Models\Koperasi\Anggota;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SimpananFactory extends Factory
{
    protected $model = Simpanan::class;

    public function definition(): array
    {
        $anggota = Anggota::factory()->make();
        return [
            'anggota_id'     => Anggota::factory(),
            'jenis_anggota'  => fake()->randomElement(['PNS GOL 1', 'PNS GOL 2', 'PNS GOL 3', 'Non-PNS']),
            'jenis_simpanan' => fake()->randomElement(['pokok', 'wajib', 'sukarela']),
            'jumlah'         => fake()->randomElement([50000, 100000, 150000, 200000]),
            'tanggal'        => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'keterangan'     => fake()->optional()->sentence(),
            'user_id'        => User::factory(),
        ];
    }
}
