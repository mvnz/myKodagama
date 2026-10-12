<?php

namespace App\Console\Commands;

use App\Support\DatabaseBackup;
use Illuminate\Console\Command;

class BackupDatabase extends Command
{
    protected $signature = 'backup:run';

    protected $description = 'Backup database ke file .ndjson.gz';

    public function handle(): int
    {
        try {
            $this->info('Backup dibuat: ' . DatabaseBackup::jalankanDanCatat());

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Backup gagal: ' . $e->getMessage());

            return self::FAILURE;
        }
    }
}
