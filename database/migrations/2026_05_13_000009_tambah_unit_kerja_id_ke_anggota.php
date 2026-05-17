<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('anggota', function (Blueprint $table) {
            $table->foreignId('unit_kerja_id')->nullable()->after('pekerjaan')
                ->constrained('unit_kerja')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('anggota', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\UnitKerja::class);
            $table->dropColumn('unit_kerja_id');
        });
    }
};
