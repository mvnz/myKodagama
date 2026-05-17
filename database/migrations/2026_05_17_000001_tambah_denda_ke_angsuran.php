<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah kolom denda ke tabel angsuran
        Schema::table('angsuran', function (Blueprint $table) {
            $table->decimal('denda', 15, 2)->default(0)->after('bunga');
            $table->integer('hari_terlambat')->default(0)->after('denda');
        });

        // Tambah pengaturan bunga keterlambatan
        DB::table('pengaturan')->insertOrIgnore([
            'kunci'      => 'sp_bunga_keterlambatan',
            'nilai'      => '2',
            'grup'       => 'simpan_pinjam',
            'label'      => 'Bunga Keterlambatan (% per bulan)',
            'tipe'       => 'number',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::table('angsuran', function (Blueprint $table) {
            $table->dropColumn(['denda', 'hari_terlambat']);
        });

        DB::table('pengaturan')->where('kunci', 'sp_bunga_keterlambatan')->delete();
    }
};
