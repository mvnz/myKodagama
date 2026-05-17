<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AnggotaController;
use App\Http\Controllers\SimpananController;
use App\Http\Controllers\PinjamanController;
use App\Http\Controllers\AngsuranController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\LogAktivitasController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\UnitKerjaController;

// Halaman publik
Route::get('/', function () {
    $pengaturan = \App\Models\Pengaturan::semuaNilai();
    return view('beranda.index', compact('pengaturan'));
})->name('beranda');

// Autentikasi
Route::get('/masuk', [AuthController::class, 'tampilMasuk'])->name('masuk');
Route::post('/masuk', [AuthController::class, 'masuk'])->name('masuk.proses');
Route::post('/keluar', [AuthController::class, 'keluar'])->name('keluar');
Route::get('/daftar', [AuthController::class, 'tampilDaftar'])->name('daftar');
Route::post('/daftar', [AuthController::class, 'daftar'])->name('daftar.proses');

// Halaman yang membutuhkan login
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/struktur-organisasi', [DashboardController::class, 'strukturOrganisasi'])->name('struktur-organisasi');

    // Anggota
    Route::resource('anggota', AnggotaController::class)->parameters(['anggota' => 'anggota']);
    Route::post('/anggota/{anggota}/setujui', [AnggotaController::class, 'setujui'])->name('anggota.setujui');
    Route::delete('/anggota/{anggota}/tolak', [AnggotaController::class, 'tolak'])->name('anggota.tolak');
    Route::get('/anggota/{anggota}/rekap', [AnggotaController::class, 'rekap'])->name('anggota.rekap');

    // Unit Kerja
    Route::resource('unit-kerja', UnitKerjaController::class)->except(['show'])
        ->parameters(['unit-kerja' => 'unitKerja']);

    // Simpanan
    Route::resource('simpanan', SimpananController::class);

    // Pinjaman
    Route::resource('pinjaman', PinjamanController::class);
    Route::post('/pinjaman/{pinjaman}/verifikasi', [PinjamanController::class, 'verifikasi'])->name('pinjaman.verifikasi');
    Route::post('/pinjaman/{pinjaman}/setujui',    [PinjamanController::class, 'setujui'])->name('pinjaman.setujui');
    Route::post('/pinjaman/{pinjaman}/tolak',      [PinjamanController::class, 'tolak'])->name('pinjaman.tolak');

    // Angsuran
    Route::resource('angsuran', AngsuranController::class)->except(['create', 'store', 'edit', 'update', 'destroy']);
    Route::post('/angsuran/{angsuran}/bayar', [AngsuranController::class, 'bayar'])->name('angsuran.bayar');

    // Pengaturan Role
    Route::resource('role', RoleController::class);

    // Pengaturan Aplikasi
    Route::get('/pengaturan/aplikasi', [PengaturanController::class, 'index'])->name('pengaturan.aplikasi');
    Route::put('/pengaturan/aplikasi', [PengaturanController::class, 'update'])->name('pengaturan.update');

    // Pengaturan Simpan Pinjam
    Route::get('/pengaturan/simpan-pinjam', [PengaturanController::class, 'simpanPinjam'])->name('pengaturan.simpan-pinjam');
    Route::put('/pengaturan/simpan-pinjam', [PengaturanController::class, 'updateSimpanPinjam'])->name('pengaturan.simpan-pinjam.update');

    // Pengaturan Pengurus
    Route::get('/pengaturan/pengurus', [PengaturanController::class, 'pengurus'])->name('pengaturan.pengurus');
    Route::put('/pengaturan/pengurus', [PengaturanController::class, 'updatePengurus'])->name('pengaturan.pengurus.update');

    // Pengaturan Notifikasi WhatsApp
    Route::get('/pengaturan/notifikasi-wa', [PengaturanController::class, 'notifikasiWa'])->name('pengaturan.notifikasi-wa');
    Route::put('/pengaturan/notifikasi-wa', [PengaturanController::class, 'updateNotifikasiWa'])->name('pengaturan.notifikasi-wa.update');
    Route::post('/pengaturan/notifikasi-wa/test', [PengaturanController::class, 'testNotifikasiWa'])->name('pengaturan.notifikasi-wa.test');

    // Log Aktivitas
    Route::get('/log', [LogAktivitasController::class, 'index'])->name('log.index');
    Route::get('/log/{log}', [LogAktivitasController::class, 'show'])->name('log.show');

    // Laporan Keuangan
    Route::get('/laporan/keuangan', [LaporanController::class, 'index'])->name('laporan.keuangan');
    Route::get('/laporan/keuangan/cetak', [LaporanController::class, 'cetak'])->name('laporan.keuangan.cetak');

    // Monitoring
    Route::get('/monitoring/simpanan', [MonitoringController::class, 'simpanan'])->name('monitoring.simpanan');
    Route::get('/monitoring/pinjaman', [MonitoringController::class, 'pinjaman'])->name('monitoring.pinjaman');
});
