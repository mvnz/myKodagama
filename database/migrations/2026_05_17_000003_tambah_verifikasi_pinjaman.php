<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL tidak bisa langsung ALTER enum, gunakan MODIFY COLUMN
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE pinjaman MODIFY COLUMN status ENUM('diajukan','diverifikasi','disetujui','ditolak','lunas') NOT NULL DEFAULT 'diajukan'");
        }

        Schema::table('pinjaman', function (Blueprint $table) {
            $table->date('tanggal_verifikasi')->nullable()->after('tanggal_pengajuan');
            $table->unsignedBigInteger('verifikator_id')->nullable()->after('tanggal_verifikasi');
            $table->foreign('verifikator_id')->references('id')->on('users')->onDelete('set null');
        });

        // Tambah permission verifikasi pinjaman
        DB::table('permissions')->insertOrIgnore([
            'nama'       => 'Verifikasi Pinjaman',
            'slug'       => 'pinjaman.verifikasi',
            'grup'       => 'Pinjaman',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Berikan ke role admin (sync semua permission admin)
        $verifikasiId = DB::table('permissions')->where('slug', 'pinjaman.verifikasi')->value('id');
        $adminRoleId  = DB::table('roles')->where('slug', 'admin')->value('id');
        if ($verifikasiId && $adminRoleId) {
            DB::table('role_permission')->insertOrIgnore([
                'role_id'       => $adminRoleId,
                'permission_id' => $verifikasiId,
            ]);
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE pinjaman MODIFY COLUMN status ENUM('diajukan','disetujui','ditolak','lunas') NOT NULL DEFAULT 'diajukan'");
        }

        Schema::table('pinjaman', function (Blueprint $table) {
            $table->dropForeign(['verifikator_id']);
            $table->dropColumn(['tanggal_verifikasi', 'verifikator_id']);
        });

        DB::table('permissions')->where('slug', 'pinjaman.verifikasi')->delete();
    }
};
