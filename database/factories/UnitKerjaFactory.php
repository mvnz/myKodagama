<?php

namespace Database\Factories;

use App\Models\Koperasi\UnitKerja;
use Illuminate\Database\Eloquent\Factories\Factory;

class UnitKerjaFactory extends Factory
{
    protected $model = UnitKerja::class;

    public function definition(): array
    {
        return [
            'kode'       => strtoupper(fake()->unique()->lexify('UK-???')),
            'nama'       => fake()->company(),
            'singkatan'  => strtoupper(fake()->lexify('???')),
            'keterangan' => fake()->optional()->sentence(),
            'aktif'      => true,
        ];
    }

    public function tidakAktif(): static
    {
        return $this->state(fn ($attributes) => ['aktif' => false]);
    }
}
