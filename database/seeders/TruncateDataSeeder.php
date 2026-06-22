<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TruncateDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $tabel = [
            'shu_anggota',
            'shu',
            'log_aktivitas',
            'angsuran',
            'pinjaman',
            'simpanan',
            'pendapatan',
            'pengeluaran',
            'anggota',
        ];

        foreach ($tabel as $t) {
            DB::table($t)->truncate();
            $this->command->info("Truncated: {$t}");
        }

        // Hapus user selain admin (berdasarkan role slug 'admin')
        $adminIds = DB::table('users')
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->where('roles.slug', 'admin')
            ->pluck('users.id');

        if ($adminIds->isEmpty()) {
            // Fallback: pertahankan user dengan email 'admin' atau role = 'admin'
            $adminIds = DB::table('users')
                ->where('role', 'admin')
                ->orWhere('email', 'like', '%admin%')
                ->pluck('id');
        }

        $deleted = DB::table('users')->whereNotIn('id', $adminIds)->delete();
        $this->command->info("Deleted {$deleted} non-admin users.");

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->command->info('Selesai. Semua data transaksi dihapus, admin dipertahankan.');
    }
}
