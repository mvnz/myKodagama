<?php

namespace App\Http\Controllers\Koperasi;

use App\Http\Controllers\Controller;

use App\Models\Koperasi\Anggota;
use App\Models\Koperasi\Angsuran;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MonitoringController extends Controller
{
    /**
     * Monitoring simpanan wajib GÇö siapa yang belum setor bulan ini.
     */
    public function simpanan(Request $request)
    {
        $bulan = (int) $request->get('bulan', now()->month);
        $tahun = (int) $request->get('tahun', now()->year);

        // Anggota aktif
        $semuaAnggota = Anggota::where('status', 'aktif')
            ->with(['simpanan' => fn($q) => $q->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)->where('jenis_simpanan', 'wajib'), 'unitKerja'])
            ->orderBy('nama')
            ->get();

        // Pisah: sudah dan belum
        $sudahSetor = $semuaAnggota->filter(fn($a) => $a->simpanan->isNotEmpty());
        $belumSetor = $semuaAnggota->filter(fn($a) => $a->simpanan->isEmpty());

        return view('koperasi.monitoring.simpanan', compact('bulan', 'tahun', 'sudahSetor', 'belumSetor'));
    }

    /**
     * Monitoring angsuran GÇö siapa yang belum bayar pada bulan tertentu.
     */
    public function pinjaman(Request $request)
    {
        $bulan = (int) $request->get('bulan', now()->month);
        $tahun = (int) $request->get('tahun', now()->year);

        // Angsuran belum bayar yang jatuh tempo di bulan ini atau sudah lewat
        $angsuranBelum = Angsuran::with(['pinjaman.anggota.unitKerja'])
            ->where('status', 'belum')
            ->whereMonth('tanggal_jatuh_tempo', $bulan)
            ->whereYear('tanggal_jatuh_tempo', $tahun)
            ->orderBy('tanggal_jatuh_tempo')
            ->get();

        // Group by anggota
        $perAnggota = $angsuranBelum->groupBy(fn($a) => $a->pinjaman->anggota_id ?? 0);

        return view('koperasi.monitoring.pinjaman', compact('bulan', 'tahun', 'angsuranBelum', 'perAnggota'));
    }
}
