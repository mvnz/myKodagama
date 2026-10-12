<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('backup:run --auto')
    ->everyMinute()
    ->when(function () {
        try {
            return \App\Models\Koperasi\Pengaturan::get('backup_waktu', '02:00') === now()->format('H:i');
        } catch (\Throwable) {
            return false;
        }
    })
    ->withoutOverlapping();
