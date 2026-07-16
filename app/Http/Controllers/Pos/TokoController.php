<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\Pos\Toko;
use App\Models\Pos\Pencairan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TokoController extends Controller
{
    public function index(Request $request)
    {
        $allowedSort = ['nama', 'pemilik', 'penjualan', 'profit', 'created_at'];
        $sortBy  = in_array($request->input('sort'), $allowedSort) ? $request->input('sort') : 'nama';
        $sortDir = $request->input('dir') === 'desc' ? 'desc' : 'asc';
        $q       = trim($request->input('q', ''));

        $statPenjualan = DB::table('pos_item as i')
            ->join('pos_produk as p', 'p.id', '=', 'i.produk_id')
            ->selectRaw('p.supplier_id as supplier_id, SUM(i.subtotal) as penjualan, SUM(i.subtotal - (i.harga_pokok * i.qty)) as profit')
            ->whereNotNull('p.supplier_id')
            ->groupBy('p.supplier_id');

        $suppliers = Toko::query()
            ->leftJoinSub($statPenjualan, 'stat', function ($join) {
                $join->on('stat.supplier_id', '=', 'pos_toko.id');
            })
            ->select('pos_toko.*')
            ->selectSub(function ($query) {
                $query->from('pos_produk')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('pos_produk.supplier_id', 'pos_toko.id');
            }, 'produk_count')
            ->selectRaw('COALESCE(stat.penjualan, 0) as penjualan')
            ->selectRaw('COALESCE(stat.profit, 0) as profit')
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

        $produkList = $toko->produk()
            ->select('id', 'kode', 'nama', 'stok', 'harga_beli', 'harga_jual', 'is_aktif')
            ->orderBy('nama')
            ->paginate(10, ['*'], 'produk_page')
            ->withQueryString();

        $omzetBulanIni = $toko->saldoLog()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('jumlah');

        $totalDicairkan = Pencairan::where('toko_id', $toko->id)->sum('jumlah');

        $mutasiMasuk = DB::table('pos_item as i')
            ->join('pos_produk as p', 'p.id', '=', 'i.produk_id')
            ->join('pos_transaksi as trx', 'trx.id', '=', 'i.transaksi_id')
            ->leftJoin('pos_saldo_log as sl', function ($join) use ($toko) {
                $join->on('sl.transaksi_id', '=', 'trx.id')
                    ->where('sl.toko_id', '=', $toko->id);
            })
            ->where('p.supplier_id', $toko->id)
            ->groupBy('i.transaksi_id', 'trx.nomor', 'trx.created_at', 'sl.created_at', 'sl.saldo_sesudah')
            ->selectRaw("COALESCE(sl.created_at, trx.created_at) as waktu, 'kredit' as tipe, SUM(i.qty * i.harga_pokok) as jumlah, COALESCE(sl.saldo_sesudah, 0) as saldo_sesudah, trx.nomor as referensi, NULL as metode, NULL as nama_penerima, NULL as nama_bank, NULL as no_rekening, CONCAT('Produk supplier: ', GROUP_CONCAT(DISTINCT p.nama ORDER BY p.nama SEPARATOR ', ')) as keterangan");

        $mutasiKeluar = DB::table('pos_pencairan as pc')
            ->where('pc.toko_id', $toko->id)
            ->selectRaw("pc.created_at as waktu, 'debet' as tipe, pc.jumlah as jumlah, pc.saldo_sesudah as saldo_sesudah, NULL as referensi, pc.metode as metode, pc.nama_penerima as nama_penerima, pc.nama_bank as nama_bank, pc.no_rekening as no_rekening, pc.keterangan as keterangan");

        $mutasiList = DB::query()
            ->fromSub($mutasiMasuk->unionAll($mutasiKeluar), 'm')
            ->orderByDesc('waktu')
            ->paginate(10, ['*'], 'mutasi_page')
            ->withQueryString();

        $activeTab = request('tab');
        if (!in_array($activeTab, ['produk', 'mutasi'], true)) {
            $activeTab = request()->has('mutasi_page') ? 'mutasi' : 'produk';
        }

        return view('pos.toko.show', compact(
            'toko', 'produkList', 'mutasiList',
            'omzetBulanIni', 'totalDicairkan', 'activeTab'
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
