<?php

if (!function_exists('grupIkon')) {
    function grupIkon(string $grup): string
    {
        return match ($grup) {
            'Anggota'    => 'bi-people-fill',
            'Simpanan'   => 'bi-piggy-bank-fill',
            'Pinjaman'   => 'bi-cash-stack',
            'Angsuran'   => 'bi-calendar-check-fill',
            'Pengguna'   => 'bi-person-gear',
            'Dashboard'  => 'bi-grid-fill',
            'Pengaturan' => 'bi-gear-fill',
            default      => 'bi-lock-fill',
        };
    }
}
