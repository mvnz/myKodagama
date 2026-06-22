<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_pencairan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('toko_id')->constrained('pos_toko')->cascadeOnDelete();
            $table->unsignedBigInteger('jumlah');
            $table->enum('metode', ['cash', 'transfer']);
            $table->string('nama_penerima', 100)->nullable();
            $table->string('nama_bank', 80)->nullable();
            $table->string('no_rekening', 50)->nullable();
            $table->text('keterangan')->nullable();
            $table->unsignedBigInteger('saldo_sebelum')->default(0);
            $table->unsignedBigInteger('saldo_sesudah')->default(0);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_pencairan');
    }
};
