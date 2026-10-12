<?php

namespace App\Console\Commands;

use App\Support\DatabaseBackup;
use Illuminate\Console\Command;

class RestoreDatabase extends Command
{
    protected $signature = 'backup:restore {file : Path file .ndjson.gz} {--force : Lewati konfirmasi}';

    protected $description = 'Pulihkan database dari file backup (menimpa seluruh data saat ini)';

    public function handle(): int
    {
        $file = $this->argument('file');
        if (! is_file($file)) {
            $this->error('File tidak ditemukan: ' . $file);

            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm('Seluruh data saat ini akan DIGANTI dengan isi backup. Lanjutkan?')) {
            return self::FAILURE;
        }

        try {
            $hasil = DatabaseBackup::pulihkan($file);
        } catch (\Throwable $e) {
            $this->error('Restore gagal: ' . $e->getMessage());

            return self::FAILURE;
        }

        $this->info("Restore selesai: {$hasil['tabel']} tabel, {$hasil['baris']} baris.");

        return self::SUCCESS;
    }
}
