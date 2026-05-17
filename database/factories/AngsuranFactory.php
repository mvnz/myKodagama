<?php

namespace Database\Factories;

use App\Models\Angsuran;
use App\Models\Pinjaman;
use Illuminate\Database\Eloquent\Factories\Factory;

class AngsuranFactory extends Factory
{
    protected $model = Angsuran::class;

    public function definition(): array
    {
        return [
            'pinjaman_id'         => Pinjaman::factory()->disetujui(),
            'ke'                  => 1,
            'jumlah_angsuran'     => 300000,
            'pokok'               => 250000,
            'bunga'               => 50000,
            'tanggal_jatuh_tempo' => now()->addMonth()->format('Y-m-d'),
            'tanggal_bayar'       => null,
            'status'              => 'belum',
        ];
    }

    public function sudahBayar(): static
    {
        return $this->state(fn ($attributes) => [
            'status'        => 'sudah',
            'tanggal_bayar' => now()->format('Y-m-d'),
        ]);
    }
}
