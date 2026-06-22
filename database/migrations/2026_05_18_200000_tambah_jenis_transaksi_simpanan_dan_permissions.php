<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Permission;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah kolom jenis_transaksi ke tabel simpanan
        Schema::table('simpanan', function (Blueprint $table) {
            $table->enum('jenis_transaksi', ['setor', 'tarik'])->default('setor')->after('jenis_simpanan');
        });

        // Permission penarikan
        $baru = [
            ['nama' => 'Tarik Simpanan',    'slug' => 'simpanan.tarik',   'grup' => 'Simpanan'],
            ['nama' => 'Kelola Pendapatan', 'slug' => 'pendapatan.kelola','grup' => 'Keuangan'],
            ['nama' => 'Lihat Pendapatan',  'slug' => 'pendapatan.lihat', 'grup' => 'Keuangan'],
            ['nama' => 'Kelola User',       'slug' => 'user.kelola',      'grup' => 'Pengaturan'],
        ];
        foreach ($baru as $p) {
            Permission::firstOrCreate(['slug' => $p['slug']], $p);
        }

        $admin = \App\Models\Role::where('slug', 'admin')->first();
        if ($admin) {
            $admin->permissions()->sync(Permission::pluck('id')->toArray());
        }

        $ketua = \App\Models\Role::where('slug', 'ketua')->first();
        if ($ketua) {
            $ids = Permission::whereIn('slug', ['pendapatan.lihat'])->pluck('id')->toArray();
            $ketua->permissions()->syncWithoutDetaching($ids);
        }
    }

    public function down(): void
    {
        Schema::table('simpanan', function (Blueprint $table) {
            $table->dropColumn('jenis_transaksi');
        });
        Permission::whereIn('slug', ['simpanan.tarik','pendapatan.kelola','pendapatan.lihat','user.kelola'])->delete();
    }
};
