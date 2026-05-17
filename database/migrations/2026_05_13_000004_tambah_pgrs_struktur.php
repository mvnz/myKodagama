<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('pengaturan')->insertOrIgnore([
            [
                'kunci'      => 'pgrs_struktur',
                'nilai'      => '[]',
                'grup'       => 'pengurus',
                'label'      => 'Struktur Pengurus',
                'tipe'       => 'json',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        DB::table('pengaturan')->where('kunci', 'pgrs_struktur')->delete();
    }
};
