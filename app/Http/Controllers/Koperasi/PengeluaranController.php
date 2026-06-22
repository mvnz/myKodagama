<?php

namespace App\Http\Controllers\Koperasi;

use App\Http\Controllers\Controller;

use App\Models\Koperasi\Pengeluaran;
use App\Http\Controllers\Concerns\CatatAktivitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PengeluaranController extends Controller
{
    use CatatAktivitas;

    public function index(Request $request)
    {
        $this->authorize('pengeluaran.lihat');

        $tahun    = $request->input('tahun', now()->year);
        $bulan    = $request->input('bulan');
        $kategori = $request->input('kategori');

        $allowedSort = ['tanggal', 'judul', 'kategori', 'jumlah'];
        $sortBy  = in_array($request->input('sort'), $allowedSort) ? $request->input('sort') : 'tanggal';
        $sortDir = $request->input('dir') === 'asc' ? 'asc' : 'desc';

        $query = Pengeluaran::with('pencatat')
            ->whereBetween('tanggal', ["{$tahun}-01-01", "{$tahun}-12-31"])
            ->when($bulan,    fn($q) => $q->whereMonth('tanggal', $bulan))
            ->when($kategori, fn($q) => $q->where('kategori', $kategori))
            ->orderBy($sortBy, $sortDir);

        $pengeluaran = $query->paginate(20)->withQueryString();

        $totalTahun  = Pengeluaran::whereBetween('tanggal', ["{$tahun}-01-01", "{$tahun}-12-31"])->sum('jumlah');
        $totalFilter = (clone $query->getQuery())->sum('jumlah');

        // Rekapitulasi per kategori untuk tahun berjalan
        $rekapKategori = Pengeluaran::selectRaw('kategori, SUM(jumlah) as total')
            ->whereBetween('tanggal', ["{$tahun}-01-01", "{$tahun}-12-31"])
            ->when($bulan, fn($q) => $q->whereMonth('tanggal', $bulan))
            ->groupBy('kategori')
            ->pluck('total', 'kategori');

        $labelKategori = Pengeluaran::labelKategori();
        $tahunList     = range(now()->year, 2020);

        return view('koperasi.pengeluaran.index', compact(
            'pengeluaran', 'tahun', 'bulan', 'kategori',
            'totalTahun', 'totalFilter', 'rekapKategori', 'labelKategori', 'tahunList',
            'sortBy', 'sortDir'
        ));
    }

    public function create()
    {
        $this->authorize('pengeluaran.kelola');
        $labelKategori = Pengeluaran::labelKategori();
        return view('koperasi.pengeluaran.tambah', compact('labelKategori'));
    }

    public function store(Request $request)
    {
        $this->authorize('pengeluaran.kelola');

        $validated = $request->validate([
            'tanggal'   => 'required|date',
            'judul'     => 'required|string|max:255',
            'kategori'  => 'required|in:' . implode(',', array_keys(Pengeluaran::labelKategori())),
            'jumlah'    => 'required|numeric|min:1',
            'keterangan'=> 'nullable|string',
            'bukti'     => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ], [
            'tanggal.required'  => 'Tanggal wajib diisi.',
            'judul.required'    => 'Judul/uraian wajib diisi.',
            'kategori.required' => 'Kategori wajib dipilih.',
            'jumlah.required'   => 'Jumlah wajib diisi.',
            'jumlah.min'        => 'Jumlah harus lebih dari 0.',
            'bukti.mimes'       => 'File bukti harus berformat JPG, PNG, atau PDF.',
            'bukti.max'         => 'Ukuran file maksimal 2MB.',
        ]);

        if ($request->hasFile('bukti')) {
            $validated['bukti'] = $request->file('bukti')->store('pengeluaran', 'public');
        }

        $validated['user_id'] = Auth::id();
        // Normalisasi jumlah (hapus titik pemisah ribuan jika ada)
        $validated['jumlah'] = str_replace('.', '', $validated['jumlah']);

        $pengeluaran = Pengeluaran::create($validated);

        $this->catatLog('tambah', 'Pengeluaran', $pengeluaran->id, $pengeluaran->judul,
            'Pengeluaran baru: Rp ' . number_format($pengeluaran->jumlah, 0, ',', '.'),
            null, $pengeluaran->toArray());

        return redirect()->route('pengeluaran.index')
            ->with('sukses', 'Pengeluaran berhasil dicatat.');
    }

    public function show(Pengeluaran $pengeluaran)
    {
        $this->authorize('pengeluaran.lihat');
        $labelKategori = Pengeluaran::labelKategori();
        return view('koperasi.pengeluaran.detail', compact('pengeluaran', 'labelKategori'));
    }

    public function edit(Pengeluaran $pengeluaran)
    {
        $this->authorize('pengeluaran.kelola');
        $labelKategori = Pengeluaran::labelKategori();
        return view('koperasi.pengeluaran.ubah', compact('pengeluaran', 'labelKategori'));
    }

    public function update(Request $request, Pengeluaran $pengeluaran)
    {
        $this->authorize('pengeluaran.kelola');

        $validated = $request->validate([
            'tanggal'   => 'required|date',
            'judul'     => 'required|string|max:255',
            'kategori'  => 'required|in:' . implode(',', array_keys(Pengeluaran::labelKategori())),
            'jumlah'    => 'required|numeric|min:1',
            'keterangan'=> 'nullable|string',
            'bukti'     => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ], [
            'tanggal.required'  => 'Tanggal wajib diisi.',
            'judul.required'    => 'Judul/uraian wajib diisi.',
            'kategori.required' => 'Kategori wajib dipilih.',
            'jumlah.required'   => 'Jumlah wajib diisi.',
            'jumlah.min'        => 'Jumlah harus lebih dari 0.',
        ]);

        if ($request->hasFile('bukti')) {
            if ($pengeluaran->bukti) {
                Storage::disk('public')->delete($pengeluaran->bukti);
            }
            $validated['bukti'] = $request->file('bukti')->store('pengeluaran', 'public');
        }

        $validated['jumlah'] = str_replace('.', '', $validated['jumlah']);

        $dataLama = $pengeluaran->toArray();
        $pengeluaran->update($validated);

        $this->catatLog('ubah', 'Pengeluaran', $pengeluaran->id, $pengeluaran->judul,
            'Pengeluaran diperbarui', $dataLama, $pengeluaran->fresh()->toArray());

        return redirect()->route('pengeluaran.show', $pengeluaran)
            ->with('sukses', 'Pengeluaran berhasil diperbarui.');
    }

    public function destroy(Pengeluaran $pengeluaran)
    {
        $this->authorize('pengeluaran.kelola');

        $label = $pengeluaran->judul;
        $dataLama = $pengeluaran->toArray();

        if ($pengeluaran->bukti) {
            Storage::disk('public')->delete($pengeluaran->bukti);
        }
        $pengeluaran->delete();

        $this->catatLog('hapus', 'Pengeluaran', null, $label,
            'Pengeluaran dihapus', $dataLama);

        return redirect()->route('pengeluaran.index')
            ->with('sukses', 'Pengeluaran berhasil dihapus.');
    }

    public function cetak(Request $request)
    {
        $this->authorize('pengeluaran.lihat');

        $tahun    = $request->input('tahun', now()->year);
        $bulan    = $request->input('bulan');
        $kategori = $request->input('kategori');

        $pengeluaran = Pengeluaran::with('pencatat')
            ->whereBetween('tanggal', ["{$tahun}-01-01", "{$tahun}-12-31"])
            ->when($bulan,    fn($q) => $q->whereMonth('tanggal', $bulan))
            ->when($kategori, fn($q) => $q->where('kategori', $kategori))
            ->orderBy('tanggal')
            ->get();

        $total         = $pengeluaran->sum('jumlah');
        $labelKategori = Pengeluaran::labelKategori();
        $profil        = \App\Models\Koperasi\Pengaturan::semuaNilai();

        return view('koperasi.pengeluaran.cetak', compact(
            'pengeluaran', 'tahun', 'bulan', 'kategori',
            'total', 'labelKategori', 'profil'
        ));
    }
}
