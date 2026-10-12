<?php

namespace App\Console\Commands;

use App\Models\Koperasi\Pengaturan;
use App\Support\DatabaseBackup;
use Illuminate\Console\Command;

class BackupDatabase extends Command
{
    protected $signature = 'backup:run {--auto : Dijalankan oleh scheduler; berhenti jika auto backup nonaktif}';

    protected $description = 'Backup database ke file .ndjson.gz';

    public function handle(): int
    {
        if ($this->option('auto') && Pengaturan::get('backup_auto', '0') !== '1') {
            return self::SUCCESS;
        }

        try {
            $path = DatabaseBackup::buat('backup');
            $hari = max(1, (int) Pengaturan::get('backup_retensi', 14));
            $dihapus = DatabaseBackup::bersihkan($hari);

            Pengaturan::set('backup_terakhir', now()->toDateTimeString() . '|ok|' . basename($path), 'backup', 'Backup terakhir');
            $this->info('Backup dibuat: ' . $path . ($dihapus ? " ({$dihapus} backup lama dihapus)" : ''));

            return self::SUCCESS;
        } catch (\Throwable $e) {
            Pengaturan::set('backup_terakhir', now()->toDateTimeString() . '|gagal|' . $e->getMessage(), 'backup', 'Backup terakhir');
            $this->error('Backup gagal: ' . $e->getMessage());

            return self::FAILURE;
        }
    }
}
