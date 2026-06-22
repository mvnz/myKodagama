<?php

namespace App\Http\Controllers\Koperasi;

use App\Http\Controllers\Controller;

use App\Models\Koperasi\LogAktivitas;
use Illuminate\Http\Request;

class LogAktivitasController extends Controller
{
    public function index(Request $request)
    {
        $cari   = $request->input('cari');
        $modul  = $request->input('modul');
        $aksi   = $request->input('aksi');
        $dari   = $request->input('dari');
        $sampai = $request->input('sampai');

        $log = LogAktivitas::with('pengguna')
            ->when($cari, fn($q) => $q->where('label', 'like', "%$cari%")
                ->orWhere('keterangan', 'like', "%$cari%"))
            ->when($modul, fn($q) => $q->where('modul', $modul))
            ->when($aksi,  fn($q) => $q->where('aksi', $aksi))
            ->when($dari,  fn($q) => $q->whereDate('created_at', '>=', $dari))
            ->when($sampai, fn($q) => $q->whereDate('created_at', '<=', $sampai))
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        $modulList = LogAktivitas::distinct()->orderBy('modul')->pluck('modul');
        $aksiList  = LogAktivitas::distinct()->orderBy('aksi')->pluck('aksi');

        // Statistik ringkasan
        $statHariIni  = LogAktivitas::whereDate('created_at', today())->count();
        $statMingguIni = LogAktivitas::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $statTotal    = LogAktivitas::count();
        $statPerAksi  = LogAktivitas::selectRaw('aksi, count(*) as total')
            ->groupBy('aksi')->pluck('total', 'aksi');

        return view('koperasi.log.index', compact(
            'log', 'cari', 'modul', 'aksi', 'dari', 'sampai',
            'modulList', 'aksiList',
            'statHariIni', 'statMingguIni', 'statTotal', 'statPerAksi'
        ));
    }

    public function show(LogAktivitas $log)
    {
        $log->load('pengguna');
        return view('koperasi.log.detail', compact('log'));
    }
}
