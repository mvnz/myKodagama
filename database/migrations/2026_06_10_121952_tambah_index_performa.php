<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('anggota', function (Blueprint $table) {
            if (!$this->hasIndex('anggota', 'anggota_status_idx'))
                $table->index('status', 'anggota_status_idx');
            if (!$this->hasIndex('anggota', 'anggota_bergabung_idx'))
                $table->index('tanggal_bergabung', 'anggota_bergabung_idx');
        });

        Schema::table('simpanan', function (Blueprint $table) {
            if (!$this->hasIndex('simpanan', 'simpanan_jenis_idx'))
                $table->index('jenis_simpanan', 'simpanan_jenis_idx');
            if (!$this->hasIndex('simpanan', 'simpanan_tanggal_idx'))
                $table->index('tanggal', 'simpanan_tanggal_idx');
            if (!$this->hasIndex('simpanan', 'simpanan_anggota_jenis_tgl_idx'))
                $table->index(['anggota_id', 'jenis_simpanan', 'tanggal'], 'simpanan_anggota_jenis_tgl_idx');
        });

        Schema::table('pinjaman', function (Blueprint $table) {
            if (!$this->hasIndex('pinjaman', 'pinjaman_status_idx'))
                $table->index('status', 'pinjaman_status_idx');
            if (!$this->hasIndex('pinjaman', 'pinjaman_tgl_persetujuan_idx'))
                $table->index('tanggal_persetujuan', 'pinjaman_tgl_persetujuan_idx');
            if (!$this->hasIndex('pinjaman', 'pinjaman_tgl_pengajuan_idx'))
                $table->index('tanggal_pengajuan', 'pinjaman_tgl_pengajuan_idx');
        });

        Schema::table('angsuran', function (Blueprint $table) {
            if (!$this->hasIndex('angsuran', 'angsuran_status_jatuh_idx'))
                $table->index(['status', 'tanggal_jatuh_tempo'], 'angsuran_status_jatuh_idx');
            if (!$this->hasIndex('angsuran', 'angsuran_tanggal_bayar_idx'))
                $table->index('tanggal_bayar', 'angsuran_tanggal_bayar_idx');
        });

        Schema::table('pengeluaran', function (Blueprint $table) {
            if (!$this->hasIndex('pengeluaran', 'pengeluaran_tanggal_idx'))
                $table->index('tanggal', 'pengeluaran_tanggal_idx');
        });

        Schema::table('pendapatan', function (Blueprint $table) {
            if (!$this->hasIndex('pendapatan', 'pendapatan_tanggal_idx'))
                $table->index('tanggal', 'pendapatan_tanggal_idx');
        });
    }

    public function down(): void
    {
        Schema::table('anggota',     function ($t) { $t->dropIndex('anggota_status_idx'); $t->dropIndex('anggota_bergabung_idx'); });
        Schema::table('simpanan',    function ($t) { $t->dropIndex('simpanan_jenis_idx'); $t->dropIndex('simpanan_tanggal_idx'); $t->dropIndex('simpanan_anggota_jenis_tgl_idx'); });
        Schema::table('pinjaman',    function ($t) { $t->dropIndex('pinjaman_status_idx'); $t->dropIndex('pinjaman_tgl_persetujuan_idx'); $t->dropIndex('pinjaman_tgl_pengajuan_idx'); });
        Schema::table('angsuran',    function ($t) { $t->dropIndex('angsuran_status_jatuh_idx'); $t->dropIndex('angsuran_tanggal_bayar_idx'); });
        Schema::table('pengeluaran', fn($t) => $t->dropIndex('pengeluaran_tanggal_idx'));
        Schema::table('pendapatan',  fn($t) => $t->dropIndex('pendapatan_tanggal_idx'));
    }

    private function hasIndex(string $table, string $name): bool
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            return collect(DB::select("PRAGMA index_list('{$table}')"))
                ->pluck('name')
                ->contains($name);
        }

        return collect(DB::select("SHOW INDEX FROM `{$table}`"))
            ->pluck('Key_name')
            ->contains($name);
    }
};
