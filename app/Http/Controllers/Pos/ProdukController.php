<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\Pos\Produk;
use App\Models\Pos\Kategori;
use App\Models\Pos\Toko;
use Illuminate\Http\Request;

class ProdukController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->q;
        $katId = $request->kategori;
        $supId = $request->supplier;

        $allowedSort = ['kode', 'barcode', 'nama', 'harga_beli', 'harga_jual', 'stok', 'is_aktif'];
        $sortBy  = in_array($request->input('sort'), $allowedSort) ? $request->input('sort') : 'nama';
        $sortDir = $request->input('dir') === 'desc' ? 'desc' : 'asc';

        $produk = Produk::with(['kategori:id,nama', 'supplier:id,nama'])
            ->when($q, fn($qu) => $qu->where(function ($qq) use ($q) {
                $qq->where('nama', 'like', "%$q%")
                    ->orWhere('kode', 'like', "%$q%")
                    ->orWhere('barcode', 'like', "%$q%");
            }))
            ->when($katId, fn($qu) => $qu->where('kategori_id', $katId))
            ->when($supId, fn($qu) => $qu->where('supplier_id', $supId))
            ->orderBy($sortBy, $sortDir)
            ->paginate(20)
            ->withQueryString();

        $kategoris = Kategori::orderBy('urutan')->orderBy('nama')->get(['id','nama']);
        $suppliers = Toko::orderBy('nama')->get(['id','nama']);

        $stats = Produk::selectRaw('COUNT(*) as total, SUM(is_aktif) as aktif, SUM(!is_aktif) as nonaktif, SUM(stok=0 AND is_aktif) as habis')->first();

        return view('pos.produk.index', compact('produk', 'kategoris', 'suppliers', 'stats', 'q', 'katId', 'supId', 'sortBy', 'sortDir'));
    }

    public function create(Request $request)
    {
        $kategoris  = Kategori::orderBy('urutan')->orderBy('nama')->get(['id','nama']);
        $suppliers  = Toko::orderBy('nama')->get(['id','nama']);
        $kode = Produk::generateKode();
        $prefillBarcode = trim((string) $request->input('barcode', ''));
        return view('pos.produk.create', compact('kategoris', 'suppliers', 'kode', 'prefillBarcode'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kode'        => 'required|string|max:50|unique:pos_produk,kode',
            'barcode'     => 'nullable|string|max:100|unique:pos_produk,barcode',
            'nama'        => 'required|string|max:255',
            'kategori_id' => 'nullable|exists:pos_kategori,id',
            'supplier_id' => 'nullable|exists:pos_toko,id',
            'harga_beli'  => 'required|integer|min:0',
            'harga_jual'  => 'required|integer|min:0',
            'stok'        => 'required|integer|min:0',
            'satuan'      => 'required|string|max:20',
            'deskripsi'   => 'nullable|string|max:1000',
            'is_aktif'    => 'nullable',
        ]);
        $data['is_aktif'] = $request->boolean('is_aktif', true);

        Produk::create($data);
        return redirect()->route('pos.produk.index')->with('sukses', 'Produk berhasil ditambahkan.');
    }

    public function edit(Produk $produk)
    {
        $kategoris = Kategori::orderBy('urutan')->orderBy('nama')->get(['id','nama']);
        $suppliers = Toko::orderBy('nama')->get(['id','nama']);
        $prefillBarcode = null;
        return view('pos.produk.edit', compact('produk', 'kategoris', 'suppliers', 'prefillBarcode'));
    }

    public function update(Request $request, Produk $produk)
    {
        $data = $request->validate([
            'kode'        => "required|string|max:50|unique:pos_produk,kode,{$produk->id}",
            'barcode'     => "nullable|string|max:100|unique:pos_produk,barcode,{$produk->id}",
            'nama'        => 'required|string|max:255',
            'kategori_id' => 'nullable|exists:pos_kategori,id',
            'supplier_id' => 'nullable|exists:pos_toko,id',
            'harga_beli'  => 'required|integer|min:0',
            'harga_jual'  => 'required|integer|min:0',
            'stok'        => 'required|integer|min:0',
            'satuan'      => 'required|string|max:20',
            'deskripsi'   => 'nullable|string|max:1000',
            'is_aktif'    => 'nullable',
        ]);
        $data['is_aktif'] = $request->boolean('is_aktif', true);

        $produk->update($data);
        return redirect()->route('pos.produk.index')->with('sukses', 'Produk berhasil diperbarui.');
    }

    public function destroy(Produk $produk)
    {
        $produk->delete();
        return redirect()->route('pos.produk.index')->with('sukses', 'Produk dihapus.');
    }

    public function stockInByScanner(Request $request)
    {
        $data = $request->validate([
            'barcode' => 'required|string|max:100',
            'qty'     => 'required|integer|min:1|max:100000',
        ]);

        $scan = trim($data['barcode']);
        $qty  = (int) $data['qty'];

        $produk = Produk::where('barcode', $scan)
            ->orWhere('kode', $scan)
            ->first();

        if (! $produk) {
            return back()
                ->withInput()
                ->with('galat', "Barcode/kode {$scan} tidak ditemukan. Silakan tambah produk baru atau periksa data barcode.");
        }

        $produk->increment('stok', $qty);

        return back()->with('sukses', "Stok {$produk->nama} berhasil ditambah {$qty}. Stok sekarang {$produk->fresh()->stok}.");
    }
}
