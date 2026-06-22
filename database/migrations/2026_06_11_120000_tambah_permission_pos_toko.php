<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Rapikan grup POS agar konsisten di pengaturan role.
        DB::table('permissions')
            ->whereIn('slug', ['pos.kasir', 'pos.produk', 'pos.riwayat'])
            ->update(['grup' => 'POS']);

        DB::table('permissions')->insertOrIgnore([
            'slug' => 'pos.toko',
            'nama' => 'Kelola Supplier POS',
            'grup' => 'POS',
        ]);

        $adminId = DB::table('roles')->where('slug', 'admin')->value('id');
        $permissionIds = DB::table('permissions')
            ->whereIn('slug', ['pos.kasir', 'pos.produk', 'pos.riwayat', 'pos.toko'])
            ->pluck('id');

        if ($adminId) {
            foreach ($permissionIds as $permissionId) {
                DB::table('role_permission')->insertOrIgnore([
                    'role_id' => $adminId,
                    'permission_id' => $permissionId,
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('permissions')->where('slug', 'pos.toko')->delete();

        DB::table('permissions')
            ->whereIn('slug', ['pos.kasir', 'pos.produk', 'pos.riwayat'])
            ->update(['grup' => 'pos']);
    }
};
