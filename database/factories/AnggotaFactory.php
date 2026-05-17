<?php

namespace Database\Factories;

use App\Models\Anggota;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnggotaFactory extends Factory
{
    protected $model = Anggota::class;

    public function definition(): array
    {
        return [
            'nomor_anggota'     => 'KOP-' . fake()->unique()->numerify('######'),
            'nama'              => fake()->name(),
            'nik'               => fake()->unique()->numerify('################'),
            'tempat_lahir'      => fake()->city(),
            'tanggal_lahir'     => fake()->dateTimeBetween('-60 years', '-20 years')->format('Y-m-d'),
            'jenis_kelamin'     => fake()->randomElement(['Laki-laki', 'Perempuan']),
            'alamat'            => fake()->address(),
            'telepon'           => '08' . fake()->numerify('#########'),
            'pekerjaan'         => fake()->jobTitle(),
            'email'             => fake()->unique()->safeEmail(),
            'status'            => 'aktif',
            'tanggal_bergabung' => fake()->dateTimeBetween('-5 years', 'now')->format('Y-m-d'),
            'foto'              => null,
        ];
    }

    public function tidakAktif(): static
    {
        return $this->state(fn ($attributes) => ['status' => 'tidak_aktif']);
    }
}
