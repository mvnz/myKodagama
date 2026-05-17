<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah kolom jenis_anggota ke tabel anggota
        Schema::table('anggota', function (Blueprint $table) {
            $table->string('jenis_anggota', 100)->nullable()->after('nama');
        });

        // Tambah pengaturan sp_simpanan_wajib_per_jenis (JSON)
        $defaultJson = json_encode([
            ['jenis' => 'Anggota Biasa',        'nominal' => 50000],
            ['jenis' => 'Anggota Luar Biasa',   'nominal' => 25000],
            ['jenis' => 'Calon Anggota',         'nominal' => 0],
        ]);

        DB::table('pengaturan')->insertOrIgnore([
            'kunci'      => 'sp_simpanan_wajib_per_jenis',
            'nilai'      => $defaultJson,
            'grup'       => 'simpan_pinjam',
            'label'      => 'Simpanan Wajib per Jenis Anggota',
            'tipe'       => 'json',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::table('anggota', function (Blueprint $table) {
            $table->dropColumn('jenis_anggota');
        });

        DB::table('pengaturan')->where('kunci', 'sp_simpanan_wajib_per_jenis')->delete();
    }
};
