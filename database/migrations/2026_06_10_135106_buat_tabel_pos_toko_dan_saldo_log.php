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
        // ─── Profil Toko ─────────────────────────────────────────
        Schema::create('pos_toko', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 150);
            $table->string('pemilik', 100)->nullable();
            $table->string('telepon', 30)->nullable();
            $table->string('email', 100)->nullable();
            $table->text('alamat')->nullable();
            $table->string('kota', 100)->nullable();
            $table->text('deskripsi')->nullable();
            $table->string('logo', 255)->nullable();
            $table->bigInteger('saldo')->default(0)->comment('Akumulasi harga pokok dari transaksi');
            $table->timestamps();
        });

        // ─── Log Perubahan Saldo Toko ─────────────────────────────
        Schema::create('pos_saldo_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('toko_id')->constrained('pos_toko')->cascadeOnDelete();
            $table->foreignId('transaksi_id')->nullable()->constrained('pos_transaksi')->nullOnDelete();
            $table->bigInteger('jumlah');
            $table->bigInteger('saldo_sebelum');
            $table->bigInteger('saldo_sesudah');
            $table->string('keterangan', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // ─── Tambah harga_pokok ke pos_item ──────────────────────
        Schema::table('pos_item', function (Blueprint $table) {
            $table->bigInteger('harga_pokok')->default(0)->after('harga');
        });
    }

    public function down(): void
    {
        Schema::table('pos_item', function (Blueprint $table) {
            $table->dropColumn('harga_pokok');
        });
        Schema::dropIfExists('pos_saldo_log');
        Schema::dropIfExists('pos_toko');
    }
};
