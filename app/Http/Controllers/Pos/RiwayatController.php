<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\Pos\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RiwayatController extends Controller
{
    private function supplierBreakdown(string $dari, string $sampai)
    {
        $debetPerSupplier = DB::table('pos_pencairan')
            ->selectRaw('toko_id as supplier_id, SUM(jumlah) as total_debet')
            ->whereDate('created_at', '>=', $dari)
            ->whereDate('created_at', '<=', $sampai)
            ->groupBy('toko_id');

        return DB::table('pos_item as i')
            ->join('pos_transaksi as trx', 'trx.id', '=', 'i.transaksi_id')
            ->join('pos_produk as p', 'p.id', '=', 'i.produk_id')
            ->leftJoin('pos_toko as s', 's.id', '=', 'p.supplier_id')
            ->leftJoinSub($debetPerSupplier, 'deb', function ($join) {
                $join->on('deb.supplier_id', '=', 'p.supplier_id');
            })
            ->whereBetween('trx.tanggal', [$dari, $sampai])
            ->selectRaw('COALESCE(s.id, 0) as supplier_id')
            ->selectRaw("COALESCE(s.nama, 'Tanpa Supplier') as supplier_nama")
            ->selectRaw('p.nama as nama_barang')
            ->selectRaw('SUM(i.qty) as jumlah')
            ->selectRaw('ROUND(SUM(i.subtotal) / NULLIF(SUM(i.qty), 0), 2) as harga_jual')
            ->selectRaw('SUM(i.subtotal) as total')
            ->selectRaw('SUM(i.subtotal - (i.harga_pokok * i.qty)) as profit')
            ->selectRaw('COALESCE(deb.total_debet, 0) as total_debet')
            ->groupByRaw('COALESCE(s.id, 0), COALESCE(s.nama, "Tanpa Supplier"), p.nama, COALESCE(deb.total_debet, 0)')
            ->orderBy('supplier_id')
            ->orderByDesc('total')
            ->get();
    }

    public function index(Request $request)
    {
        $dari   = $request->dari   ?? now()->format('Y-m-01');
        $sampai = $request->sampai ?? now()->format('Y-m-d');
        $q      = $request->q;

        $allowedSort = ['nomor', 'tanggal', 'total', 'metode_bayar'];
        $sortBy  = in_array($request->input('sort'), $allowedSort) ? $request->input('sort') : 'tanggal';
        $sortDir = $request->input('dir') === 'asc' ? 'asc' : 'desc';

        $transaksi = Transaksi::with('kasir:id,name')
            ->whereBetween('tanggal', [$dari, $sampai])
            ->when($q, fn($qu) => $qu->where('nomor', 'like', "%$q%"))
            ->orderBy($sortBy, $sortDir)
            ->paginate(20)->withQueryString();

        $stats = Transaksi::whereBetween('tanggal', [$dari, $sampai])
            ->selectRaw('COUNT(*) as jumlah, SUM(total) as omzet, SUM(diskon) as total_diskon')
            ->first();

        return view('pos.riwayat.index', compact('transaksi', 'dari', 'sampai', 'q', 'stats', 'sortBy', 'sortDir'));
    }

    public function show(Transaksi $transaksi)
    {
        $transaksi->load(['kasir:id,name', 'items']);
        return view('pos.riwayat.show', compact('transaksi'));
    }

    public function struk(Transaksi $transaksi)
    {
        $transaksi->load(['kasir:id,name', 'items']);
        return view('pos.struk', compact('transaksi'));
    }

    public function laporan(Request $request)
    {
        $mode    = in_array($request->input('mode'), ['hari','minggu','bulan','tahun']) ? $request->input('mode') : 'bulan';
        $tanggal = $request->input('tanggal', now()->format('Y-m-d'));
        $minggu  = $request->input('minggu',  now()->format('Y-\WW'));
        $bulan   = $request->input('bulan',   now()->format('Y-m'));
        $tahun   = $request->input('tahun',   now()->year);

        switch ($mode) {
            case 'hari':
                $dari = $sampai = $tanggal;
                break;
            case 'minggu':
                $parts = explode('-W', $minggu);
                $yPart = (int) ($parts[0] ?? now()->year);
                $wPart = (int) ($parts[1] ?? now()->isoWeek());
                $startOfWeek = \Carbon\Carbon::now()->setISODate($yPart, $wPart)->startOfDay();
                $dari   = $startOfWeek->format('Y-m-d');
                $sampai = $startOfWeek->copy()->addDays(6)->format('Y-m-d');
                break;
            case 'tahun':
                $dari   = "{$tahun}-01-01";
                $sampai = "{$tahun}-12-31";
                break;
            default:
                $mode = 'bulan';
                $parts = explode('-', $bulan);
                $yr = $parts[0]; $mn = $parts[1];
                $dari   = "{$yr}-{$mn}-01";
                $sampai = \Carbon\Carbon::create($yr, $mn)->endOfMonth()->format('Y-m-d');
        }

        if ($mode === 'tahun') {
            $harian = Transaksi::selectRaw("DATE_FORMAT(tanggal, '%Y-%m') as tanggal, COUNT(*) as jumlah, SUM(total) as omzet")
                ->whereBetween('tanggal', [$dari, $sampai])
                ->groupByRaw("DATE_FORMAT(tanggal, '%Y-%m')")
                ->orderBy('tanggal')
                ->get();
        } else {
            $harian = Transaksi::selectRaw('tanggal, COUNT(*) as jumlah, SUM(total) as omzet')
                ->whereBetween('tanggal', [$dari, $sampai])
                ->groupBy('tanggal')
                ->orderBy('tanggal')
                ->get();
        }

        $totalOmzet     = $harian->sum('omzet');
        $totalTransaksi = $harian->sum('jumlah');

        if ($mode === 'hari') {
            $rataRata      = $totalTransaksi > 0 ? $totalOmzet / $totalTransaksi : 0;
            $rataRataLabel = 'Per Transaksi';
            $grafikLabel   = 'Transaksi Harian';
        } elseif ($mode === 'tahun') {
            $rataRata      = $harian->count() > 0 ? $totalOmzet / $harian->count() : 0;
            $rataRataLabel = 'Per Bulan';
            $grafikLabel   = 'Omzet Bulanan';
        } else {
            $rataRata      = $harian->count() > 0 ? $totalOmzet / $harian->count() : 0;
            $rataRataLabel = 'Per Hari';
            $grafikLabel   = 'Omzet Harian';
        }

        $topProduk = \App\Models\Pos\Item::join('pos_transaksi', 'pos_item.transaksi_id', '=', 'pos_transaksi.id')
            ->whereBetween('pos_transaksi.tanggal', [$dari, $sampai])
            ->selectRaw('nama_produk, SUM(pos_item.qty) as total_qty, SUM(pos_item.subtotal) as total_omzet')
            ->groupBy('nama_produk')
            ->orderByDesc('total_omzet')
            ->limit(10)
            ->get();

        $supplierBreakdown = $this->supplierBreakdown($dari, $sampai);

        return view('pos.laporan', compact(
            'harian', 'mode', 'bulan', 'tahun', 'tanggal', 'minggu',
            'totalOmzet', 'totalTransaksi', 'topProduk', 'supplierBreakdown',
            'rataRata', 'rataRataLabel', 'grafikLabel'
        ));
    }

    public function cetakLaporan(Request $request)
    {
        // Reuse same query logic as laporan()
        $mode    = in_array($request->input('mode'), ['hari','minggu','bulan','tahun']) ? $request->input('mode') : 'bulan';
        $tanggal = $request->input('tanggal', now()->format('Y-m-d'));
        $minggu  = $request->input('minggu',  now()->format('Y-\WW'));
        $bulan   = $request->input('bulan',   now()->format('Y-m'));
        $tahun   = $request->input('tahun',   now()->year);

        switch ($mode) {
            case 'hari':
                $dari = $sampai = $tanggal;
                break;
            case 'minggu':
                $parts = explode('-W', $minggu);
                $yPart = (int) ($parts[0] ?? now()->year);
                $wPart = (int) ($parts[1] ?? now()->isoWeek());
                $startOfWeek = \Carbon\Carbon::now()->setISODate($yPart, $wPart)->startOfDay();
                $dari   = $startOfWeek->format('Y-m-d');
                $sampai = $startOfWeek->copy()->addDays(6)->format('Y-m-d');
                break;
            case 'tahun':
                $dari   = "{$tahun}-01-01";
                $sampai = "{$tahun}-12-31";
                break;
            default:
                $parts = explode('-', $bulan);
                $yr = $parts[0]; $mn = $parts[1];
                $dari   = "{$yr}-{$mn}-01";
                $sampai = \Carbon\Carbon::create($yr, $mn)->endOfMonth()->format('Y-m-d');
        }

        if ($mode === 'tahun') {
            $harian = Transaksi::selectRaw("DATE_FORMAT(tanggal, '%Y-%m') as tanggal, COUNT(*) as jumlah, SUM(total) as omzet")
                ->whereBetween('tanggal', [$dari, $sampai])
                ->groupByRaw("DATE_FORMAT(tanggal, '%Y-%m')")
                ->orderBy('tanggal')
                ->get();
        } else {
            $harian = Transaksi::selectRaw('tanggal, COUNT(*) as jumlah, SUM(total) as omzet')
                ->whereBetween('tanggal', [$dari, $sampai])
                ->groupBy('tanggal')
                ->orderBy('tanggal')
                ->get();
        }

        $totalOmzet     = $harian->sum('omzet');
        $totalTransaksi = $harian->sum('jumlah');
        $rataRata       = $harian->count() > 0 ? $totalOmzet / $harian->count() : 0;

        $transaksiDetail = Transaksi::with('kasir:id,name', 'items')
            ->whereBetween('tanggal', [$dari, $sampai])
            ->orderBy('tanggal')
            ->orderBy('nomor')
            ->get();

        $topProduk = \App\Models\Pos\Item::join('pos_transaksi', 'pos_item.transaksi_id', '=', 'pos_transaksi.id')
            ->whereBetween('pos_transaksi.tanggal', [$dari, $sampai])
            ->selectRaw('nama_produk, SUM(pos_item.qty) as total_qty, SUM(pos_item.subtotal) as total_omzet')
            ->groupBy('nama_produk')
            ->orderByDesc('total_omzet')
            ->limit(10)
            ->get();

        $supplierBreakdown = $this->supplierBreakdown($dari, $sampai);

        $profil = \App\Models\Koperasi\Pengaturan::semuaNilai();

        // Build period label
        if ($mode === 'hari') {
            $periodeLabel = \Carbon\Carbon::parse($dari)->translatedFormat('d F Y');
        } elseif ($mode === 'minggu') {
            $periodeLabel = \Carbon\Carbon::parse($dari)->translatedFormat('d M') . ' – ' . \Carbon\Carbon::parse($sampai)->translatedFormat('d M Y');
        } elseif ($mode === 'tahun') {
            $periodeLabel = 'Tahun ' . $tahun;
        } else {
            $periodeLabel = \Carbon\Carbon::parse($dari)->translatedFormat('F Y');
        }

        return view('pos.laporan-cetak', compact(
            'harian', 'mode', 'bulan', 'tahun', 'tanggal', 'minggu',
            'dari', 'sampai', 'periodeLabel',
            'totalOmzet', 'totalTransaksi', 'rataRata',
            'transaksiDetail', 'topProduk', 'supplierBreakdown', 'profil'
        ));
    }
}
