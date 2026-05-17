<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\Angsuran;
use App\Models\Pinjaman;
use App\Models\Simpanan;
use App\Models\Pengaturan;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $user->loadMissing('roleModel.permissions');
        $roleSlug = $user->roleModel?->slug ?? $user->role ?? '';

        return match (true) {
            $roleSlug === 'anggota'                   => $this->dashboardAnggota($user),
            $roleSlug === 'ketua'                     => $this->dashboardKetua(),
            in_array($roleSlug, ['admin', 'petugas']) => $this->dashboardAdmin(),
            default                                   => $this->dashboardAdmin(),
        };
    }

    /* ─── ADMIN / BENDAHARA ─── */
    private function dashboardAdmin()
    {
        $totalAnggota     = Anggota::where('status', 'aktif')->count();
        $totalSimpanan    = Simpanan::sum('jumlah');
        $totalPinjaman    = Pinjaman::whereIn('status', ['disetujui'])->sum('jumlah_pinjaman');
        $pengajuanBaru    = Pinjaman::where('status', 'diajukan')->count();
        $anggotaBaru      = Anggota::whereMonth('tanggal_bergabung', now()->month)->count();
        $angsuranJatuhTempoHariIni = Angsuran::where('tanggal_jatuh_tempo', today())
            ->where('status', 'belum')->count();

        $pinjamanTerbaru  = Pinjaman::with('anggota')->latest()->take(5)->get();
        $simpananTerbaru  = Simpanan::with('anggota')->latest()->take(5)->get();
        $angsuranMendatang = Angsuran::with('pinjaman.anggota')
            ->where('status', 'belum')
            ->where('tanggal_jatuh_tempo', '>=', today())
            ->orderBy('tanggal_jatuh_tempo')
            ->take(5)->get();

        return view('dashboard.index', compact(
            'totalAnggota', 'totalSimpanan', 'totalPinjaman',
            'pengajuanBaru', 'anggotaBaru', 'angsuranJatuhTempoHariIni',
            'pinjamanTerbaru', 'simpananTerbaru', 'angsuranMendatang'
        ));
    }

    /* ─── KETUA ─── */
    private function dashboardKetua()
    {
        $totalAnggota  = Anggota::where('status', 'aktif')->count();
        $totalSimpanan = Simpanan::sum('jumlah');
        $totalPinjaman = Pinjaman::whereIn('status', ['disetujui'])->sum('jumlah_pinjaman');

        $menungguVerifikasi = Pinjaman::with('anggota')
            ->where('status', 'diajukan')
            ->latest()
            ->get();

        $menungguPersetujuan = Pinjaman::with('anggota')
            ->where('status', 'diverifikasi')
            ->latest()
            ->get();

        $angsuranTelat = Angsuran::with('pinjaman.anggota')
            ->where('status', 'belum')
            ->where('tanggal_jatuh_tempo', '<', today())
            ->orderBy('tanggal_jatuh_tempo')
            ->take(10)->get();

        $permissions = Auth::user()->roleModel?->permissions ?? collect();

        return view('dashboard.ketua', compact(
            'totalAnggota', 'totalSimpanan', 'totalPinjaman',
            'menungguVerifikasi', 'menungguPersetujuan', 'angsuranTelat',
            'permissions'
        ));
    }

    /* ─── ANGGOTA ─── */
    private function dashboardAnggota($user)
    {
        $anggota = $user->anggota;

        if (! $anggota) {
            // Anggota belum dikaitkan ke user
            return view('dashboard.anggota-belum-terdaftar');
        }

        $anggota->load([
            'simpanan' => fn($q) => $q->orderBy('tanggal', 'desc'),
            'pinjaman' => fn($q) => $q->orderBy('tanggal_pengajuan', 'desc'),
            'pinjaman.angsuran' => fn($q) => $q->orderBy('ke'),
            'unitKerja',
        ]);

        $totalSimpanan   = $anggota->simpanan->sum('jumlah');
        $totalPokok      = $anggota->simpanan->where('jenis_simpanan', 'pokok')->sum('jumlah');
        $totalWajib      = $anggota->simpanan->where('jenis_simpanan', 'wajib')->sum('jumlah');
        $totalSukarela   = $anggota->simpanan->where('jenis_simpanan', 'sukarela')->sum('jumlah');

        $semuaPinjaman   = $anggota->pinjaman;
        $pinjamanAktif   = $semuaPinjaman->whereIn('status', ['diajukan', 'diverifikasi', 'disetujui']);

        $totalSisaHutang = $semuaPinjaman->where('status', 'disetujui')
            ->map(fn($p) => max(0, $p->jumlah_pinjaman - $p->angsuran->where('status', 'sudah')->sum('jumlah_angsuran')))
            ->sum();

        $angsuranMendatang = $semuaPinjaman
            ->flatMap(fn($p) => $p->angsuran->where('status', 'belum'))
            ->sortBy('tanggal_jatuh_tempo')
            ->take(5);

        $wajibPerJenis = collect(json_decode(Pengaturan::get('sp_simpanan_wajib_per_jenis', '[]'), true) ?: [])
            ->firstWhere('jenis', $anggota->jenis_anggota);

        return view('dashboard.anggota', compact(
            'anggota', 'totalSimpanan', 'totalPokok', 'totalWajib', 'totalSukarela',
            'semuaPinjaman', 'pinjamanAktif', 'totalSisaHutang',
            'angsuranMendatang', 'wajibPerJenis'
        ));
    }

    public function strukturOrganisasi()
    {
        $pgrs = Pengaturan::where('grup', 'pengurus')->pluck('nilai', 'kunci');

        $struktur = json_decode($pgrs->get('pgrs_struktur', '[]'), true) ?: [];

        if (empty($struktur)) {
            $ketua = $pgrs->get('pgrs_ketua_nama', '');
            if ($ketua) $struktur[] = ['jabatan' => 'Ketua', 'nama' => $ketua, 'periode' => $pgrs->get('pgrs_ketua_periode', ''), 'baris' => 1];
            $wakil = $pgrs->get('pgrs_wakil_nama', '');
            if ($wakil) $struktur[] = ['jabatan' => 'Wakil Ketua', 'nama' => $wakil, 'periode' => $pgrs->get('pgrs_wakil_periode', ''), 'baris' => 2];
            foreach (json_decode($pgrs->get('pgrs_sekretaris', '[]'), true) ?: [] as $s) {
                if (!empty($s['nama'])) $struktur[] = ['jabatan' => 'Sekretaris', 'nama' => $s['nama'], 'periode' => $s['periode'] ?? '', 'baris' => 3];
            }
            foreach (json_decode($pgrs->get('pgrs_bendahara', '[]'), true) ?: [] as $b) {
                if (!empty($b['nama'])) $struktur[] = ['jabatan' => 'Bendahara', 'nama' => $b['nama'], 'periode' => $b['periode'] ?? '', 'baris' => 3];
            }
        }

        $grouped   = collect($struktur)->groupBy('baris')->sortKeys()->all();
        $skNomor   = $pgrs->get('pgrs_sk_nomor', '');
        $skTanggal = $pgrs->get('pgrs_sk_tanggal', '');

        return view('dashboard.struktur-organisasi', compact('grouped', 'skNomor', 'skTanggal'));
    }
}
