<?php

if (!function_exists('grupIkon')) {
    function grupIkon(string $grup): string
    {
        return match ($grup) {
            'Dashboard'       => 'bi-grid-fill',
            'Anggota'         => 'bi-people-fill',
            'Simpanan'        => 'bi-piggy-bank-fill',
            'Pinjaman'        => 'bi-cash-stack',
            'Angsuran'        => 'bi-calendar-check-fill',
            'Pengeluaran'     => 'bi-arrow-down-circle-fill',
            'Pendapatan'      => 'bi-arrow-up-circle-fill',
            'SHU'             => 'bi-pie-chart-fill',
            'Laporan'         => 'bi-file-earmark-bar-graph-fill',
            'Monitoring'      => 'bi-bar-chart-fill',
            'Unit Kerja'      => 'bi-building-fill',
            'Export & Import' => 'bi-arrow-left-right',
            'Pengguna'        => 'bi-person-gear',
            'Pengaturan'      => 'bi-gear-fill',
            'Log'             => 'bi-journal-text',
            'POS'             => 'bi-shop',
            'pos'             => 'bi-shop',
            default           => 'bi-lock-fill',
        };
    }
}
