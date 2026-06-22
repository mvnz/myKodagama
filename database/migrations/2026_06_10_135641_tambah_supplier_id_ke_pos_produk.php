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
        Schema::table('pos_produk', function (Blueprint $table) {
            $table->foreignId('supplier_id')->nullable()->after('kategori_id')
                  ->constrained('pos_toko')->nullOnDelete();
        });

        // Rename kolom keterangan di pos_saldo_log agar lebih deskriptif
        // (tidak perlu perubahan skema, cukup model)
    }

    public function down(): void
    {
        Schema::table('pos_produk', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropColumn('supplier_id');
        });
    }
};
