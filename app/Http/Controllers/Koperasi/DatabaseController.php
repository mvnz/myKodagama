<?php

namespace App\Http\Controllers\Koperasi;

use App\Http\Controllers\Concerns\CatatAktivitas;
use App\Http\Controllers\Controller;
use App\Models\Koperasi\Pengaturan;
use App\Support\DatabaseBackup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\View\View;

class DatabaseController extends Controller
{
    use CatatAktivitas;

    public function index(): View
    {
        $terakhir = explode('|', (string) Pengaturan::get('backup_terakhir', ''), 3);

        return view('koperasi.pengaturan.database', [
            'pengaturan' => Pengaturan::where('grup', 'backup')->pluck('nilai', 'kunci'),
            'daftar' => DatabaseBackup::daftar(),
            'direktori' => DatabaseBackup::direktori(),
            'terakhir' => count($terakhir) === 3 ? ['waktu' => $terakhir[0], 'status' => $terakhir[1], 'info' => $terakhir[2]] : null,
        ]);
    }

    public function simpan(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'backup_waktu' => ['required', 'date_format:H:i'],
            'backup_retensi' => ['required', 'integer', 'min:1', 'max:365'],
            'backup_lokasi' => ['required', 'in:server,custom'],
            'backup_path' => ['nullable', 'required_if:backup_lokasi,custom', 'string', 'max:255'],
        ], [
            'backup_waktu.date_format' => 'Format jam harus HH:MM.',
            'backup_retensi.min' => 'Retensi minimal 1 hari.',
            'backup_path.required_if' => 'Isi folder tujuan untuk lokasi kustom.',
        ]);

        if ($data['backup_lokasi'] === 'custom') {
            $path = rtrim(trim($data['backup_path']), '\\/');
            if (! is_dir($path) && ! @mkdir($path, 0775, true) && ! is_dir($path)) {
                return back()->withInput()->withErrors(['backup_path' => 'Folder tidak dapat dibuat: ' . $path]);
            }
            if (! is_writable($path)) {
                return back()->withInput()->withErrors(['backup_path' => 'Folder tidak dapat ditulis: ' . $path]);
            }
            $data['backup_path'] = $path;
        } else {
            $data['backup_path'] = '';
        }

        Pengaturan::set('backup_auto', $request->boolean('backup_auto') ? '1' : '0', 'backup', 'Auto backup harian');
        Pengaturan::set('backup_waktu', $data['backup_waktu'], 'backup', 'Jam auto backup');
        Pengaturan::set('backup_retensi', $data['backup_retensi'], 'backup', 'Retensi backup (hari)');
        Pengaturan::set('backup_lokasi', $data['backup_lokasi'], 'backup', 'Lokasi backup');
        Pengaturan::set('backup_path', $data['backup_path'], 'backup', 'Folder backup kustom');

        return redirect()->route('pengaturan.database')->with('sukses', 'Pengaturan backup berhasil disimpan.');
    }

    public function backup(): RedirectResponse
    {
        set_time_limit(0);

        try {
            $path = DatabaseBackup::buat('backup');
        } catch (\Throwable $e) {
            return back()->with('galat', 'Backup gagal: ' . $e->getMessage());
        }

        $this->catatLog('backup', 'Database', null, basename($path), 'Backup database dibuat manual');

        return redirect()->route('pengaturan.database')->with('sukses', 'Backup berhasil dibuat: ' . basename($path));
    }

    public function unduh(string $nama): BinaryFileResponse
    {
        $path = DatabaseBackup::pathFile($nama);
        abort_if($path === null, 404);

        return response()->download($path, $nama);
    }

    public function hapus(string $nama): RedirectResponse
    {
        $path = DatabaseBackup::pathFile($nama);
        abort_if($path === null, 404);

        unlink($path);

        return back()->with('sukses', 'File backup dihapus.');
    }

    public function pulihkan(Request $request): RedirectResponse
    {
        $request->validate([
            'konfirmasi' => ['required', 'in:RESTORE'],
            'nama' => ['nullable', 'string'],
            'file' => ['nullable', 'file', 'max:512000'],
        ], [
            'konfirmasi.in' => 'Ketik RESTORE untuk mengonfirmasi.',
            'konfirmasi.required' => 'Ketik RESTORE untuk mengonfirmasi.',
        ]);

        set_time_limit(0);

        $unggah = $request->file('file');
        $sumber = $unggah?->getRealPath() ?: ($request->filled('nama') ? DatabaseBackup::pathFile($request->input('nama')) : null);
        if (! $sumber) {
            return back()->with('galat', 'Pilih file backup yang akan dipulihkan.');
        }

        try {
            // Cadangan otomatis kondisi saat ini, supaya restore bisa dibatalkan.
            DatabaseBackup::buat('prerestore');
            $hasil = DatabaseBackup::pulihkan($sumber);
        } catch (\Throwable $e) {
            return back()->with('galat', 'Restore gagal: ' . $e->getMessage());
        }

        try {
            $this->catatLog('restore', 'Database', null, '-', "Database dipulihkan ({$hasil['tabel']} tabel, {$hasil['baris']} baris)");
        } catch (\Throwable) {
            // user pelaku bisa saja tidak ada lagi setelah data diganti
        }

        return redirect()->route('pengaturan.database')
            ->with('sukses', "Restore selesai: {$hasil['tabel']} tabel, {$hasil['baris']} baris. Backup kondisi sebelumnya disimpan (prerestore).");
    }

    // Tanpa login: hanya tersedia selama aplikasi belum memiliki akun (server baru).
    public function pemulihanAwal(): View
    {
        abort_unless(DatabaseBackup::belumAdaAkun(), 404);

        return view('auth.pemulihan');
    }

    public function prosesPemulihanAwal(Request $request): RedirectResponse
    {
        abort_unless(DatabaseBackup::belumAdaAkun(), 404);

        $request->validate(['file' => ['required', 'file', 'max:512000']], [
            'file.required' => 'Pilih file backup.',
        ]);

        set_time_limit(0);

        try {
            $hasil = DatabaseBackup::pulihkan($request->file('file')->getRealPath());
        } catch (\Throwable $e) {
            return back()->with('galat', 'Restore gagal: ' . $e->getMessage());
        }

        return redirect()->route('masuk')
            ->with('sukses', "Restore selesai ({$hasil['tabel']} tabel, {$hasil['baris']} baris). Silakan masuk dengan akun dari data lama.");
    }
}