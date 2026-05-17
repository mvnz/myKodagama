<?php

namespace Database\Factories;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PermissionFactory extends Factory
{
    protected $model = Permission::class;

    public function definition(): array
    {
        $grup  = fake()->randomElement(['Anggota', 'Simpanan', 'Pinjaman', 'Angsuran', 'Pengguna', 'Pengaturan']);
        $aksi  = fake()->randomElement(['lihat', 'tambah', 'edit', 'hapus']);
        $slug  = Str::slug($grup) . '.' . $aksi . '.' . fake()->unique()->randomNumber(4);
        return [
            'nama' => ucfirst($aksi) . ' ' . $grup,
            'slug' => $slug,
            'grup' => $grup,
        ];
    }
}
