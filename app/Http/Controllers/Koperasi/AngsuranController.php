<?php

namespace App\Http\Controllers\Koperasi;

use App\Http\Controllers\Controller;

use App\Models\Koperasi\Angsuran;
use App\Models\Koperasi\Pinjaman;
use App\Http\Controllers\Concerns\CatatAktivitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AngsuranController extends Controller
{
    use CatatAktivitas;
    public function index(Request $request)
    {
        $cari   = $request->input('cari');
        $status = $request->input('status');
        $user   = Auth::user();
        $anggotaId = $user->roleModel?->slug === 'anggota' ? $user->anggota_id : null;

        $angsuran = Angsuran::with('pinjaman.anggota')
            ->when($anggotaId, fn($q) => $q->whereHas('pinjaman', fn($pq) => $pq->where('anggota_id', $anggotaId)))
            ->when($cari, function ($q) use ($cari) {
                $q->whereHas('pinjaman', function($pq) use ($cari) {
                    $pq->where('nomor_pinjaman', 'like', "%$cari%")
                        ->orWhereHas('anggota', fn($aq) => $aq->where('nama', 'like', "%$cari%"));
                });
            })
            ->when($status, fn($q) => $q->where('status', $status))
            ->orderBy('tanggal_jatuh_tempo')
            ->paginate(15)
            ->withQueryString();

        $statsAngsuran = [
            'total'     => Angsuran::when($anggotaId, fn($q) => $q->whereHas('pinjaman', fn($pq) => $pq->where('anggota_id', $anggotaId)))->count(),
            'sudah'     => Angsuran::when($anggotaId, fn($q) => $q->whereHas('pinjaman', fn($pq) => $pq->where('anggota_id', $anggotaId)))->where('status', 'sudah')->count(),
            'belum'     => Angsuran::when($anggotaId, fn($q) => $q->whereHas('pinjaman', fn($pq) => $pq->where('anggota_id', $anggotaId)))->where('status', 'belum')->count(),
            'terlambat' => Angsuran::when($anggotaId, fn($q) => $q->whereHas('pinjaman', fn($pq) => $pq->where('anggota_id', $anggotaId)))->where('status', 'belum')->where('tanggal_jatuh_tempo', '<', today())->count(),
        ];

        return view('koperasi.angsuran.index', compact('angsuran', 'cari', 'status', 'statsAngsuran'));
    }

    public function show(Angsuran $angsuran)
    {
        $angsuran->load('pinjaman.anggota');
        return view('koperasi.angsuran.detail', compact('angsuran'));
    }

    public function bayar(Request $request, Angsuran $angsuran)
    {
        if ($angsuran->status === 'sudah') {
            return back()->with('galat', 'Angsuran ini sudah dibayar.');
        }

        $request->validate([
            'tanggal_bayar' => ['nullable', 'date'],
        ]);

        $tanggalBayar = $request->input('tanggal_bayar', today()->toDateString());
        $denda        = $angsuran->hitungDenda($tanggalBayar);
        $hariTerlambat = 0;

        $jatuhTempo = $angsuran->tanggal_jatuh_tempo;
        if (\Illuminate\Support\Carbon::parse($tanggalBayar)->gt($jatuhTempo)) {
            $hariTerlambat = $jatuhTempo->diffInDays(\Illuminate\Support\Carbon::parse($tanggalBayar));
        }

        $angsuran->load('pinjaman.anggota');
        $angsuran->update([
            'status'         => 'sudah',
            'tanggal_bayar'  => $tanggalBayar,
            'denda'          => $denda,
            'hari_terlambat' => $hariTerlambat,
        ]);

        $pinjaman = $angsuran->pinjaman;
        $keterangan = 'Angsuran ke-' . $angsuran->ke . ' pinjaman ' . $pinjaman->nomor_pinjaman . ' dibayar';
        if ($denda > 0) {
            $keterangan .= ' (terlambat ' . $hariTerlambat . ' hari, denda Rp ' . number_format($denda, 0, ',', '.') . ')';
        }
        $this->catatLog('bayar', 'Angsuran', $angsuran->id,
            $pinjaman->anggota->nama ?? '-', $keterangan);

        // Cek apakah semua angsuran sudah lunas
        if ($pinjaman->angsuran()->where('status', 'belum')->count() === 0) {
            $pinjaman->update(['status' => 'lunas']);
        }

        $pesan = 'Pembayaran angsuran berhasil dicatat.';
        if ($denda > 0) {
            $pesan .= ' Denda keterlambatan: Rp ' . number_format($denda, 0, ',', '.') . ' (' . $hariTerlambat . ' hari terlambat).';
        }

        return back()->with('sukses', $pesan);
    }

    public function create() {}
    public function store(Request $request) {}
    public function edit(Angsuran $angsuran) {}
    public function update(Request $request, Angsuran $angsuran) {}
    public function destroy(Angsuran $angsuran) {}
}
