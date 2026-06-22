<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─── anggota ───────────────────────────────────────────────────────────
        Schema::table('anggota', function (Blueprint $table) {
            $table->index(['status', 'created_at'],        'idx_anggota_status_created');
            $table->index(['status', 'tanggal_bergabung'], 'idx_anggota_status_bergabung');
        });

        // ─── simpanan ──────────────────────────────────────────────────────────
        Schema::table('simpanan', function (Blueprint $table) {
            $table->index(['anggota_id', 'jenis_simpanan'], 'idx_simpanan_anggota_jenis');
            $table->index(['tanggal'],                       'idx_simpanan_tanggal');
            $table->index(['jenis_simpanan', 'tanggal'],     'idx_simpanan_jenis_tanggal');
        });

        // ─── pinjaman ──────────────────────────────────────────────────────────
        Schema::table('pinjaman', function (Blueprint $table) {
            $table->index(['anggota_id', 'status'],  'idx_pinjaman_anggota_status');
            $table->index(['status', 'created_at'],  'idx_pinjaman_status_created');
            $table->index('tanggal_pengajuan',        'idx_pinjaman_tgl_pengajuan');
        });

        // ─── angsuran ──────────────────────────────────────────────────────────
        Schema::table('angsuran', function (Blueprint $table) {
            $table->index(['status', 'tanggal_jatuh_tempo'], 'idx_angsuran_status_jatuh_tempo');
            $table->index(['pinjaman_id', 'status'],          'idx_angsuran_pinjaman_status');
        });

        // ─── pendapatan ────────────────────────────────────────────────────────
        Schema::table('pendapatan', function (Blueprint $table) {
            $table->index(['tanggal', 'kategori'], 'idx_pendapatan_tanggal_kategori');
        });

        // ─── pengeluaran ───────────────────────────────────────────────────────
        Schema::table('pengeluaran', function (Blueprint $table) {
            $table->index(['tanggal', 'kategori'], 'idx_pengeluaran_tanggal_kategori');
        });

        // ─── log_aktivitas ─────────────────────────────────────────────────────
        Schema::table('log_aktivitas', function (Blueprint $table) {
            $table->index(['modul', 'aksi', 'created_at'], 'idx_log_modul_aksi_created');
            $table->index(['user_id', 'created_at'],        'idx_log_user_created');
            $table->index('created_at',                     'idx_log_created_at');
        });

        // ─── users ─────────────────────────────────────────────────────────────
        Schema::table('users', function (Blueprint $table) {
            $table->index(['role', 'created_at'], 'idx_users_role_created');
        });
    }

    public function down(): void
    {
        Schema::table('anggota', function (Blueprint $table) {
            $table->dropIndex('idx_anggota_status_created');
            $table->dropIndex('idx_anggota_status_bergabung');
        });

        Schema::table('simpanan', function (Blueprint $table) {
            $table->dropIndex('idx_simpanan_anggota_jenis');
            $table->dropIndex('idx_simpanan_tanggal');
            $table->dropIndex('idx_simpanan_jenis_tanggal');
        });

        Schema::table('pinjaman', function (Blueprint $table) {
            $table->dropIndex('idx_pinjaman_anggota_status');
            $table->dropIndex('idx_pinjaman_status_created');
            $table->dropIndex('idx_pinjaman_tgl_pengajuan');
        });

        Schema::table('angsuran', function (Blueprint $table) {
            $table->dropIndex('idx_angsuran_status_jatuh_tempo');
            $table->dropIndex('idx_angsuran_pinjaman_status');
        });

        Schema::table('pendapatan', function (Blueprint $table) {
            $table->dropIndex('idx_pendapatan_tanggal_kategori');
        });

        Schema::table('pengeluaran', function (Blueprint $table) {
            $table->dropIndex('idx_pengeluaran_tanggal_kategori');
        });

        Schema::table('log_aktivitas', function (Blueprint $table) {
            $table->dropIndex('idx_log_modul_aksi_created');
            $table->dropIndex('idx_log_user_created');
            $table->dropIndex('idx_log_created_at');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_role_created');
        });
    }
};
