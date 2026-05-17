<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pinjaman', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_pinjaman')->unique();
            $table->foreignId('anggota_id')->constrained('anggota')->onDelete('cascade');
            $table->decimal('jumlah_pinjaman', 15, 2);
            $table->integer('jangka_waktu');
            $table->decimal('bunga', 5, 2)->default(1.5);
            $table->decimal('cicilan_per_bulan', 15, 2)->nullable();
            $table->text('tujuan');
            $table->enum('status', ['diajukan', 'disetujui', 'ditolak', 'lunas'])->default('diajukan');
            $table->date('tanggal_pengajuan');
            $table->date('tanggal_persetujuan')->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pinjaman');
    }
};
