<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Permission;

return new class extends Migration
{
    public function up(): void
    {
        $baru = [
            ['nama' => 'Lihat SHU',   'slug' => 'shu.lihat',   'grup' => 'Laporan'],
            ['nama' => 'Kelola SHU',  'slug' => 'shu.kelola',  'grup' => 'Laporan'],
        ];

        foreach ($baru as $p) {
            Permission::firstOrCreate(['slug' => $p['slug']], $p);
        }

        // Tambahkan ke role admin
        $admin = \App\Models\Role::where('slug', 'admin')->first();
        if ($admin) {
            $allIds = Permission::pluck('id')->toArray();
            $admin->permissions()->sync($allIds);
        }

        // Tambahkan shu.lihat ke role ketua jika ada
        $ketua = \App\Models\Role::where('slug', 'ketua')->first();
        if ($ketua) {
            $ids = Permission::whereIn('slug', ['shu.lihat'])->pluck('id')->toArray();
            $ketua->permissions()->syncWithoutDetaching($ids);
        }
    }

    public function down(): void
    {
        Permission::whereIn('slug', ['shu.lihat', 'shu.kelola'])->delete();
    }
};
