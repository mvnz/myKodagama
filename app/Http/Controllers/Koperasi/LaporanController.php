<?php

namespace App\Http\Controllers\Koperasi;

use App\Http\Controllers\Controller;

use App\Models\Koperasi\Simpanan;
use App\Models\Koperasi\Pinjaman;
use App\Models\Koperasi\Angsuran;
use App\Models\Koperasi\Anggota;
use App\Models\Koperasi\Pengaturan;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LaporanController extends Controller
{
    public function index(Request $request): View
    {
        $tahun  = (int) $request->input('tahun',  now()->year);
        $bulan  = $request->input('bulan');   // null = seluruh tahun

        // GöÇGöÇ Ringkasan Simpanan GöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇ
        $simpananPokok    = Simpanan::where('jenis_simpanan', 'pokok')
            ->whereYear('tanggal', $tahun)
            ->when(!empty($bulan), fn($q) => $q->whereMonth('tanggal', $bulan))
            ->sum('jumlah');
        $simpananWajib    = Simpanan::where('jenis_simpanan', 'wajib')
            ->whereYear('tanggal', $tahun)
            ->when(!empty($bulan), fn($q) => $q->whereMonth('tanggal', $bulan))
            ->sum('jumlah');
        $simpananSukarela = Simpanan::where('jenis_simpanan', 'sukarela')
            ->whereYear('tanggal', $tahun)
            ->when(!empty($bulan), fn($q) => $q->whereMonth('tanggal', $bulan))
            ->sum('jumlah');
        $totalSimpananMasuk = $simpananPokok + $simpananWajib + $simpananSukarela;

        // GöÇGöÇ Pinjaman Dicairkan GöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇ
        $pinjamanDicairkan = Pinjaman::whereIn('status', ['disetujui'])
            ->whereYear('tanggal_persetujuan', $tahun)
            ->when(!empty($bulan), fn($q) => $q->whereMonth('tanggal_persetujuan', $bulan))
            ->sum('jumlah_pinjaman');

        $jumlahPinjamanBaru = Pinjaman::whereIn('status', ['disetujui'])
            ->whereYear('tanggal_persetujuan', $tahun)
            ->when(!empty($bulan), fn($q) => $q->whereMonth('tanggal_persetujuan', $bulan))
            ->count();

        // GöÇGöÇ Angsuran Diterima GöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇ
        $totalAngsuranDiterima = Angsuran::where('status', 'sudah')
            ->whereYear('tanggal_bayar', $tahun)
            ->when(!empty($bulan), fn($q) => $q->whereMonth('tanggal_bayar', $bulan))
            ->sum('jumlah_angsuran');

        $totalBungaDiterima = Angsuran::where('status', 'sudah')
            ->whereYear('tanggal_bayar', $tahun)
            ->when(!empty($bulan), fn($q) => $q->whereMonth('tanggal_bayar', $bulan))
            ->sum('bunga');

        $totalPokokDiterima = Angsuran::where('status', 'sudah')
            ->whereYear('tanggal_bayar', $tahun)
            ->when(!empty($bulan), fn($q) => $q->whereMonth('tanggal_bayar', $bulan))
            ->sum('pokok');

        // GöÇGöÇ Arus Kas Bersih GöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇ
        $totalPemasukan  = $totalSimpananMasuk + $totalAngsuranDiterima;
        $totalPengeluaran = $pinjamanDicairkan;
        $arusKasBersih   = $totalPemasukan - $totalPengeluaran;

        // GöÇGöÇ Detail Simpanan per bulan (untuk grafik/tabel) GöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇ
        $simpananPerBulan = Simpanan::selectRaw(
                'MONTH(tanggal) as bulan_ke, SUM(jumlah) as total, jenis_simpanan'
            )
            ->whereYear('tanggal', $tahun)
            ->when(!empty($bulan), fn($q) => $q->whereMonth('tanggal', $bulan))
            ->groupBy('bulan_ke', 'jenis_simpanan')
            ->orderBy('bulan_ke')
            ->get();

        // GöÇGöÇ Detail Angsuran per bulan GöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇ
        $angsuranPerBulan = Angsuran::selectRaw(
                'MONTH(tanggal_bayar) as bulan_ke, SUM(jumlah_angsuran) as total, SUM(pokok) as total_pokok, SUM(bunga) as total_bunga'
            )
            ->where('status', 'sudah')
            ->whereYear('tanggal_bayar', $tahun)
            ->when(!empty($bulan), fn($q) => $q->whereMonth('tanggal_bayar', $bulan))
            ->groupBy('bulan_ke')
            ->orderBy('bulan_ke')
            ->get()
            ->keyBy('bulan_ke');

        // GöÇGöÇ Pinjaman Baru per bulan GöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇ
        $pinjamanPerBulan = Pinjaman::selectRaw(
                'MONTH(tanggal_persetujuan) as bulan_ke, COUNT(*) as jumlah, SUM(jumlah_pinjaman) as total'
            )
            ->whereIn('status', ['disetujui'])
            ->whereYear('tanggal_persetujuan', $tahun)
            ->when(!empty($bulan), fn($q) => $q->whereMonth('tanggal_persetujuan', $bulan))
            ->groupBy('bulan_ke')
            ->orderBy('bulan_ke')
            ->get()
            ->keyBy('bulan_ke');

        // GöÇGöÇ Daftar transaksi simpanan (10 terakhir) GöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇ
        $detailSimpanan = Simpanan::with('anggota')
            ->whereYear('tanggal', $tahun)
            ->when(!empty($bulan), fn($q) => $q->whereMonth('tanggal', $bulan))
            ->orderByDesc('tanggal')
            ->take(10)
            ->get();

        // GöÇGöÇ Daftar angsuran dibayar (10 terakhir) GöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇ
        $detailAngsuran = Angsuran::with('pinjaman.anggota')
            ->where('status', 'sudah')
            ->whereYear('tanggal_bayar', $tahun)
            ->when(!empty($bulan), fn($q) => $q->whereMonth('tanggal_bayar', $bulan))
            ->orderByDesc('tanggal_bayar')
            ->take(10)
            ->get();

        // GöÇGöÇ Daftar pinjaman dicairkan (10 terakhir) GöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇ
        $detailPinjaman = Pinjaman::with('anggota')
            ->whereIn('status', ['disetujui'])
            ->whereYear('tanggal_persetujuan', $tahun)
            ->when(!empty($bulan), fn($q) => $q->whereMonth('tanggal_persetujuan', $bulan))
            ->orderByDesc('tanggal_persetujuan')
            ->take(10)
            ->get();

        // GöÇGöÇ Daftar tahun yang tersedia GöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇGöÇ
        $tahunTersedia = collect([
            Simpanan::selectRaw('YEAR(tanggal) as thn')->distinct()->pluck('thn'),
            Pinjaman::selectRaw('YEAR(tanggal_persetujuan) as thn')->whereNotNull('tanggal_persetujuan')->distinct()->pluck('thn'),
            Angsuran::selectRaw('YEAR(tanggal_bayar) as thn')->whereNotNull('tanggal_bayar')->distinct()->pluck('thn'),
        ])->flatten()->unique()->sort()->values();

        if ($tahunTersedia->isEmpty()) {
            $tahunTersedia = collect([now()->year]);
        }

        return view('koperasi.laporan.keuangan', compact(
            'tahun', 'bulan',
            'simpananPokok', 'simpananWajib', 'simpananSukarela', 'totalSimpananMasuk',
            'pinjamanDicairkan', 'jumlahPinjamanBaru',
            'totalAngsuranDiterima', 'totalBungaDiterima', 'totalPokokDiterima',
            'totalPemasukan', 'totalPengeluaran', 'arusKasBersih',
            'simpananPerBulan', 'angsuranPerBulan', 'pinjamanPerBulan',
            'detailSimpanan', 'detailAngsuran', 'detailPinjaman',
            'tahunTersedia',
        ));
    }

    public function cetak(Request $request): View
    {
        $tahun = (int) $request->input('tahun', now()->year);
        $bulan = $request->input('bulan', '');

        $scopeSimpanan = fn($q) => !empty($bulan)
            ? $q->whereYear('tanggal', $tahun)->whereMonth('tanggal', $bulan)
            : $q->whereYear('tanggal', $tahun);

        $scopePinjaman = fn($q) => !empty($bulan)
            ? $q->whereYear('tanggal_persetujuan', $tahun)->whereMonth('tanggal_persetujuan', $bulan)
            : $q->whereYear('tanggal_persetujuan', $tahun);

        $scopeAngsuran = fn($q) => !empty($bulan)
            ? $q->whereYear('tanggal_bayar', $tahun)->whereMonth('tanggal_bayar', $bulan)
            : $q->whereYear('tanggal_bayar', $tahun);

        $simpananPokok    = Simpanan::where('jenis_simpanan', 'pokok')->tap($scopeSimpanan)->sum('jumlah');
        $simpananWajib    = Simpanan::where('jenis_simpanan', 'wajib')->tap($scopeSimpanan)->sum('jumlah');
        $simpananSukarela = Simpanan::where('jenis_simpanan', 'sukarela')->tap($scopeSimpanan)->sum('jumlah');
        $totalSimpananMasuk = $simpananPokok + $simpananWajib + $simpananSukarela;

        $pinjamanDicairkan  = Pinjaman::whereIn('status', ['disetujui'])->tap($scopePinjaman)->sum('jumlah_pinjaman');
        $jumlahPinjamanBaru = Pinjaman::whereIn('status', ['disetujui'])->tap($scopePinjaman)->count();

        $totalAngsuranDiterima = Angsuran::where('status', 'sudah')->tap($scopeAngsuran)->sum('jumlah_angsuran');
        $totalBungaDiterima    = Angsuran::where('status', 'sudah')->tap($scopeAngsuran)->sum('bunga');
        $totalPokokDiterima    = Angsuran::where('status', 'sudah')->tap($scopeAngsuran)->sum('pokok');

        $totalPemasukan   = $totalSimpananMasuk + $totalAngsuranDiterima;
        $totalPengeluaran = $pinjamanDicairkan;
        $arusKasBersih    = $totalPemasukan - $totalPengeluaran;

        $detailSimpanan = Simpanan::with('anggota')
            ->tap($scopeSimpanan)->orderBy('tanggal')->get();

        $detailAngsuran = Angsuran::with('pinjaman.anggota')
            ->where('status', 'sudah')->tap($scopeAngsuran)->orderBy('tanggal_bayar')->get();

        $detailPinjaman = Pinjaman::with('anggota')
            ->whereIn('status', ['disetujui'])->tap($scopePinjaman)->orderBy('tanggal_persetujuan')->get();

        $profil = Pengaturan::semuaNilai();

        return view('koperasi.laporan.cetak', compact(
            'tahun', 'bulan', 'profil',
            'simpananPokok', 'simpananWajib', 'simpananSukarela', 'totalSimpananMasuk',
            'pinjamanDicairkan', 'jumlahPinjamanBaru',
            'totalAngsuranDiterima', 'totalBungaDiterima', 'totalPokokDiterima',
            'totalPemasukan', 'totalPengeluaran', 'arusKasBersih',
            'detailSimpanan', 'detailAngsuran', 'detailPinjaman',
        ));
    }
}
