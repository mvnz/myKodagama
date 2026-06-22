<?php

namespace App\Http\Controllers\Koperasi;

use App\Http\Controllers\Controller;

use App\Models\Koperasi\Pendapatan;
use App\Http\Controllers\Concerns\CatatAktivitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PendapatanController extends Controller
{
    use CatatAktivitas;

    public function index(Request $request)
    {
        $this->authorize('pendapatan.lihat');

        $tahun    = $request->input('tahun', date('Y'));
        $bulan    = $request->input('bulan');
        $kategori = $request->input('kategori');

        $allowedSort = ['tanggal', 'judul', 'kategori', 'jumlah'];
        $sortBy  = in_array($request->input('sort'), $allowedSort) ? $request->input('sort') : 'tanggal';
        $sortDir = $request->input('dir') === 'asc' ? 'asc' : 'desc';

        $pendapatan = Pendapatan::with('pencatat')
            ->whereBetween('tanggal', ["{$tahun}-01-01", "{$tahun}-12-31"])
            ->when($bulan, fn($q) => $q->whereMonth('tanggal', $bulan))
            ->when($kategori, fn($q) => $q->where('kategori', $kategori))
            ->orderBy($sortBy, $sortDir)
            ->paginate(20)
            ->withQueryString();

        $totalFilter = Pendapatan::whereBetween('tanggal', ["{$tahun}-01-01", "{$tahun}-12-31"])
            ->when($bulan, fn($q) => $q->whereMonth('tanggal', $bulan))
            ->when($kategori, fn($q) => $q->where('kategori', $kategori))
            ->sum('jumlah');

        $totalTahun = Pendapatan::whereBetween('tanggal', ["{$tahun}-01-01", "{$tahun}-12-31"])->sum('jumlah');

        $rekapKategori = Pendapatan::whereBetween('tanggal', ["{$tahun}-01-01", "{$tahun}-12-31"])
            ->when($bulan, fn($q) => $q->whereMonth('tanggal', $bulan))
            ->selectRaw('kategori, SUM(jumlah) as total')
            ->groupBy('kategori')
            ->pluck('total', 'kategori');

        $labelKategori = Pendapatan::labelKategori();

        return view('koperasi.pendapatan.index', compact(
            'pendapatan', 'totalFilter', 'totalTahun',
            'rekapKategori', 'labelKategori', 'tahun', 'bulan', 'kategori',
            'sortBy', 'sortDir'
        ));
    }

    public function create()
    {
        $this->authorize('pendapatan.kelola');
        return view('koperasi.pendapatan.tambah', ['labelKategori' => Pendapatan::labelKategori()]);
    }

    public function store(Request $request)
    {
        $this->authorize('pendapatan.kelola');

        $validated = $request->validate([
            'tanggal'    => 'required|date',
            'judul'      => 'required|string|max:200',
            'kategori'   => 'required|in:' . implode(',', array_keys(Pendapatan::labelKategori())),
            'jumlah'     => 'required|numeric|min:1',
            'keterangan' => 'nullable|string|max:500',
        ]);

        $validated['user_id'] = Auth::id();
        $pendapatan = Pendapatan::create($validated);

        $this->catatLog('tambah', 'Pendapatan', $pendapatan->id, $pendapatan->judul,
            'Pendapatan ' . $pendapatan->labelKategori . ' Rp ' . number_format($pendapatan->jumlah, 0, ',', '.'));

        return redirect()->route('pendapatan.index')
            ->with('sukses', 'Data pendapatan berhasil disimpan.');
    }

    public function show(Pendapatan $pendapatan)
    {
        $this->authorize('pendapatan.lihat');
        return view('koperasi.pendapatan.detail', compact('pendapatan'));
    }

    public function edit(Pendapatan $pendapatan)
    {
        $this->authorize('pendapatan.kelola');
        return view('koperasi.pendapatan.ubah', ['pendapatan' => $pendapatan, 'labelKategori' => Pendapatan::labelKategori()]);
    }

    public function update(Request $request, Pendapatan $pendapatan)
    {
        $this->authorize('pendapatan.kelola');

        $validated = $request->validate([
            'tanggal'    => 'required|date',
            'judul'      => 'required|string|max:200',
            'kategori'   => 'required|in:' . implode(',', array_keys(Pendapatan::labelKategori())),
            'jumlah'     => 'required|numeric|min:1',
            'keterangan' => 'nullable|string|max:500',
        ]);

        $dataLama = $pendapatan->toArray();
        $pendapatan->update($validated);

        $this->catatLog('ubah', 'Pendapatan', $pendapatan->id, $pendapatan->judul,
            'Pendapatan diperbarui', $dataLama, $pendapatan->toArray());

        return redirect()->route('pendapatan.index')
            ->with('sukses', 'Data pendapatan berhasil diperbarui.');
    }

    public function destroy(Pendapatan $pendapatan)
    {
        $this->authorize('pendapatan.kelola');
        $dataLama = $pendapatan->toArray();

        $pendapatan->delete();

        $this->catatLog('hapus', 'Pendapatan', null, $dataLama['judul'] ?? '-',
            'Pendapatan dihapus', $dataLama);

        return redirect()->route('pendapatan.index')
            ->with('sukses', 'Data pendapatan berhasil dihapus.');
    }

    public function cetak(Request $request)
    {
        $this->authorize('pendapatan.lihat');

        $tahun    = $request->input('tahun', date('Y'));
        $bulan    = $request->input('bulan');
        $kategori = $request->input('kategori');

        $pendapatan = Pendapatan::with('pencatat')
            ->whereBetween('tanggal', ["{$tahun}-01-01", "{$tahun}-12-31"])
            ->when($bulan, fn($q) => $q->whereMonth('tanggal', $bulan))
            ->when($kategori, fn($q) => $q->where('kategori', $kategori))
            ->orderBy('tanggal')
            ->get();

        $total         = $pendapatan->sum('jumlah');
        $labelKategori = Pendapatan::labelKategori();
        $profil        = \App\Models\Koperasi\Pengaturan::semuaNilai();

        return view('koperasi.pendapatan.cetak', compact(
            'pendapatan', 'tahun', 'bulan', 'kategori',
            'total', 'labelKategori', 'profil'
        ));
    }
}
