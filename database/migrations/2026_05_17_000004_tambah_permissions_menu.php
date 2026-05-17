<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Permission;

return new class extends Migration
{
    public function up(): void
    {
        $baru = [
            ['nama' => 'Lihat Unit Kerja',         'slug' => 'unit-kerja.lihat',       'grup' => 'Data Master'],
            ['nama' => 'Kelola Pengurus',           'slug' => 'pengaturan.pengurus',    'grup' => 'Data Master'],
            ['nama' => 'Lihat Laporan Keuangan',    'slug' => 'laporan.lihat',          'grup' => 'Laporan'],
            ['nama' => 'Lihat Monitoring',          'slug' => 'monitoring.lihat',       'grup' => 'Laporan'],
            ['nama' => 'Lihat History Log',         'slug' => 'log.lihat',              'grup' => 'Pengaturan'],
            ['nama' => 'Kelola Pengaturan Simpan Pinjam', 'slug' => 'pengaturan.simpan-pinjam', 'grup' => 'Pengaturan'],
        ];

        foreach ($baru as $p) {
            Permission::firstOrCreate(['slug' => $p['slug']], $p);
        }

        // Tambahkan semua permission baru ke role admin
        $admin = \App\Models\Role::where('slug', 'admin')->first();
        if ($admin) {
            $allIds = Permission::pluck('id')->toArray();
            $admin->permissions()->sync($allIds);
        }
    }

    public function down(): void
    {
        Permission::whereIn('slug', [
            'unit-kerja.lihat', 'pengaturan.pengurus', 'laporan.lihat',
            'monitoring.lihat', 'log.lihat', 'pengaturan.simpan-pinjam',
        ])->delete();
    }
};
