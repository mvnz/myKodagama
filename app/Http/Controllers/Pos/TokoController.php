<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\Pos\Toko;
use App\Models\Pos\SaldoLog;
use App\Models\Pos\Pencairan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TokoController extends Controller
{
    public function index(Request $request)
    {
        $allowedSort = ['nama', 'pemilik', 'saldo', 'created_at'];
        $sortBy  = in_array($request->input('sort'), $allowedSort) ? $request->input('sort') : 'nama';
        $sortDir = $request->input('dir') === 'desc' ? 'desc' : 'asc';
        $q       = trim($request->input('q', ''));

        $suppliers = Toko::withCount('produk')
            ->when($q, fn($query) => $query->where(function ($sub) use ($q) {
                $sub->where('nama', 'like', "%{$q}%")
                    ->orWhere('pemilik', 'like', "%{$q}%")
                    ->orWhere('kota', 'like', "%{$q}%");
            }))
            ->orderBy($sortBy, $sortDir)
            ->paginate(20)->withQueryString();

        return view('pos.toko.index', compact('suppliers', 'sortBy', 'sortDir', 'q'));
    }

    public function create()
    {
        return view('pos.toko.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama'      => 'required|string|max:150',
            'pemilik'   => 'nullable|string|max:100',
            'telepon'   => 'nullable|string|max:30',
            'email'     => 'nullable|email|max:100',
            'alamat'    => 'nullable|string',
            'kota'      => 'nullable|string|max:100',
            'deskripsi' => 'nullable|string',
        ]);
        $data['saldo'] = 0;

        $supplier = Toko::create($data);

        return redirect()->route('pos.toko.index')
            ->with('sukses', "Supplier \"{$supplier->nama}\" berhasil ditambahkan.");
    }

    public function show(Toko $toko)
    {
        $toko->loadCount('produk');

        $saldoLog = SaldoLog::where('toko_id', $toko->id)
            ->with('transaksi:id,nomor,tanggal,total')
            ->orderByDesc('created_at')
            ->paginate(10, ['*'], 'log_page');

        $pencairanList = Pencairan::where('toko_id', $toko->id)
            ->orderByDesc('created_at')
            ->paginate(10, ['*'], 'cair_page');

        $omzetBulanIni = $toko->saldoLog()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('jumlah');

        $totalDicairkan = Pencairan::where('toko_id', $toko->id)->sum('jumlah');

        return view('pos.toko.show', compact(
            'toko', 'saldoLog', 'pencairanList',
            'omzetBulanIni', 'totalDicairkan'
        ));
    }

    public function edit(Toko $toko)
    {
        return view('pos.toko.edit', compact('toko'));
    }

    public function update(Request $request, Toko $toko)
    {
        $data = $request->validate([
            'nama'      => 'required|string|max:150',
            'pemilik'   => 'nullable|string|max:100',
            'telepon'   => 'nullable|string|max:30',
            'email'     => 'nullable|email|max:100',
            'alamat'    => 'nullable|string',
            'kota'      => 'nullable|string|max:100',
            'deskripsi' => 'nullable|string',
        ]);

        $toko->update($data);

        return redirect()->route('pos.toko.show', $toko)
            ->with('sukses', 'Data supplier berhasil diperbarui.');
    }

    public function destroy(Toko $toko)
    {
        if ($toko->produk()->exists()) {
            return back()->with('galat', "Supplier \"{$toko->nama}\" masih memiliki produk, tidak bisa dihapus.");
        }
        $toko->delete();
        return redirect()->route('pos.toko.index')
            ->with('sukses', "Supplier \"{$toko->nama}\" berhasil dihapus.");
    }

    public function cairkan(Request $request, Toko $toko)
    {
        $data = $request->validate([
            'jumlah'        => "required|integer|min:1|max:{$toko->saldo}",
            'metode'        => 'required|in:cash,transfer',
            'nama_penerima' => 'nullable|string|max:100',
            'nama_bank'     => 'required_if:metode,transfer|nullable|string|max:80',
            'no_rekening'   => 'required_if:metode,transfer|nullable|string|max:50',
            'keterangan'    => 'nullable|string|max:500',
        ], [
            'jumlah.max'           => "Jumlah pencairan melebihi saldo tersedia (Rp " . number_format($toko->saldo, 0, ',', '.') . ").",
            'nama_bank.required_if'  => 'Nama bank wajib diisi untuk metode transfer.',
            'no_rekening.required_if'=> 'Nomor rekening wajib diisi untuk metode transfer.',
        ]);

        DB::transaction(function () use ($data, $toko) {
            $saldoSebelum = $toko->saldo;
            $saldoSesudah = $saldoSebelum - $data['jumlah'];

            $toko->decrement('saldo', $data['jumlah']);

            Pencairan::create([
                'toko_id'       => $toko->id,
                'jumlah'        => $data['jumlah'],
                'metode'        => $data['metode'],
                'nama_penerima' => $data['nama_penerima'] ?? null,
                'nama_bank'     => $data['nama_bank'] ?? null,
                'no_rekening'   => $data['no_rekening'] ?? null,
                'keterangan'    => $data['keterangan'] ?? null,
                'saldo_sebelum' => $saldoSebelum,
                'saldo_sesudah' => $saldoSesudah,
                'created_at'    => now(),
            ]);
        });

        return back()->with('sukses', 'Pencairan saldo sebesar Rp ' . number_format($data['jumlah'], 0, ',', '.') . ' berhasil diproses.');
    }
}
