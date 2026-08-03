<?php

namespace App\Http\Controllers\Koperasi;

use App\Http\Controllers\Controller;

use App\Models\Koperasi\Shu;
use App\Models\Koperasi\ShuAnggota;
use App\Models\Koperasi\Anggota;
use App\Models\Koperasi\Simpanan;
use App\Models\Koperasi\Pinjaman;
use App\Models\Koperasi\Pengeluaran;
use App\Models\Koperasi\Pengaturan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ShuController extends Controller
{
    public function index()
    {
        $this->authorize('shu.lihat');

        $daftarShu = Shu::withCount('anggota')
            ->orderByDesc('tahun')
            ->paginate(10);

        return view('koperasi.shu.index', compact('daftarShu'));
    }

    public function hitung(Request $request)
    {
        $this->authorize('shu.kelola');

        $tahunList = range(now()->year, 2020);
        $tahun     = $request->integer('tahun', now()->year);
        $qNama     = trim((string) $request->input('q_nama', ''));

        // Sudah ada SHU untuk tahun ini?
        $existing = Shu::where('tahun', $tahun)->first();

        // Hitung dari DB
        $totalSimpanan = Simpanan::whereYear('tanggal', $tahun)->sum('jumlah');
        $totalPinjaman = Pinjaman::where('status', 'disetujui')
            ->whereYear('tanggal_pengajuan', $tahun)
            ->sum('jumlah_pinjaman');
        $totalPengeluaran = Pengeluaran::whereYear('tanggal', $tahun)
            ->sum('jumlah');

        // Simpanan per anggota (aggregate)
        $simpananPerAnggota = Simpanan::whereYear('tanggal', $tahun)
            ->select('anggota_id', DB::raw('SUM(jumlah) as total'))
            ->groupBy('anggota_id')
            ->pluck('total', 'anggota_id');

        // Pinjaman per anggota (aggregate)
        $pinjamanPerAnggota = Pinjaman::where('status', 'disetujui')
            ->whereYear('tanggal_pengajuan', $tahun)
            ->select('anggota_id', DB::raw('SUM(jumlah_pinjaman) as total'))
            ->groupBy('anggota_id')
            ->pluck('total', 'anggota_id');

        // Gabungkan anggota yang terlibat untuk proses hitung SHU
        $anggotaIds = $simpananPerAnggota->keys()
            ->merge($pinjamanPerAnggota->keys())
            ->unique();

        // Query preview (dengan search + paging)
        $simpananAgg = Simpanan::whereYear('tanggal', $tahun)
            ->select('anggota_id', DB::raw('SUM(jumlah) as total_simpanan'))
            ->groupBy('anggota_id');

        $pinjamanAgg = Pinjaman::where('status', 'disetujui')
            ->whereYear('tanggal_pengajuan', $tahun)
            ->select('anggota_id', DB::raw('SUM(jumlah_pinjaman) as total_pinjaman'))
            ->groupBy('anggota_id');

        $anggotaList = Anggota::query()
            ->leftJoinSub($simpananAgg, 'simp', fn($j) => $j->on('simp.anggota_id', '=', 'anggota.id'))
            ->leftJoinSub($pinjamanAgg, 'pinj', fn($j) => $j->on('pinj.anggota_id', '=', 'anggota.id'))
            ->where(function ($q) {
                $q->whereNotNull('simp.anggota_id')
                  ->orWhereNotNull('pinj.anggota_id');
            })
            ->when($qNama !== '', fn($q) => $q->where('anggota.nama', 'like', '%' . $qNama . '%'))
            ->orderBy('anggota.nama')
            ->select([
                'anggota.id',
                'anggota.nama',
                DB::raw('COALESCE(simp.total_simpanan, 0) as total_simpanan'),
                DB::raw('COALESCE(pinj.total_pinjaman, 0) as total_pinjaman'),
            ])
            ->paginate(25)
            ->withQueryString();

        return view('koperasi.shu.hitung', compact(
            'tahun', 'tahunList', 'existing',
            'totalSimpanan', 'totalPinjaman', 'totalPengeluaran',
            'simpananPerAnggota', 'pinjamanPerAnggota',
            'anggotaList', 'qNama'
        ));
    }

    public function store(Request $request)
    {
        $this->authorize('shu.kelola');

        $validated = $request->validate([
            'tahun'                  => 'required|integer|min:2000|max:2100',
            'total_pendapatan'       => 'required|numeric|min:0',
            'total_biaya'            => 'required|numeric|min:0',
            'persentase_jasa_modal'  => 'required|numeric|min:0|max:100',
            'persentase_jasa_usaha'  => 'required|numeric|min:0|max:100',
            'keterangan'             => 'nullable|string|max:1000',
        ]);

        // Validasi total persentase tidak boleh lebih dari 100
        $totalPersen = $validated['persentase_jasa_modal'] + $validated['persentase_jasa_usaha'];
        if ($totalPersen > 100) {
            return back()->withErrors(['persentase_jasa_modal' => 'Total persentase jasa modal + jasa usaha tidak boleh melebihi 100%'])->withInput();
        }

        $tahun       = $validated['tahun'];
        $totalShu    = $validated['total_pendapatan'] - $validated['total_biaya'];
        $pctModal    = $validated['persentase_jasa_modal'] / 100;
        $pctUsaha    = $validated['persentase_jasa_usaha'] / 100;

        $shuModal  = $totalShu * $pctModal;
        $shuUsaha  = $totalShu * $pctUsaha;

        // Hapus draft lama jika ada
        Shu::where('tahun', $tahun)->where('status', 'draft')->delete();

        $shu = Shu::create([
            'tahun'                 => $tahun,
            'total_pendapatan'      => $validated['total_pendapatan'],
            'total_biaya'           => $validated['total_biaya'],
            'total_shu'             => $totalShu,
            'persentase_jasa_modal' => $validated['persentase_jasa_modal'],
            'persentase_jasa_usaha' => $validated['persentase_jasa_usaha'],
            'status'                => 'draft',
            'keterangan'            => $validated['keterangan'],
            'user_id'               => Auth::id(),
        ]);

        // Hitung per anggota
        $totalSimpanan = (float) Simpanan::whereYear('tanggal', $tahun)->sum('jumlah');
        $totalPinjaman = (float) Pinjaman::where('status', 'disetujui')
            ->whereYear('tanggal_pengajuan', $tahun)
            ->sum('jumlah_pinjaman');

        $simpananPerAnggota = Simpanan::whereYear('tanggal', $tahun)
            ->select('anggota_id', DB::raw('SUM(jumlah) as total'))
            ->groupBy('anggota_id')
            ->pluck('total', 'anggota_id');

        $pinjamanPerAnggota = Pinjaman::where('status', 'disetujui')
            ->whereYear('tanggal_pengajuan', $tahun)
            ->select('anggota_id', DB::raw('SUM(jumlah_pinjaman) as total'))
            ->groupBy('anggota_id')
            ->pluck('total', 'anggota_id');

        $anggotaIds = $simpananPerAnggota->keys()
            ->merge($pinjamanPerAnggota->keys())
            ->unique();

        foreach ($anggotaIds as $anggotaId) {
            $simpAnggota = (float) ($simpananPerAnggota[$anggotaId] ?? 0);
            $pinjAnggota = (float) ($pinjamanPerAnggota[$anggotaId] ?? 0);

            $jasaModal = $totalSimpanan > 0 ? ($simpAnggota / $totalSimpanan) * $shuModal : 0;
            $jasaUsaha = $totalPinjaman > 0 ? ($pinjAnggota / $totalPinjaman) * $shuUsaha : 0;

            ShuAnggota::create([
                'shu_id'           => $shu->id,
                'anggota_id'       => $anggotaId,
                'total_simpanan'   => $simpAnggota,
                'total_pinjaman'   => $pinjAnggota,
                'jasa_modal'       => $jasaModal,
                'jasa_usaha'       => $jasaUsaha,
                'total_shu_anggota'=> $jasaModal + $jasaUsaha,
            ]);
        }

        return redirect()->route('shu.show', $shu)->with('sukses', 'SHU tahun ' . $tahun . ' berhasil dihitung.');
    }

    public function show(Shu $shu)
    {
        $this->authorize('shu.lihat');

        $shu->load(['anggota.anggota', 'pembuat']);

        return view('koperasi.shu.detail', compact('shu'));
    }

    public function finalisasi(Shu $shu)
    {
        $this->authorize('shu.kelola');

        if ($shu->status !== 'draft') {
            return back()->with('error', 'SHU sudah final, tidak dapat diubah.');
        }

        $shu->update(['status' => 'final']);

        return back()->with('sukses', 'SHU tahun ' . $shu->tahun . ' berhasil difinalisasi.');
    }

    public function cetak(Shu $shu)
    {
        $this->authorize('shu.lihat');

        $shu->load(['anggota.anggota', 'pembuat']);

        $profil = Pengaturan::semuaNilai();

        return view('koperasi.shu.cetak', compact('shu', 'profil'));
    }

    public function destroy(Shu $shu)
    {
        $this->authorize('shu.kelola');

        if ($shu->status === 'final') {
            return back()->with('error', 'SHU yang sudah final tidak dapat dihapus.');
        }

        $shu->delete();

        return redirect()->route('shu.index')->with('sukses', 'Data SHU berhasil dihapus.');
    }
}
