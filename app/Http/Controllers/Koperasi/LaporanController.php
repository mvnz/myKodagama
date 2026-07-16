<?php

namespace App\Http\Controllers\Koperasi;

use App\Http\Controllers\Controller;

use App\Models\Koperasi\Simpanan;
use App\Models\Koperasi\Pinjaman;
use App\Models\Koperasi\Angsuran;
use App\Models\Koperasi\Anggota;
use App\Models\Koperasi\Pendapatan;
use App\Models\Koperasi\Pengeluaran;
use App\Models\Koperasi\Pengaturan;
use App\Models\Koperasi\Shu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LaporanController extends Controller
{
    public function index(Request $request): View
    {
        $tahun  = (int) $request->input('tahun',  now()->year);
        $bulan  = $request->input('bulan');   // null = seluruh tahun
        $bulanExprTanggal = $this->monthExpr('tanggal');
        $bulanExprBayar = $this->monthExpr('tanggal_bayar');
        $bulanExprPersetujuan = $this->monthExpr('tanggal_persetujuan');

        // G��G�� Ringkasan Simpanan G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��
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

        // G��G�� Pinjaman Dicairkan G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��
        $pinjamanDicairkan = Pinjaman::whereIn('status', ['disetujui'])
            ->whereYear('tanggal_persetujuan', $tahun)
            ->when(!empty($bulan), fn($q) => $q->whereMonth('tanggal_persetujuan', $bulan))
            ->sum('jumlah_pinjaman');

        $jumlahPinjamanBaru = Pinjaman::whereIn('status', ['disetujui'])
            ->whereYear('tanggal_persetujuan', $tahun)
            ->when(!empty($bulan), fn($q) => $q->whereMonth('tanggal_persetujuan', $bulan))
            ->count();

        // G��G�� Angsuran Diterima G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��
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

        // G��G�� Arus Kas Bersih G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��
        $totalPemasukan  = $totalSimpananMasuk + $totalAngsuranDiterima;
        $totalPengeluaran = $pinjamanDicairkan;
        $arusKasBersih   = $totalPemasukan - $totalPengeluaran;

        // G��G�� Detail Simpanan per bulan (untuk grafik/tabel) G��G��G��G��G��G��G��G��G��G��
        $simpananPerBulan = Simpanan::selectRaw(
            "{$bulanExprTanggal} as bulan_ke, SUM(jumlah) as total, jenis_simpanan"
            )
            ->whereYear('tanggal', $tahun)
            ->when(!empty($bulan), fn($q) => $q->whereMonth('tanggal', $bulan))
            ->groupBy('bulan_ke', 'jenis_simpanan')
            ->orderBy('bulan_ke')
            ->get();

        // G��G�� Detail Angsuran per bulan G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��
        $angsuranPerBulan = Angsuran::selectRaw(
            "{$bulanExprBayar} as bulan_ke, SUM(jumlah_angsuran) as total, SUM(pokok) as total_pokok, SUM(bunga) as total_bunga"
            )
            ->where('status', 'sudah')
            ->whereYear('tanggal_bayar', $tahun)
            ->when(!empty($bulan), fn($q) => $q->whereMonth('tanggal_bayar', $bulan))
            ->groupBy('bulan_ke')
            ->orderBy('bulan_ke')
            ->get()
            ->keyBy('bulan_ke');

        // G��G�� Pinjaman Baru per bulan G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��
        $pinjamanPerBulan = Pinjaman::selectRaw(
            "{$bulanExprPersetujuan} as bulan_ke, COUNT(*) as jumlah, SUM(jumlah_pinjaman) as total"
            )
            ->whereIn('status', ['disetujui'])
            ->whereYear('tanggal_persetujuan', $tahun)
            ->when(!empty($bulan), fn($q) => $q->whereMonth('tanggal_persetujuan', $bulan))
            ->groupBy('bulan_ke')
            ->orderBy('bulan_ke')
            ->get()
            ->keyBy('bulan_ke');

        // G��G�� Daftar transaksi simpanan (10 terakhir) G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��
        $detailSimpanan = Simpanan::with('anggota')
            ->whereYear('tanggal', $tahun)
            ->when(!empty($bulan), fn($q) => $q->whereMonth('tanggal', $bulan))
            ->orderByDesc('tanggal')
            ->take(10)
            ->get();

        // G��G�� Daftar angsuran dibayar (10 terakhir) G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��
        $detailAngsuran = Angsuran::with('pinjaman.anggota')
            ->where('status', 'sudah')
            ->whereYear('tanggal_bayar', $tahun)
            ->when(!empty($bulan), fn($q) => $q->whereMonth('tanggal_bayar', $bulan))
            ->orderByDesc('tanggal_bayar')
            ->take(10)
            ->get();

        // G��G�� Daftar pinjaman dicairkan (10 terakhir) G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��
        $detailPinjaman = Pinjaman::with('anggota')
            ->whereIn('status', ['disetujui'])
            ->whereYear('tanggal_persetujuan', $tahun)
            ->when(!empty($bulan), fn($q) => $q->whereMonth('tanggal_persetujuan', $bulan))
            ->orderByDesc('tanggal_persetujuan')
            ->take(10)
            ->get();

        // G��G�� Daftar tahun yang tersedia G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��G��
        $tahunTersedia = collect([
            Simpanan::whereNotNull('tanggal')->pluck('tanggal')->map(fn($tgl) => (int) date('Y', strtotime((string) $tgl))),
            Pinjaman::whereNotNull('tanggal_persetujuan')->pluck('tanggal_persetujuan')->map(fn($tgl) => (int) date('Y', strtotime((string) $tgl))),
            Angsuran::whereNotNull('tanggal_bayar')->pluck('tanggal_bayar')->map(fn($tgl) => (int) date('Y', strtotime((string) $tgl))),
        ])->flatten()->filter()->unique()->sort()->values();

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

    public function labaRugi(Request $request): View
    {
        $tahun = (int) $request->input('tahun', now()->year);
        $bulan = $request->input('bulan');

        $data = $this->buildLabaRugiData($tahun, $bulan);

        return view('koperasi.laporan.laba-rugi', $data);
    }

    public function cetakLabaRugi(Request $request): View
    {
        $tahun = (int) $request->input('tahun', now()->year);
        $bulan = $request->input('bulan');

        $data = $this->buildLabaRugiData($tahun, $bulan);
        $data['profil'] = Pengaturan::semuaNilai();

        return view('koperasi.laporan.cetak-laba-rugi', $data);
    }

    private function buildLabaRugiData(int $tahun, ?string $bulan): array
    {
        $bulan = filled($bulan) ? str_pad((string) $bulan, 2, '0', STR_PAD_LEFT) : '';

        $bungaMasuk = Angsuran::where('status', 'sudah')
            ->whereYear('tanggal_bayar', $tahun)
            ->when(!empty($bulan), fn($q) => $q->whereMonth('tanggal_bayar', $bulan))
            ->sum('bunga');

        $pendapatanLain = Pendapatan::whereYear('tanggal', $tahun)
            ->when(!empty($bulan), fn($q) => $q->whereMonth('tanggal', $bulan))
            ->selectRaw('kategori, SUM(jumlah) as total')
            ->groupBy('kategori')
            ->pluck('total', 'kategori');

        $pengeluaran = Pengeluaran::whereYear('tanggal', $tahun)
            ->when(!empty($bulan), fn($q) => $q->whereMonth('tanggal', $bulan))
            ->selectRaw('kategori, SUM(jumlah) as total')
            ->groupBy('kategori')
            ->pluck('total', 'kategori');

        $detailPendapatan = Pendapatan::query()
            ->whereYear('tanggal', $tahun)
            ->when(!empty($bulan), fn($q) => $q->whereMonth('tanggal', $bulan))
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();

        $detailPengeluaran = Pengeluaran::query()
            ->whereYear('tanggal', $tahun)
            ->when(!empty($bulan), fn($q) => $q->whereMonth('tanggal', $bulan))
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();

        $totalPendapatan = $bungaMasuk + $pendapatanLain->sum();
        $totalPengeluaran = $pengeluaran->sum();
        $shu = $totalPendapatan - $totalPengeluaran;

        $tahunList = collect([
            Angsuran::whereNotNull('tanggal_bayar')->pluck('tanggal_bayar')->map(fn($tgl) => (int) date('Y', strtotime((string) $tgl))),
            Pendapatan::whereNotNull('tanggal')->pluck('tanggal')->map(fn($tgl) => (int) date('Y', strtotime((string) $tgl))),
            Pengeluaran::whereNotNull('tanggal')->pluck('tanggal')->map(fn($tgl) => (int) date('Y', strtotime((string) $tgl))),
        ])->flatten()->filter()->unique()->sort()->values();

        if ($tahunList->isEmpty()) {
            $tahunList = collect([now()->year]);
        }

        $labelKategoriPendapatan = [
            'jasa_admin' => 'Jasa Administrasi',
            'provisi' => 'Provisi',
            'bunga_pinjaman' => 'Bunga Pinjaman',
            'bunga_simpanan' => 'Bunga Simpanan',
            'denda_angsuran' => 'Denda Angsuran',
            'lainnya' => 'Lainnya',
        ];

        $labelKategoriPengeluaran = [
            'atk' => 'ATK',
            'listrik_air' => 'Listrik/Air',
            'gaji_honor' => 'Gaji/Honor',
            'transport' => 'Transport',
            'pajak' => 'Pajak',
            'lainnya' => 'Lainnya',
        ];

        return [
            'tahun' => $tahun,
            'bulan' => $bulan,
            'tahunList' => $tahunList,
            'bungaMasuk' => $bungaMasuk,
            'pendapatanLain' => $pendapatanLain,
            'pengeluaran' => $pengeluaran,
            'totalPendapatan' => $totalPendapatan,
            'totalPengeluaran' => $totalPengeluaran,
            'shu' => $shu,
            'labelKategoriPendapatan' => $labelKategoriPendapatan,
            'labelKategoriPengeluaran' => $labelKategoriPengeluaran,
            'detailPendapatan' => $detailPendapatan,
            'detailPengeluaran' => $detailPengeluaran,
        ];
    }

    public function lpj(Request $request): View
    {
        $tahun = (int) $request->input('tahun', now()->year);
        $data = $this->buildLpjData($tahun);

        return view('koperasi.laporan.lpj', $data);
    }

    public function cetakLpj(Request $request): View
    {
        $tahun = (int) $request->input('tahun', now()->year);
        $data = $this->buildLpjData($tahun);

        return view('koperasi.laporan.cetak-lpj', $data);
    }

    private function buildLpjData(int $tahun): array
    {
        $awalTahun = now()->setYear($tahun)->startOfYear();
        $akhirTahun = now()->setYear($tahun)->endOfYear();

        $anggotaAwalTahun = Anggota::query()
            ->where(function ($q) use ($awalTahun, $tahun) {
                $q->where(function ($q2) use ($awalTahun) {
                    $q2->whereNotNull('tanggal_bergabung')
                        ->whereDate('tanggal_bergabung', '<', $awalTahun);
                })->orWhere(function ($q2) use ($tahun) {
                    $q2->whereNull('tanggal_bergabung')
                        ->whereYear('created_at', '<', $tahun);
                });
            })
            ->count();

        $anggotaBaru = Anggota::query()
            ->where(function ($q) use ($tahun) {
                $q->where(function ($q2) use ($tahun) {
                    $q2->whereNotNull('tanggal_bergabung')
                        ->whereYear('tanggal_bergabung', $tahun);
                })->orWhere(function ($q2) use ($tahun) {
                    $q2->whereNull('tanggal_bergabung')
                        ->whereYear('created_at', $tahun);
                });
            })
            ->count();

        $totalAnggota = Anggota::query()
            ->where(function ($q) use ($akhirTahun, $tahun) {
                $q->where(function ($q2) use ($akhirTahun) {
                    $q2->whereNotNull('tanggal_bergabung')
                        ->whereDate('tanggal_bergabung', '<=', $akhirTahun);
                })->orWhere(function ($q2) use ($tahun) {
                    $q2->whereNull('tanggal_bergabung')
                        ->whereYear('created_at', '<=', $tahun);
                });
            })
            ->count();

        $anggotaAktif = Anggota::query()->where('status', 'aktif')->count();
        $anggotaTidakAktif = max(0, $totalAnggota - $anggotaAktif);

        $simpananPokok = Simpanan::query()
            ->where('jenis_simpanan', 'pokok')
            ->whereYear('tanggal', $tahun)
            ->sum('jumlah');
        $simpananWajib = Simpanan::query()
            ->where('jenis_simpanan', 'wajib')
            ->whereYear('tanggal', $tahun)
            ->sum('jumlah');
        $simpananSukarela = Simpanan::query()
            ->where('jenis_simpanan', 'sukarela')
            ->whereYear('tanggal', $tahun)
            ->sum('jumlah');
        $totalSimpananTahun = $simpananPokok + $simpananWajib + $simpananSukarela;

        $totalSimpananKumulatif = Simpanan::query()
            ->whereDate('tanggal', '<=', $akhirTahun)
            ->sum('jumlah');

        $jumlahPinjamanDiajukan = Pinjaman::query()
            ->whereYear('tanggal_pengajuan', $tahun)
            ->count();

        $jumlahPinjamanDisetujui = Pinjaman::query()
            ->whereIn('status', ['disetujui', 'lunas'])
            ->whereYear('tanggal_persetujuan', $tahun)
            ->count();

        $nilaiPinjamanDicairkan = Pinjaman::query()
            ->whereIn('status', ['disetujui', 'lunas'])
            ->whereYear('tanggal_persetujuan', $tahun)
            ->sum('jumlah_pinjaman');

        $pinjamanAktif = Pinjaman::query()
            ->where('status', 'disetujui')
            ->withSum(['angsuran as pokok_terbayar' => fn($q) => $q->where('status', 'sudah')], 'pokok')
            ->get();

        $jumlahPinjamanAktif = $pinjamanAktif->count();
        $nilaiPinjamanAktif = $pinjamanAktif->sum(function ($pinjaman) {
            $sisa = (float) $pinjaman->jumlah_pinjaman - (float) ($pinjaman->pokok_terbayar ?? 0);
            return max($sisa, 0);
        });

        $totalPokokDiterima = Angsuran::query()
            ->where('status', 'sudah')
            ->whereYear('tanggal_bayar', $tahun)
            ->sum('pokok');

        $totalBungaDiterima = Angsuran::query()
            ->where('status', 'sudah')
            ->whereYear('tanggal_bayar', $tahun)
            ->sum('bunga');

        $totalAngsuranDiterima = Angsuran::query()
            ->where('status', 'sudah')
            ->whereYear('tanggal_bayar', $tahun)
            ->sum('jumlah_angsuran');

        $totalAngsuranTunggak = Angsuran::query()
            ->where('status', 'belum')
            ->whereDate('tanggal_jatuh_tempo', '<=', $akhirTahun)
            ->sum('jumlah_angsuran');

        $totalPemasukan = $totalSimpananTahun + $totalAngsuranDiterima;
        $totalPengeluaran = $nilaiPinjamanDicairkan;
        $arusKasBersih = $totalPemasukan - $totalPengeluaran;

        $bungaMasuk = $totalBungaDiterima;
        $pendapatanLain = Pendapatan::query()
            ->whereYear('tanggal', $tahun)
            ->sum('jumlah');
        $totalPendapatan = $bungaMasuk + $pendapatanLain;
        $totalBiayaOps = Pengeluaran::query()
            ->whereYear('tanggal', $tahun)
            ->sum('jumlah');
        $shuBersih = $totalPendapatan - $totalBiayaOps;

        $shuRecord = Shu::query()
            ->where('tahun', $tahun)
            ->latest('id')
            ->first();

        $tahunList = collect([
            Anggota::query()->whereNotNull('tanggal_bergabung')->pluck('tanggal_bergabung')->map(fn($tgl) => (int) date('Y', strtotime((string) $tgl))),
            Simpanan::query()->whereNotNull('tanggal')->pluck('tanggal')->map(fn($tgl) => (int) date('Y', strtotime((string) $tgl))),
            Pinjaman::query()->whereNotNull('tanggal_pengajuan')->pluck('tanggal_pengajuan')->map(fn($tgl) => (int) date('Y', strtotime((string) $tgl))),
            Angsuran::query()->whereNotNull('tanggal_bayar')->pluck('tanggal_bayar')->map(fn($tgl) => (int) date('Y', strtotime((string) $tgl))),
            Pendapatan::query()->whereNotNull('tanggal')->pluck('tanggal')->map(fn($tgl) => (int) date('Y', strtotime((string) $tgl))),
            Pengeluaran::query()->whereNotNull('tanggal')->pluck('tanggal')->map(fn($tgl) => (int) date('Y', strtotime((string) $tgl))),
            Shu::query()->pluck('tahun')->map(fn($t) => (int) $t),
        ])->flatten()->filter()->unique()->sortDesc()->values();

        if ($tahunList->isEmpty()) {
            $tahunList = collect([now()->year]);
        }

        $profil = Pengaturan::semuaNilai();

        return compact(
            'profil',
            'tahun',
            'tahunList',
            'anggotaAwalTahun',
            'anggotaBaru',
            'totalAnggota',
            'anggotaAktif',
            'anggotaTidakAktif',
            'simpananPokok',
            'simpananWajib',
            'simpananSukarela',
            'totalSimpananTahun',
            'totalSimpananKumulatif',
            'jumlahPinjamanDiajukan',
            'jumlahPinjamanDisetujui',
            'nilaiPinjamanDicairkan',
            'jumlahPinjamanAktif',
            'nilaiPinjamanAktif',
            'totalPokokDiterima',
            'totalBungaDiterima',
            'totalAngsuranDiterima',
            'totalAngsuranTunggak',
            'totalPemasukan',
            'totalPengeluaran',
            'arusKasBersih',
            'bungaMasuk',
            'pendapatanLain',
            'totalPendapatan',
            'totalBiayaOps',
            'shuBersih',
            'shuRecord',
        );
    }

    private function monthExpr(string $column): string
    {
        if (DB::getDriverName() === 'sqlite') {
            return "CAST(strftime('%m', {$column}) AS INTEGER)";
        }

        return "MONTH({$column})";
    }
}
