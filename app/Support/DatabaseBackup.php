<?php

namespace App\Support;

use App\Models\Koperasi\Pengaturan;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

/**
 * Backup & restore database dalam format NDJSON terkompresi (.ndjson.gz),
 * independen dari mysqldump sehingga bisa dipindah antar server/driver.
 *
 * Baris 1: meta. Lalu per tabel: {"table","columns"} diikuti baris {"t","r"} untuk tiap record.
 */
class DatabaseBackup
{
    public const FORMAT = 'kodagama-backup';

    public const VERSI = 1;

    public const EKSTENSI = '.ndjson.gz';

    private const ABAIKAN = ['migrations', 'sessions', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs'];

    private const CHUNK = 500;

    public static function direktori(): string
    {
        $path = Pengaturan::get('backup_lokasi', 'server') === 'custom'
            ? trim((string) Pengaturan::get('backup_path', ''))
            : '';

        return $path !== '' ? rtrim($path, '\\/') : storage_path('app' . DIRECTORY_SEPARATOR . 'backups');
    }

    public static function siapkanDirektori(?string $dir = null): string
    {
        $dir ??= self::direktori();

        if (! is_dir($dir) && ! @mkdir($dir, 0775, true) && ! is_dir($dir)) {
            throw new RuntimeException('Folder backup tidak dapat dibuat: ' . $dir);
        }
        if (! is_writable($dir)) {
            throw new RuntimeException('Folder backup tidak dapat ditulis: ' . $dir);
        }

        return $dir;
    }

    /** @return list<string> */
    private static function tabel(): array
    {
        $driver = DB::getDriverName();
        $skema = match ($driver) {
            'mysql', 'mariadb' => DB::getDatabaseName(),
            'pgsql' => 'public',
            default => null,
        };

        $semua = [];
        foreach (Schema::getTables() as $tabel) {
            if ($skema === null || ($tabel['schema'] ?? $skema) === $skema) {
                $semua[] = $tabel['name'];
            }
        }

        return array_values(array_diff($semua, self::ABAIKAN));
    }

    public static function buat(string $awalan = 'backup'): string
    {
        $dir = self::siapkanDirektori();
        $path = $dir . DIRECTORY_SEPARATOR . 'kodagama-' . $awalan . '-' . now()->format('Ymd-His') . self::EKSTENSI;

        $handle = gzopen($path . '.tmp', 'wb6');
        if ($handle === false) {
            throw new RuntimeException('File backup tidak dapat dibuat.');
        }

        try {
            self::tulis($handle, [
                'format' => self::FORMAT,
                'versi' => self::VERSI,
                'dibuat' => now()->toIso8601String(),
                'driver' => DB::getDriverName(),
                'database' => DB::getDatabaseName(),
                'migrations' => DB::table('migrations')->orderBy('id')->pluck('migration')->all(),
            ]);

            foreach (self::tabel() as $tabel) {
                $kolom = Schema::getColumnListing($tabel);
                self::tulis($handle, ['table' => $tabel, 'columns' => $kolom]);

                $kunci = in_array('id', $kolom, true) ? 'id' : $kolom[0];
                DB::table($tabel)->orderBy($kunci)->chunk(self::CHUNK, function ($rows) use ($handle, $tabel, $kolom) {
                    foreach ($rows as $row) {
                        $row = (array) $row;
                        self::tulis($handle, ['t' => $tabel, 'r' => array_map(fn ($k) => $row[$k] ?? null, $kolom)]);
                    }
                });
            }
        } catch (\Throwable $e) {
            gzclose($handle);
            @unlink($path . '.tmp');
            throw $e;
        }

        gzclose($handle);
        rename($path . '.tmp', $path);

        return $path;
    }

    /** @return array{tabel:int, baris:int} */
    public static function pulihkan(string $path): array
    {
        $handle = @gzopen($path, 'rb');
        if ($handle === false) {
            throw new RuntimeException('File backup tidak dapat dibaca.');
        }

        try {
            $meta = json_decode((string) gzgets($handle), true);
            if (! is_array($meta) || ($meta['format'] ?? null) !== self::FORMAT) {
                throw new RuntimeException('File bukan backup Kodagama yang valid.');
            }
            if (($meta['versi'] ?? 0) > self::VERSI) {
                throw new RuntimeException('Versi format backup lebih baru dari aplikasi ini.');
            }

            // Pastikan struktur tabel di server tujuan sudah lengkap sebelum data dimasukkan.
            Artisan::call('migrate', ['--force' => true]);

            $terpasang = DB::table('migrations')->pluck('migration')->all();
            $belumAda = array_diff($meta['migrations'] ?? [], $terpasang);
            if ($belumAda !== []) {
                throw new RuntimeException('Backup dibuat dari versi aplikasi yang lebih baru (migrasi tidak dikenal: ' . reset($belumAda) . '). Perbarui kode aplikasi terlebih dahulu.');
            }

            $ringkasan = ['tabel' => 0, 'baris' => 0];
            $tabelBackup = [];

            Schema::disableForeignKeyConstraints();

            try {
                DB::transaction(function () use ($handle, &$ringkasan, &$tabelBackup) {
                    $tabelAda = self::tabel();
                    $kolomTarget = [];
                    $indeks = [];
                    $buffer = [];
                    $aktif = null;

                    $kosongkan = function (string $tabel) use (&$kolomTarget) {
                        $kolomTarget[$tabel] = Schema::getColumnListing($tabel);
                        DB::table($tabel)->delete();
                    };

                    $flush = function () use (&$buffer, &$aktif) {
                        if ($buffer !== [] && $aktif !== null) {
                            DB::table($aktif)->insert($buffer);
                        }
                        $buffer = [];
                    };

                    while (($baris = gzgets($handle)) !== false) {
                        $baris = trim($baris);
                        if ($baris === '') {
                            continue;
                        }
                        $data = json_decode($baris, true);
                        if (! is_array($data)) {
                            throw new RuntimeException('Baris backup rusak.');
                        }

                        if (isset($data['table'])) {
                            $flush();
                            $aktif = null;
                            if (! in_array($data['table'], $tabelAda, true)) {
                                continue;
                            }
                            $aktif = $data['table'];
                            $tabelBackup[] = $aktif;
                            $kosongkan($aktif);
                            $indeks = [];
                            foreach ($data['columns'] as $i => $kolom) {
                                if (in_array($kolom, $kolomTarget[$aktif], true)) {
                                    $indeks[$kolom] = $i;
                                }
                            }
                            $ringkasan['tabel']++;

                            continue;
                        }

                        if ($aktif === null || ($data['t'] ?? null) !== $aktif) {
                            continue;
                        }

                        $record = [];
                        foreach ($indeks as $kolom => $i) {
                            $record[$kolom] = $data['r'][$i] ?? null;
                        }
                        $buffer[] = $record;
                        $ringkasan['baris']++;

                        if (count($buffer) >= self::CHUNK) {
                            $flush();
                        }
                    }
                    $flush();

                    // Tabel yang tidak ada di backup dikosongkan agar hasilnya identik.
                    foreach (array_diff($tabelAda, $tabelBackup) as $tabel) {
                        DB::table($tabel)->delete();
                    }
                });
            } finally {
                Schema::enableForeignKeyConstraints();
            }

            return $ringkasan;
        } finally {
            gzclose($handle);
        }
    }

    /** @return list<array{nama:string, ukuran:int, waktu:Carbon}> */
    public static function daftar(): array
    {
        $dir = self::direktori();
        if (! is_dir($dir)) {
            return [];
        }

        $hasil = [];
        foreach (glob($dir . DIRECTORY_SEPARATOR . 'kodagama-*' . self::EKSTENSI) ?: [] as $file) {
            $hasil[] = [
                'nama' => basename($file),
                'ukuran' => (int) filesize($file),
                'waktu' => Carbon::createFromTimestamp(filemtime($file)),
            ];
        }
        usort($hasil, fn ($a, $b) => $b['waktu']->timestamp <=> $a['waktu']->timestamp);

        return $hasil;
    }

    /** Path aman untuk nama file backup (mencegah path traversal). */
    public static function pathFile(string $nama): ?string
    {
        if (! preg_match('/^kodagama-[a-z]+-\d{8}-\d{6}' . preg_quote(self::EKSTENSI, '/') . '$/', $nama)) {
            return null;
        }
        $path = self::direktori() . DIRECTORY_SEPARATOR . $nama;

        return is_file($path) ? $path : null;
    }

    private static function tulis($handle, array $data): void
    {
        gzwrite($handle, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION | JSON_INVALID_UTF8_SUBSTITUTE) . "\n");
    }

    /** Buat backup dan catat statusnya. */
    public static function jalankanDanCatat(): string
    {
        try {
            $path = self::buat('backup');
            Pengaturan::set('backup_terakhir', now()->toDateTimeString() . '|ok|' . basename($path), 'backup', 'Backup terakhir');

            return $path;
        } catch (\Throwable $e) {
            Pengaturan::set('backup_terakhir', now()->toDateTimeString() . '|gagal|' . $e->getMessage(), 'backup', 'Backup terakhir');
            throw $e;
        }
    }

    /** Jalankan auto backup bila sudah waktunya dan hari ini belum dijalankan. */
    public static function jalankanBilaJatuhTempo(): void
    {
        $cfg = Pengaturan::whereIn('kunci', ['backup_auto', 'backup_waktu', 'backup_auto_tanggal'])->pluck('nilai', 'kunci');
        if (($cfg['backup_auto'] ?? '0') !== '1') {
            return;
        }

        $hariIni = now()->toDateString();
        if (($cfg['backup_auto_tanggal'] ?? '') === $hariIni || now()->format('H:i') < ($cfg['backup_waktu'] ?? '02:00')) {
            return;
        }

        $kunci = Cache::lock('backup-otomatis', 3600);
        if (! $kunci->get()) {
            return;
        }

        try {
            // Dicatat sebelum berjalan agar kegagalan tidak diulang pada setiap request.
            Pengaturan::set('backup_auto_tanggal', $hariIni, 'backup', 'Tanggal auto backup terakhir');
            self::jalankanDanCatat();
        } catch (\Throwable $e) {
            report($e);
        } finally {
            $kunci->release();
        }
    }

    /** Aplikasi baru/kosong (belum ada akun) — boleh restore tanpa login. */
    public static function belumAdaAkun(): bool
    {
        try {
            return ! DB::table('users')->exists();
        } catch (\Throwable) {
            return true;
        }
    }
}