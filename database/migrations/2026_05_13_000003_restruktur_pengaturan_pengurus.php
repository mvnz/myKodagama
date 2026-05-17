<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Hapus key lama yang diganti JSON
        DB::table('pengaturan')->whereIn('kunci', [
            'pgrs_sekretaris_nama',
            'pgrs_sekretaris_periode',
            'pgrs_bendahara_nama',
            'pgrs_bendahara_periode',
        ])->delete();

        // Tambah key baru
        DB::table('pengaturan')->insertOrIgnore([
            ['kunci' => 'pgrs_wakil_nama',    'nilai' => '', 'grup' => 'pengurus', 'label' => 'Nama Wakil Ketua',  'tipe' => 'text'],
            ['kunci' => 'pgrs_wakil_periode',  'nilai' => '', 'grup' => 'pengurus', 'label' => 'Periode Wakil',     'tipe' => 'text'],
            ['kunci' => 'pgrs_sekretaris',     'nilai' => '[]', 'grup' => 'pengurus', 'label' => 'Sekretaris',     'tipe' => 'json'],
            ['kunci' => 'pgrs_bendahara',      'nilai' => '[]', 'grup' => 'pengurus', 'label' => 'Bendahara',      'tipe' => 'json'],
        ]);
    }

    public function down(): void
    {
        DB::table('pengaturan')->whereIn('kunci', [
            'pgrs_wakil_nama',
            'pgrs_wakil_periode',
            'pgrs_sekretaris',
            'pgrs_bendahara',
        ])->delete();

        DB::table('pengaturan')->insertOrIgnore([
            ['kunci' => 'pgrs_sekretaris_nama',    'nilai' => '', 'grup' => 'pengurus', 'label' => 'Nama Sekretaris',        'tipe' => 'text'],
            ['kunci' => 'pgrs_sekretaris_periode', 'nilai' => '', 'grup' => 'pengurus', 'label' => 'Periode Sekretaris',     'tipe' => 'text'],
            ['kunci' => 'pgrs_bendahara_nama',     'nilai' => '', 'grup' => 'pengurus', 'label' => 'Nama Bendahara',         'tipe' => 'text'],
            ['kunci' => 'pgrs_bendahara_periode',  'nilai' => '', 'grup' => 'pengurus', 'label' => 'Periode Bendahara',      'tipe' => 'text'],
        ]);
    }
};
