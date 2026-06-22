<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Permission;

return new class extends Migration
{
    public function up(): void
    {
        $baru = [
            ['nama' => 'Lihat Pengeluaran',  'slug' => 'pengeluaran.lihat',   'grup' => 'Keuangan'],
            ['nama' => 'Kelola Pengeluaran', 'slug' => 'pengeluaran.kelola',  'grup' => 'Keuangan'],
        ];

        foreach ($baru as $p) {
            Permission::firstOrCreate(['slug' => $p['slug']], $p);
        }

        // Semua ke admin
        $admin = \App\Models\Role::where('slug', 'admin')->first();
        if ($admin) {
            $admin->permissions()->sync(Permission::pluck('id')->toArray());
        }

        // pengeluaran.lihat ke ketua
        $ketua = \App\Models\Role::where('slug', 'ketua')->first();
        if ($ketua) {
            $ids = Permission::whereIn('slug', ['pengeluaran.lihat'])->pluck('id')->toArray();
            $ketua->permissions()->syncWithoutDetaching($ids);
        }
    }

    public function down(): void
    {
        Permission::whereIn('slug', ['pengeluaran.lihat', 'pengeluaran.kelola'])->delete();
    }
};
