<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah nilai 'menunggu' ke enum status anggota (MySQL only)
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE anggota MODIFY COLUMN status ENUM('aktif', 'tidak_aktif', 'menunggu') NOT NULL DEFAULT 'aktif'");
        }

        Schema::table('anggota', function (Blueprint $table) {
            // Buat kolom wajib menjadi nullable agar bisa diisi saat registrasi mandiri
            $table->string('nik', 16)->nullable()->change();
            $table->string('tempat_lahir')->nullable()->change();
            $table->date('tanggal_lahir')->nullable()->change();
            $table->string('alamat')->nullable()->change();
            $table->string('pekerjaan')->nullable()->change();
        });

        // Jenis kelamin enum terpisah karena butuh MODIFY dengan tipe enum (MySQL only)
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE anggota MODIFY COLUMN jenis_kelamin ENUM('Laki-laki','Perempuan') NULL DEFAULT NULL");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE anggota MODIFY COLUMN status ENUM('aktif', 'tidak_aktif') NOT NULL DEFAULT 'aktif'");
        }

        Schema::table('anggota', function (Blueprint $table) {
            $table->string('nik', 16)->nullable(false)->change();
            $table->string('tempat_lahir')->nullable(false)->change();
            $table->date('tanggal_lahir')->nullable(false)->change();
            $table->string('alamat')->nullable(false)->change();
            $table->string('pekerjaan')->nullable(false)->change();
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE anggota MODIFY COLUMN jenis_kelamin ENUM('Laki-laki','Perempuan') NOT NULL");
        }
    }
};
