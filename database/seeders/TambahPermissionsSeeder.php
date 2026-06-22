<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class TambahPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Pengeluaran
            ['nama' => 'Lihat Pengeluaran',    'slug' => 'pengeluaran.lihat',   'grup' => 'Pengeluaran'],
            ['nama' => 'Tambah Pengeluaran',   'slug' => 'pengeluaran.tambah',  'grup' => 'Pengeluaran'],
            ['nama' => 'Edit Pengeluaran',     'slug' => 'pengeluaran.edit',    'grup' => 'Pengeluaran'],
            ['nama' => 'Hapus Pengeluaran',    'slug' => 'pengeluaran.hapus',   'grup' => 'Pengeluaran'],

            // Pendapatan
            ['nama' => 'Lihat Pendapatan',     'slug' => 'pendapatan.lihat',    'grup' => 'Pendapatan'],
            ['nama' => 'Tambah Pendapatan',    'slug' => 'pendapatan.tambah',   'grup' => 'Pendapatan'],
            ['nama' => 'Edit Pendapatan',      'slug' => 'pendapatan.edit',     'grup' => 'Pendapatan'],
            ['nama' => 'Hapus Pendapatan',     'slug' => 'pendapatan.hapus',    'grup' => 'Pendapatan'],

            // SHU
            ['nama' => 'Lihat SHU',            'slug' => 'shu.lihat',           'grup' => 'SHU'],
            ['nama' => 'Hitung SHU',           'slug' => 'shu.hitung',          'grup' => 'SHU'],
            ['nama' => 'Finalisasi SHU',        'slug' => 'shu.finalisasi',      'grup' => 'SHU'],
            ['nama' => 'Hapus SHU',            'slug' => 'shu.hapus',           'grup' => 'SHU'],

            // Laporan
            ['nama' => 'Lihat Laporan Keuangan', 'slug' => 'laporan.lihat',     'grup' => 'Laporan'],
            ['nama' => 'Lihat Laporan Laba Rugi','slug' => 'laporan.laba-rugi', 'grup' => 'Laporan'],

            // Monitoring
            ['nama' => 'Monitoring Simpanan',  'slug' => 'monitoring.simpanan', 'grup' => 'Monitoring'],
            ['nama' => 'Monitoring Pinjaman',  'slug' => 'monitoring.pinjaman', 'grup' => 'Monitoring'],

            // Unit Kerja
            ['nama' => 'Kelola Unit Kerja',    'slug' => 'unit-kerja.kelola',   'grup' => 'Unit Kerja'],

            // Export / Import
            ['nama' => 'Export Data',          'slug' => 'export.data',         'grup' => 'Export & Import'],
            ['nama' => 'Import Data',          'slug' => 'import.data',         'grup' => 'Export & Import'],

            // Pengaturan tambahan
            ['nama' => 'Kelola Simpan Pinjam', 'slug' => 'pengaturan.simpan-pinjam', 'grup' => 'Pengaturan'],
            ['nama' => 'Kelola Pengurus',      'slug' => 'pengaturan.pengurus',      'grup' => 'Pengaturan'],
            ['nama' => 'Kelola Notifikasi WA', 'slug' => 'pengaturan.notifikasi-wa', 'grup' => 'Pengaturan'],

            // Log Aktivitas
            ['nama' => 'Lihat Log Aktivitas',  'slug' => 'log.lihat',           'grup' => 'Log'],
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['slug' => $p['slug']], $p);
        }

        $this->command->info('Permissions berhasil ditambahkan/diperbarui.');
    }
}
