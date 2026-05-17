<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $rows = [
            // Simpanan
            ['kunci' => 'sp_simpanan_pokok',         'nilai' => '100000', 'grup' => 'simpan_pinjam', 'label' => 'Nominal Simpanan Pokok',             'tipe' => 'number'],
            ['kunci' => 'sp_simpanan_wajib',          'nilai' => '50000',  'grup' => 'simpan_pinjam', 'label' => 'Nominal Simpanan Wajib per Bulan',   'tipe' => 'number'],
            ['kunci' => 'sp_bunga_simpanan',          'nilai' => '5',      'grup' => 'simpan_pinjam', 'label' => 'Bunga Simpanan Sukarela (% / tahun)', 'tipe' => 'number'],
            // Pinjaman
            ['kunci' => 'sp_bunga_pinjaman',          'nilai' => '1.5',    'grup' => 'simpan_pinjam', 'label' => 'Bunga Pinjaman (% / bulan)',          'tipe' => 'number'],
            ['kunci' => 'sp_min_pinjaman',            'nilai' => '500000', 'grup' => 'simpan_pinjam', 'label' => 'Minimum Jumlah Pinjaman (Rp)',        'tipe' => 'number'],
            ['kunci' => 'sp_maks_pinjaman',           'nilai' => '50000000','grup' => 'simpan_pinjam', 'label' => 'Maksimum Jumlah Pinjaman (Rp)',      'tipe' => 'number'],
            ['kunci' => 'sp_min_jangka',              'nilai' => '3',      'grup' => 'simpan_pinjam', 'label' => 'Minimum Jangka Waktu (bulan)',        'tipe' => 'number'],
            ['kunci' => 'sp_maks_jangka',             'nilai' => '36',     'grup' => 'simpan_pinjam', 'label' => 'Maksimum Jangka Waktu (bulan)',       'tipe' => 'number'],
            ['kunci' => 'sp_denda_keterlambatan',     'nilai' => '0.1',    'grup' => 'simpan_pinjam', 'label' => 'Denda Keterlambatan (% / hari)',      'tipe' => 'number'],
            ['kunci' => 'sp_batas_kelipatan_pinjaman','nilai' => '5',      'grup' => 'simpan_pinjam', 'label' => 'Batas Kelipatan Pinjaman dari Simpanan', 'tipe' => 'number'],
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
            'sp_simpanan_pokok',
            'sp_simpanan_wajib',
            'sp_bunga_simpanan',
            'sp_bunga_pinjaman',
            'sp_min_pinjaman',
            'sp_maks_pinjaman',
            'sp_min_jangka',
            'sp_maks_jangka',
            'sp_denda_keterlambatan',
            'sp_batas_kelipatan_pinjaman',
        ])->delete();
    }
};
