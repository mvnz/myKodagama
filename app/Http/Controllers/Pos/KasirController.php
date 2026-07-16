<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\Pos\Kategori;
use App\Models\Pos\Produk;
use App\Models\Pos\Transaksi;
use App\Models\Pos\Item;
use App\Models\Pos\Toko;
use App\Models\Pos\SaldoLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Auth;

class KasirController extends Controller
{
    public function index(Request $request)
    {
        $kategoriId = $request->kategori;

        $kategoris = Kategori::orderBy('urutan')->orderBy('nama')->get();

        $produk = Produk::where('is_aktif', true)
            ->when($kategoriId, fn($q) => $q->where('kategori_id', $kategoriId))
            ->when($request->q, fn($q, $cari) => $q->where(function ($qq) use ($cari) {
                $qq->where('nama', 'like', "%$cari%")
                   ->orWhere('kode', 'like', "%$cari%");
            }))
            ->with('kategori:id,nama')
            ->orderBy('nama')
            ->get(['id','kategori_id','kode','nama','harga_jual','stok','satuan']);

        return view('pos.kasir', compact('kategoris', 'produk', 'kategoriId'));
    }

    public function scan(Request $request)
    {
        $scan = trim((string) $request->query('q', ''));

        if ($scan === '') {
            return response()->json(['ok' => false, 'message' => 'Barcode/kode kosong.'], 422);
        }

        $produk = Produk::where('is_aktif', true)
            ->where(function ($q) use ($scan) {
                $q->where('barcode', $scan)
                    ->orWhere('kode', $scan);
            })
            ->first(['id', 'kode', 'nama', 'harga_jual', 'stok']);

        if (! $produk) {
            return response()->json(['ok' => false, 'message' => "Barcode/kode {$scan} tidak ditemukan."], 404);
        }

        if ((int) $produk->stok <= 0) {
            return response()->json(['ok' => false, 'message' => "Stok {$produk->nama} habis."], 422);
        }

        return response()->json([
            'ok' => true,
            'product' => [
                'id' => $produk->id,
                'kode' => $produk->kode,
                'nama' => $produk->nama,
                'harga' => (int) $produk->harga_jual,
                'stok' => (int) $produk->stok,
            ],
        ]);
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'items'       => 'required|json',
            'diskon'      => 'nullable|integer|min:0',
            'bayar'       => 'required|integer|min:0',
            'metode_bayar'=> 'required|in:tunai,transfer,qris',
            'catatan'     => 'nullable|string|max:500',
        ]);

        $isAjax = $request->wantsJson() || $request->ajax();

        $items = json_decode($request->items, true);
        if (empty($items)) {
            if ($isAjax) return response()->json(['error' => 'Keranjang kosong.'], 422);
            return back()->withErrors(['items' => 'Keranjang kosong.']);
        }

        // Validasi stok
        $ids = array_column($items, 'id');
        $produkMap = Produk::whereIn('id', $ids)->where('is_aktif', true)
            ->get()->keyBy('id');

        foreach ($items as $item) {
            $p = $produkMap[$item['id']] ?? null;
            if (!$p) {
                if ($isAjax) return response()->json(['error' => "Produk #{$item['id']} tidak ditemukan."], 422);
                return back()->withErrors(['items' => "Produk #{$item['id']} tidak ditemukan."]);
            }
            if ($p->stok < $item['qty']) {                if ($isAjax) return response()->json(['error' => "Stok {$p->nama} tidak cukup (tersisa {$p->stok})."], 422);                return back()->withErrors(['items' => "Stok {$p->nama} tidak cukup (tersisa {$p->stok})."]);
            }
        }

        $diskon   = (int) ($request->diskon ?? 0);
        $subtotal = array_sum(array_map(fn($i) => $i['qty'] * $produkMap[$i['id']]->harga_jual, $items));
        $total    = max(0, $subtotal - $diskon);
        $metodeBayar = (string) $request->metode_bayar;
        $bayar    = (int) $request->bayar;

        if ($metodeBayar !== 'tunai') {
            $bayar = $total;
            $kembalian = 0;
        } else {
            if ($bayar < $total) {
                if ($isAjax) return response()->json(['error' => 'Jumlah bayar kurang dari total.'], 422);
                return back()->withErrors(['bayar' => 'Jumlah bayar kurang dari total.']);
            }

            $kembalian = $bayar - $total;
        }

        $trxData = null;
        DB::transaction(function () use ($items, $produkMap, $subtotal, $diskon, $total, $bayar, $kembalian, $request, &$trxData) {
            $trx = Transaksi::create([
                'nomor'        => Transaksi::generateNomor(),
                'kasir_id'     => Auth::id(),
                'subtotal'     => $subtotal,
                'diskon'       => $diskon,
                'total'        => $total,
                'bayar'        => $bayar,
                'kembalian'    => $kembalian,
                'metode_bayar' => $request->metode_bayar,
                'catatan'      => $request->catatan,
                'tanggal'      => now()->toDateString(),
            ]);

            $itemRows = [];
            foreach ($items as $item) {
                $p = $produkMap[$item['id']];
                $itemRows[] = [
                    'transaksi_id' => $trx->id,
                    'produk_id'    => $p->id,
                    'nama_produk'  => $p->nama,
                    'kode_produk'  => $p->kode,
                    'harga'        => $p->harga_jual,
                    'harga_pokok'  => $p->harga_beli,
                    'qty'          => $item['qty'],
                    'subtotal'     => $item['qty'] * $p->harga_jual,
                ];
                $p->decrement('stok', $item['qty']);
            }
            Item::insert($itemRows);

            // ─── Update saldo per supplier (harga pokok per item) ─
            // Kelompokkan item berdasarkan supplier_id produk
            $supplierMap = Toko::whereIn('id',
                array_filter(array_map(fn($i) => $produkMap[$i['id']]->supplier_id, $items))
            )->get()->keyBy('id');

            $saldoPerSupplier = [];
            foreach ($items as $item) {
                $p = $produkMap[$item['id']];
                if (!$p->supplier_id) continue;
                $saldoPerSupplier[$p->supplier_id] = ($saldoPerSupplier[$p->supplier_id] ?? 0)
                    + ($item['qty'] * $p->harga_beli);
            }

            foreach ($saldoPerSupplier as $supplierId => $jumlah) {
                $supplier = $supplierMap[$supplierId] ?? null;
                if (!$supplier) continue;
                $saldoSebelum = $supplier->saldo;
                $supplier->increment('saldo', $jumlah);
                SaldoLog::create([
                    'toko_id'       => $supplier->id,
                    'transaksi_id'  => $trx->id,
                    'jumlah'        => $jumlah,
                    'saldo_sebelum' => $saldoSebelum,
                    'saldo_sesudah' => $saldoSebelum + $jumlah,
                    'keterangan'    => 'Transaksi ' . $trx->nomor,
                ]);
            }

            $trxData = $trx;
        });

        if (! $trxData) {
            return back()->withErrors(['items' => 'Transaksi gagal diproses. Silakan coba lagi.']);
        }

        if ($isAjax) {
            return response()->json([
                'sukses'    => true,
                'nomor'     => $trxData->nomor,
                'trx_id'    => $trxData->id,
                'struk_url' => route('pos.struk', $trxData->id),
                'total'     => $total,
                'bayar'     => $bayar,
                'kembalian' => $kembalian,
                'metode_bayar' => $trxData->metode_bayar,
            ]);
        }

        return redirect()->route('pos.struk', $trxData->id)
            ->with('sukses', 'Transaksi berhasil disimpan.');
    }
}
