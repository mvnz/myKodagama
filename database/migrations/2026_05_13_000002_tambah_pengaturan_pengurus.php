<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $rows = [
            // Ketua
            ['kunci' => 'pgrs_ketua_nama',    'nilai' => '',  'grup' => 'pengurus', 'label' => 'Nama Ketua',             'tipe' => 'text'],
            ['kunci' => 'pgrs_ketua_periode', 'nilai' => '',  'grup' => 'pengurus', 'label' => 'Periode Jabatan Ketua',  'tipe' => 'text'],
            // Sekretaris
            ['kunci' => 'pgrs_sekretaris_nama',    'nilai' => '', 'grup' => 'pengurus', 'label' => 'Nama Sekretaris',            'tipe' => 'text'],
            ['kunci' => 'pgrs_sekretaris_periode', 'nilai' => '', 'grup' => 'pengurus', 'label' => 'Periode Jabatan Sekretaris', 'tipe' => 'text'],
            // Bendahara
            ['kunci' => 'pgrs_bendahara_nama',    'nilai' => '', 'grup' => 'pengurus', 'label' => 'Nama Bendahara',            'tipe' => 'text'],
            ['kunci' => 'pgrs_bendahara_periode', 'nilai' => '', 'grup' => 'pengurus', 'label' => 'Periode Jabatan Bendahara', 'tipe' => 'text'],
            // Pengawas
            ['kunci' => 'pgrs_pengawas1_nama',   'nilai' => '', 'grup' => 'pengurus', 'label' => 'Nama Pengawas 1',    'tipe' => 'text'],
            ['kunci' => 'pgrs_pengawas2_nama',   'nilai' => '', 'grup' => 'pengurus', 'label' => 'Nama Pengawas 2',    'tipe' => 'text'],
            ['kunci' => 'pgrs_pengawas3_nama',   'nilai' => '', 'grup' => 'pengurus', 'label' => 'Nama Pengawas 3',    'tipe' => 'text'],
            // Pengesahan
            ['kunci' => 'pgrs_sk_nomor',  'nilai' => '', 'grup' => 'pengurus', 'label' => 'Nomor SK Pengurus',     'tipe' => 'text'],
            ['kunci' => 'pgrs_sk_tanggal','nilai' => '', 'grup' => 'pengurus', 'label' => 'Tanggal SK Pengurus',   'tipe' => 'date'],
        ];

        $now = now();
        foreach ($rows as &$row) {
            $row['created_at'] = $now;
            $row['updated_at'] = $now;
        }

        DB::table('pengaturan')->insertOrIgnore($rows);
    }

    public function down(): void
    {
        DB::table('pengaturan')->whereIn('kunci', [
            'pgrs_ketua_nama', 'pgrs_ketua_periode',
            'pgrs_sekretaris_nama', 'pgrs_sekretaris_periode',
            'pgrs_bendahara_nama', 'pgrs_bendahara_periode',
            'pgrs_pengawas1_nama', 'pgrs_pengawas2_nama', 'pgrs_pengawas3_nama',
            'pgrs_sk_nomor', 'pgrs_sk_tanggal',
        ])->delete();
    }
};
