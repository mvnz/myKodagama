<?php

namespace App\Http\Controllers;

use App\Models\Simpanan;
use App\Models\Pinjaman;
use App\Models\Angsuran;
use App\Models\Anggota;
use App\Models\Pengaturan;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LaporanController extends Controller
{
    public function index(Request $request): View
    {
        $tahun  = (int) $request->input('tahun',  now()->year);
        $bulan  = $request->input('bulan');   // null = seluruh tahun

        // ── Ringkasan Simpanan ──────────────────────────────────────
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

        // ── Pinjaman Dicairkan ──────────────────────────────────────
        $pinjamanDicairkan = Pinjaman::whereIn('status', ['disetujui'])
            ->whereYear('tanggal_persetujuan', $tahun)
            ->when(!empty($bulan), fn($q) => $q->whereMonth('tanggal_persetujuan', $bulan))
            ->sum('jumlah_pinjaman');

        $jumlahPinjamanBaru = Pinjaman::whereIn('status', ['disetujui'])
            ->whereYear('tanggal_persetujuan', $tahun)
            ->when(!empty($bulan), fn($q) => $q->whereMonth('tanggal_persetujuan', $bulan))
            ->count();

        // ── Angsuran Diterima ───────────────────────────────────────
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

        // ── Arus Kas Bersih ─────────────────────────────────────────
        $totalPemasukan  = $totalSimpananMasuk + $totalAngsuranDiterima;
        $totalPengeluaran = $pinjamanDicairkan;
        $arusKasBersih   = $totalPemasukan - $totalPengeluaran;

        // ── Detail Simpanan per bulan (untuk grafik/tabel) ──────────
        $simpananPerBulan = Simpanan::selectRaw(
                'MONTH(tanggal) as bulan_ke, SUM(jumlah) as total, jenis_simpanan'
            )
            ->whereYear('tanggal', $tahun)
            ->when(!empty($bulan), fn($q) => $q->whereMonth('tanggal', $bulan))
            ->groupBy('bulan_ke', 'jenis_simpanan')
            ->orderBy('bulan_ke')
            ->get();

        // ── Detail Angsuran per bulan ───────────────────────────────
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

        // ── Pinjaman Baru per bulan ─────────────────────────────────
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

        // ── Daftar transaksi simpanan (10 terakhir) ─────────────────
        $detailSimpanan = Simpanan::with('anggota')
            ->whereYear('tanggal', $tahun)
            ->when(!empty($bulan), fn($q) => $q->whereMonth('tanggal', $bulan))
            ->orderByDesc('tanggal')
            ->take(10)
            ->get();

        // ── Daftar angsuran dibayar (10 terakhir) ───────────────────
        $detailAngsuran = Angsuran::with('pinjaman.anggota')
            ->where('status', 'sudah')
            ->whereYear('tanggal_bayar', $tahun)
            ->when(!empty($bulan), fn($q) => $q->whereMonth('tanggal_bayar', $bulan))
            ->orderByDesc('tanggal_bayar')
            ->take(10)
            ->get();

        // ── Daftar pinjaman dicairkan (10 terakhir) ─────────────────
        $detailPinjaman = Pinjaman::with('anggota')
            ->whereIn('status', ['disetujui'])
            ->whereYear('tanggal_persetujuan', $tahun)
            ->when(!empty($bulan), fn($q) => $q->whereMonth('tanggal_persetujuan', $bulan))
            ->orderByDesc('tanggal_persetujuan')
            ->take(10)
            ->get();

        // ── Daftar tahun yang tersedia ──────────────────────────────
        $tahunTersedia = collect([
            Simpanan::selectRaw('YEAR(tanggal) as thn')->distinct()->pluck('thn'),
            Pinjaman::selectRaw('YEAR(tanggal_persetujuan) as thn')->whereNotNull('tanggal_persetujuan')->distinct()->pluck('thn'),
            Angsuran::selectRaw('YEAR(tanggal_bayar) as thn')->whereNotNull('tanggal_bayar')->distinct()->pluck('thn'),
        ])->flatten()->unique()->sort()->values();

        if ($tahunTersedia->isEmpty()) {
            $tahunTersedia = collect([now()->year]);
        }

        return view('laporan.keuangan', compact(
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

        return view('laporan.cetak', compact(
            'tahun', 'bulan', 'profil',
            'simpananPokok', 'simpananWajib', 'simpananSukarela', 'totalSimpananMasuk',
            'pinjamanDicairkan', 'jumlahPinjamanBaru',
            'totalAngsuranDiterima', 'totalBungaDiterima', 'totalPokokDiterima',
            'totalPemasukan', 'totalPengeluaran', 'arusKasBersih',
            'detailSimpanan', 'detailAngsuran', 'detailPinjaman',
        ));
    }
}
