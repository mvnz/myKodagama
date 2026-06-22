<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('simpanan', function (Blueprint $table) {
            $table->string('jenis_anggota', 100)->nullable()->after('anggota_id');
        });

        // Backfill dari data anggota saat ini (best-effort untuk data lama)
        DB::table('anggota')->get()->each(function ($anggota) {
            DB::table('simpanan')
                ->where('anggota_id', $anggota->id)
                ->update(['jenis_anggota' => $anggota->jenis_anggota]);
        });
    }

    public function down(): void
    {
        Schema::table('simpanan', function (Blueprint $table) {
            $table->dropColumn('jenis_anggota');
        });
    }
};
