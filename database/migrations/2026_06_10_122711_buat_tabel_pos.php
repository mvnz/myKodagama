<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Kategori produk
        Schema::create('pos_kategori', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->string('slug', 110)->unique();
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });

        // Produk
        Schema::create('pos_produk', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_id')->nullable()->constrained('pos_kategori')->nullOnDelete();
            $table->string('kode', 50)->unique();
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->unsignedBigInteger('harga_beli')->default(0);
            $table->unsignedBigInteger('harga_jual');
            $table->integer('stok')->default(0);
            $table->string('satuan', 20)->default('pcs');
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });

        // Transaksi penjualan
        Schema::create('pos_transaksi', function (Blueprint $table) {
            $table->id();
            $table->string('nomor', 30)->unique();
            $table->foreignId('kasir_id')->constrained('users');
            $table->unsignedBigInteger('subtotal')->default(0);
            $table->unsignedBigInteger('diskon')->default(0);
            $table->unsignedBigInteger('total')->default(0);
            $table->unsignedBigInteger('bayar')->default(0);
            $table->unsignedBigInteger('kembalian')->default(0);
            $table->enum('metode_bayar', ['tunai', 'transfer', 'qris'])->default('tunai');
            $table->text('catatan')->nullable();
            $table->date('tanggal');
            $table->timestamps();

            $table->index('tanggal');
            $table->index('kasir_id');
        });

        // Item transaksi
        Schema::create('pos_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaksi_id')->constrained('pos_transaksi')->cascadeOnDelete();
            $table->foreignId('produk_id')->nullable()->constrained('pos_produk')->nullOnDelete();
            $table->string('nama_produk');
            $table->string('kode_produk', 50);
            $table->unsignedBigInteger('harga');
            $table->integer('qty');
            $table->unsignedBigInteger('subtotal');
        });

        // Permissions POS
        DB::table('permissions')->insert([
            ['slug' => 'pos.kasir',   'nama' => 'Akses Kasir POS',          'grup' => 'pos'],
            ['slug' => 'pos.produk',  'nama' => 'Kelola Produk POS',         'grup' => 'pos'],
            ['slug' => 'pos.riwayat', 'nama' => 'Riwayat Transaksi POS',     'grup' => 'pos'],
        ]);
    }

    public function down(): void
    {
        DB::table('permissions')->whereIn('slug', ['pos.kasir', 'pos.produk', 'pos.riwayat'])->delete();
        Schema::dropIfExists('pos_item');
        Schema::dropIfExists('pos_transaksi');
        Schema::dropIfExists('pos_produk');
        Schema::dropIfExists('pos_kategori');
    }
};
