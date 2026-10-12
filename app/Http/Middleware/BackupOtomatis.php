<?php

namespace App\Http\Middleware;

use App\Support\DatabaseBackup;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BackupOtomatis
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    // Berjalan setelah respons terkirim sehingga tidak memperlambat pengguna.
    public function terminate(Request $request, Response $response): void
    {
        if ($request->isMethod('GET') && ! $request->ajax()) {
            try {
                set_time_limit(0);
                DatabaseBackup::jalankanBilaJatuhTempo();
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }
}
