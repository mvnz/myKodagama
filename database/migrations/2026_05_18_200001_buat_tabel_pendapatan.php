<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pendapatan', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('judul');
            $table->enum('kategori', [
                'jasa_admin', 'provisi', 'bunga_pinjaman', 'bunga_simpanan',
                'denda', 'hibah', 'iuran_anggota', 'lain_lain'
            ]);
            $table->decimal('jumlah', 15, 2);
            $table->text('keterangan')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pendapatan');
    }
};
