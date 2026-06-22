<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Koperasi\DashboardController;
use App\Http\Controllers\Koperasi\AnggotaController;
use App\Http\Controllers\Koperasi\SimpananController;
use App\Http\Controllers\Koperasi\PinjamanController;
use App\Http\Controllers\Koperasi\AngsuranController;
use App\Http\Controllers\Koperasi\RoleController;
use App\Http\Controllers\Koperasi\PengaturanController;
use App\Http\Controllers\Koperasi\LogAktivitasController;
use App\Http\Controllers\Koperasi\LaporanController;
use App\Http\Controllers\Koperasi\MonitoringController;
use App\Http\Controllers\Koperasi\UnitKerjaController;
use App\Http\Controllers\Koperasi\ExportController;
use App\Http\Controllers\Koperasi\ShuController;
use App\Http\Controllers\Koperasi\PengeluaranController;
use App\Http\Controllers\Koperasi\UserController;
use App\Http\Controllers\Koperasi\ProfilController;
use App\Http\Controllers\Koperasi\PendapatanController;
use App\Http\Controllers\Koperasi\RekonController;
use App\Http\Controllers\Koperasi\RekonAngsuranController;
use App\Http\Controllers\Pos\KasirController;
use App\Http\Controllers\Pos\ProdukController as PosProdukController;
use App\Http\Controllers\Pos\KategoriController as PosKategoriController;
use App\Http\Controllers\Pos\RiwayatController as PosRiwayatController;
use App\Http\Controllers\Pos\TokoController as PosTokoController;

// Halaman publik
Route::get('/', function () {
    $pengaturan = \App\Models\Koperasi\Pengaturan::semuaNilai();
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
    Route::get('/home', [DashboardController::class, 'home'])->name('home');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/struktur-organisasi', [DashboardController::class, 'strukturOrganisasi'])->name('struktur-organisasi');
    Route::get('/bantuan', fn() => view('koperasi.bantuan.index'))->name('bantuan');

    // Anggota
    Route::get('/anggota/cetak', [AnggotaController::class, 'cetak'])->name('anggota.cetak');
    Route::resource('anggota', AnggotaController::class)->parameters(['anggota' => 'anggota']);
    Route::post('/anggota/generate-akun-login', [AnggotaController::class, 'generateAkunLoginMassal'])->name('anggota.generate-akun-login');
    Route::post('/anggota/{anggota}/setujui', [AnggotaController::class, 'setujui'])->name('anggota.setujui');
    Route::delete('/anggota/{anggota}/tolak', [AnggotaController::class, 'tolak'])->name('anggota.tolak');
    Route::get('/anggota/{anggota}/rekap', [AnggotaController::class, 'rekap'])->name('anggota.rekap');
    Route::get('/anggota/{anggota}/kartu', [AnggotaController::class, 'kartu'])->name('anggota.kartu');

    // Unit Kerja
    Route::resource('unit-kerja', UnitKerjaController::class)->except(['show'])
        ->parameters(['unit-kerja' => 'unitKerja']);

    // Simpanan
    Route::get('/simpanan/cetak', [SimpananController::class, 'cetak'])->name('simpanan.cetak');
    Route::resource('simpanan', SimpananController::class);
    Route::get('/simpanan/tarik/{anggota}', [SimpananController::class, 'tarikForm'])->name('simpanan.tarik.form');
    Route::post('/simpanan/tarik/{anggota}', [SimpananController::class, 'tarik'])->name('simpanan.tarik');

    // Rekonsiliasi Simpanan
    Route::get('/rekon', [RekonController::class, 'index'])->name('rekon.index');
    Route::get('/rekon/template', [RekonController::class, 'template'])->name('rekon.template');
    Route::post('/rekon/preview', [RekonController::class, 'preview'])->name('rekon.preview');
    Route::post('/rekon/import', [RekonController::class, 'import'])->name('rekon.import');

    // Rekonsiliasi Angsuran
    Route::get('/rekon-angsuran', [RekonAngsuranController::class, 'index'])->name('rekon-angsuran.index');
    Route::get('/rekon-angsuran/template', [RekonAngsuranController::class, 'template'])->name('rekon-angsuran.template');
    Route::post('/rekon-angsuran/preview', [RekonAngsuranController::class, 'preview'])->name('rekon-angsuran.preview');
    Route::post('/rekon-angsuran/import', [RekonAngsuranController::class, 'import'])->name('rekon-angsuran.import');

    // Pinjaman
    Route::get('/pinjaman/cetak', [PinjamanController::class, 'cetak'])->name('pinjaman.cetak');
    Route::resource('pinjaman', PinjamanController::class);
    Route::post('/pinjaman/{pinjaman}/verifikasi', [PinjamanController::class, 'verifikasi'])->name('pinjaman.verifikasi');
    Route::post('/pinjaman/{pinjaman}/setujui',    [PinjamanController::class, 'setujui'])->name('pinjaman.setujui');
    Route::post('/pinjaman/{pinjaman}/tolak',      [PinjamanController::class, 'tolak'])->name('pinjaman.tolak');

    // Angsuran
    Route::get('/angsuran/cetak', [AngsuranController::class, 'cetak'])->name('angsuran.cetak');
    Route::resource('angsuran', AngsuranController::class)->except(['create', 'store', 'edit', 'update', 'destroy']);
    Route::post('/angsuran/{angsuran}/bayar', [AngsuranController::class, 'bayar'])->name('angsuran.bayar');

    // Pengaturan Role
    Route::resource('role', RoleController::class);

    // Pengaturan Aplikasi
    Route::get('/pengaturan/aplikasi', [PengaturanController::class, 'index'])->name('pengaturan.aplikasi');
    Route::put('/pengaturan/aplikasi', [PengaturanController::class, 'update'])->name('pengaturan.aplikasi.update');

    // Pengaturan Simpan Pinjam
    Route::get('/pengaturan/simpan-pinjam', [PengaturanController::class, 'simpanPinjam'])->name('pengaturan.simpan-pinjam');
    Route::put('/pengaturan/simpan-pinjam', [PengaturanController::class, 'updateSimpanPinjam'])->name('pengaturan.simpan-pinjam.update');

    // Pengaturan Alur Persetujuan Pinjaman
    Route::get('/pengaturan/alur-pinjaman', [PengaturanController::class, 'alurPinjaman'])->name('pengaturan.alur-pinjaman');
    Route::put('/pengaturan/alur-pinjaman', [PengaturanController::class, 'updateAlurPinjaman'])->name('pengaturan.alur-pinjaman.update');

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
    Route::get('/laporan/laba-rugi', [LaporanController::class, 'labaRugi'])->name('laporan.laba-rugi');
    Route::get('/laporan/lpj', [LaporanController::class, 'lpj'])->name('laporan.lpj');
    Route::get('/laporan/lpj/cetak', [LaporanController::class, 'cetakLpj'])->name('laporan.lpj.cetak');

    // Monitoring
    Route::get('/monitoring/simpanan', [MonitoringController::class, 'simpanan'])->name('monitoring.simpanan');
    Route::get('/monitoring/pinjaman', [MonitoringController::class, 'pinjaman'])->name('monitoring.pinjaman');

    // Export CSV
    Route::get('/export/anggota',       [ExportController::class, 'anggota'])->name('export.anggota');
    Route::get('/export/simpanan',       [ExportController::class, 'simpanan'])->name('export.simpanan');
    Route::get('/export/pinjaman',       [ExportController::class, 'pinjaman'])->name('export.pinjaman');
    Route::get('/export/angsuran',       [ExportController::class, 'angsuran'])->name('export.angsuran');
    Route::get('/export/pengeluaran',    [ExportController::class, 'pengeluaran'])->name('export.pengeluaran');

    // Import CSV
    Route::get('/import/anggota',           [AnggotaController::class, 'importForm'])->name('import.anggota.form');
    Route::post('/import/anggota',          [AnggotaController::class, 'import'])->name('import.anggota');
    Route::get('/export/template-anggota',  [ExportController::class, 'templateAnggota'])->name('export.template.anggota');

    Route::get('/import/simpanan',          [SimpananController::class, 'importForm'])->name('import.simpanan.form');
    Route::post('/import/simpanan',         [SimpananController::class, 'import'])->name('import.simpanan');
    Route::get('/export/template-simpanan', [ExportController::class, 'templateSimpanan'])->name('export.template.simpanan');

    Route::get('/import/unit-kerja',           [UnitKerjaController::class, 'importForm'])->name('import.unit-kerja.form');
    Route::post('/import/unit-kerja',          [UnitKerjaController::class, 'import'])->name('import.unit-kerja');
    Route::get('/export/template-unit-kerja',  [ExportController::class, 'templateUnitKerja'])->name('export.template.unit-kerja');

    // Pengeluaran
    Route::get('/pengeluaran/cetak', [PengeluaranController::class, 'cetak'])->name('pengeluaran.cetak');
    Route::resource('pengeluaran', PengeluaranController::class)->except(['index']);
    Route::get('/pengeluaran', [PengeluaranController::class, 'index'])->name('pengeluaran.index');

    // Pendapatan
    Route::get('/pendapatan/cetak', [PendapatanController::class, 'cetak'])->name('pendapatan.cetak');
    Route::resource('pendapatan', PendapatanController::class)->except(['index']);
    Route::get('/pendapatan', [PendapatanController::class, 'index'])->name('pendapatan.index');

    // Manajemen User
    Route::resource('user', UserController::class)->except(['index']);
    Route::get('/user', [UserController::class, 'index'])->name('user.index');

    // Profil
    Route::get('/profil', [ProfilController::class, 'show'])->name('profil');
    Route::put('/profil', [ProfilController::class, 'update'])->name('profil.update');
    Route::put('/profil/password', [ProfilController::class, 'gantiPassword'])->name('profil.password');

    // SHU
    Route::get('/shu', [ShuController::class, 'index'])->name('shu.index');
    Route::get('/shu/hitung', [ShuController::class, 'hitung'])->name('shu.hitung');
    Route::post('/shu', [ShuController::class, 'store'])->name('shu.store');
    Route::get('/shu/{shu}', [ShuController::class, 'show'])->name('shu.show');
    Route::post('/shu/{shu}/finalisasi', [ShuController::class, 'finalisasi'])->name('shu.finalisasi');
    Route::get('/shu/{shu}/cetak', [ShuController::class, 'cetak'])->name('shu.cetak');
    Route::delete('/shu/{shu}', [ShuController::class, 'destroy'])->name('shu.destroy');

    // ─── POS ─────────────────────────────────────────────────
    Route::prefix('pos')->name('pos.')->group(function () {
        // Kasir
        Route::middleware('can:pos.kasir')->group(function () {
            Route::get('/',          [KasirController::class, 'index'])->name('kasir');
            Route::post('/checkout', [KasirController::class, 'checkout'])->name('checkout');
        });

        // Riwayat & Laporan
        Route::middleware('can:pos.riwayat')->group(function () {
            Route::get('/struk/{transaksi}', [PosRiwayatController::class, 'struk'])->name('struk');
            Route::get('/riwayat',            [PosRiwayatController::class, 'index'])->name('riwayat.index');
            Route::get('/riwayat/{transaksi}', [PosRiwayatController::class, 'show'])->name('riwayat.show');
            Route::get('/laporan',            [PosRiwayatController::class, 'laporan'])->name('laporan');
            Route::get('/laporan/cetak',      [PosRiwayatController::class, 'cetakLaporan'])->name('laporan.cetak');
        });

        // Produk & Kategori
        Route::middleware('can:pos.produk')->group(function () {
            Route::get('/produk',              [PosProdukController::class, 'index'])->name('produk.index');
            Route::get('/produk/baru',         [PosProdukController::class, 'create'])->name('produk.create');
            Route::post('/produk',             [PosProdukController::class, 'store'])->name('produk.store');
            Route::post('/produk/stok-scan',   [PosProdukController::class, 'stockInByScanner'])->name('produk.stok-scan');
            Route::get('/produk/{produk}/edit',[PosProdukController::class, 'edit'])->name('produk.edit');
            Route::put('/produk/{produk}',     [PosProdukController::class, 'update'])->name('produk.update');
            Route::delete('/produk/{produk}',  [PosProdukController::class, 'destroy'])->name('produk.destroy');

            Route::get('/kategori',              [PosKategoriController::class, 'index'])->name('kategori.index');
            Route::post('/kategori',             [PosKategoriController::class, 'store'])->name('kategori.store');
            Route::put('/kategori/{kategori}',   [PosKategoriController::class, 'update'])->name('kategori.update');
            Route::delete('/kategori/{kategori}',[PosKategoriController::class, 'destroy'])->name('kategori.destroy');
        });

        // Supplier (Toko)
        Route::middleware('can:pos.toko')->group(function () {
            Route::get('/toko',              [PosTokoController::class, 'index'])->name('toko.index');
            Route::get('/toko/baru',         [PosTokoController::class, 'create'])->name('toko.create');
            Route::post('/toko',             [PosTokoController::class, 'store'])->name('toko.store');
            Route::get('/toko/{toko}',       [PosTokoController::class, 'show'])->name('toko.show');
            Route::get('/toko/{toko}/edit',  [PosTokoController::class, 'edit'])->name('toko.edit');
            Route::put('/toko/{toko}',       [PosTokoController::class, 'update'])->name('toko.update');
            Route::delete('/toko/{toko}',    [PosTokoController::class, 'destroy'])->name('toko.destroy');
            Route::post('/toko/{toko}/cairkan', [PosTokoController::class, 'cairkan'])->name('toko.cairkan');
        });
    });
});
