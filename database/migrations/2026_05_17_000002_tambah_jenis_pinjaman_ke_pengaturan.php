<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $default = json_encode([
            [
                'nama'        => 'Pinjaman Reguler',
                'bunga'       => 1.5,
                'min_pinjaman'=> 500000,
                'maks_pinjaman'=> 10000000,
                'min_jangka'  => 3,
                'maks_jangka' => 24,
            ],
            [
                'nama'        => 'Pinjaman Darurat',
                'bunga'       => 1.0,
                'min_pinjaman'=> 100000,
                'maks_pinjaman'=> 3000000,
                'min_jangka'  => 1,
                'maks_jangka' => 12,
            ],
        ]);

        DB::table('pengaturan')->insertOrIgnore([
            'kunci'      => 'sp_jenis_pinjaman',
            'nilai'      => $default,
            'grup'       => 'simpan_pinjam',
            'label'      => 'Jenis Pinjaman',
            'tipe'       => 'json',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Tambah kolom jenis_pinjaman ke tabel pinjaman
        if (!Schema::hasColumn('pinjaman', 'jenis_pinjaman')) {
            Schema::table('pinjaman', function ($table) {
                $table->string('jenis_pinjaman', 100)->nullable()->after('tujuan');
            });
        }
    }

    public function down(): void
    {
        DB::table('pengaturan')->where('kunci', 'sp_jenis_pinjaman')->delete();

        Schema::table('pinjaman', function ($table) {
            $table->dropColumn('jenis_pinjaman');
        });
    }
};
