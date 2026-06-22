<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_produk', function (Blueprint $table) {
            $table->string('barcode', 100)->nullable()->unique()->after('kode');
        });
    }

    public function down(): void
    {
        Schema::table('pos_produk', function (Blueprint $table) {
            $table->dropUnique('pos_produk_barcode_unique');
            $table->dropColumn('barcode');
        });
    }
};
