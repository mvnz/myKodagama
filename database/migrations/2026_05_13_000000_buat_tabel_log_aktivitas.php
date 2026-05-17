<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_aktivitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('aksi', 20);           // tambah | ubah | hapus | bayar | setujui | tolak
            $table->string('modul', 50);           // Anggota | Simpanan | Pinjaman | Angsuran | Role | Pengaturan
            $table->unsignedBigInteger('model_id')->nullable();
            $table->string('label', 255)->nullable(); // nama / nomor yang mudah dibaca manusia
            $table->text('keterangan')->nullable();
            $table->json('data_lama')->nullable();
            $table->json('data_baru')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_aktivitas');
    }
};
