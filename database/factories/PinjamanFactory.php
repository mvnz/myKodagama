<?php

namespace Database\Factories;

use App\Models\Pinjaman;
use App\Models\Anggota;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PinjamanFactory extends Factory
{
    protected $model = Pinjaman::class;

    public function definition(): array
    {
        $jumlah  = fake()->randomElement([1000000, 2000000, 3000000, 5000000]);
        $jangka  = fake()->randomElement([6, 12, 24]);
        $bunga   = 1.5;
        $pokok   = $jumlah / $jangka;
        $cicilan = round($pokok + ($jumlah * $bunga / 100), 2);

        return [
            'nomor_pinjaman'      => 'PIN-' . fake()->unique()->numerify('######'),
            'anggota_id'          => Anggota::factory(),
            'jumlah_pinjaman'     => $jumlah,
            'jangka_waktu'        => $jangka,
            'bunga'               => $bunga,
            'cicilan_per_bulan'   => $cicilan,
            'tujuan'              => fake()->sentence(),
            'status'              => 'diajukan',
            'tanggal_pengajuan'   => now()->format('Y-m-d'),
            'tanggal_persetujuan' => null,
            'user_id'             => User::factory(),
        ];
    }

    public function disetujui(): static
    {
        return $this->state(fn ($attributes) => [
            'status'              => 'disetujui',
            'tanggal_persetujuan' => now()->format('Y-m-d'),
        ]);
    }

    public function lunas(): static
    {
        return $this->state(fn ($attributes) => [
            'status'              => 'lunas',
            'tanggal_persetujuan' => now()->subMonths(12)->format('Y-m-d'),
        ]);
    }
}
