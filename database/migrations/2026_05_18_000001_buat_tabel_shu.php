<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shu', function (Blueprint $table) {
            $table->id();
            $table->year('tahun')->unique();
            $table->decimal('total_pendapatan', 15, 2)->default(0);
            $table->decimal('total_biaya', 15, 2)->default(0);
            $table->decimal('total_shu', 15, 2)->default(0);
            $table->decimal('persentase_jasa_modal', 5, 2)->default(30); // % dari total SHU untuk jasa modal
            $table->decimal('persentase_jasa_usaha', 5, 2)->default(40); // % dari total SHU untuk jasa usaha
            $table->enum('status', ['draft', 'final'])->default('draft');
            $table->text('keterangan')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('shu_anggota', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shu_id')->constrained('shu')->cascadeOnDelete();
            $table->foreignId('anggota_id')->constrained('anggota')->cascadeOnDelete();
            $table->decimal('total_simpanan', 15, 2)->default(0);
            $table->decimal('total_pinjaman', 15, 2)->default(0);
            $table->decimal('jasa_modal', 15, 2)->default(0);
            $table->decimal('jasa_usaha', 15, 2)->default(0);
            $table->decimal('total_shu_anggota', 15, 2)->default(0);
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['shu_id', 'anggota_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shu_anggota');
        Schema::dropIfExists('shu');
    }
};
