<?php

namespace App\Http\Controllers\Koperasi;

use App\Http\Controllers\Controller;

use App\Models\Koperasi\Pinjaman;
use App\Models\Koperasi\Anggota;
use App\Models\Koperasi\Angsuran;
use App\Http\Controllers\Concerns\CatatAktivitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class PinjamanController extends Controller
{
    use CatatAktivitas;
    public function index(Request $request)
    {
        $cari   = $request->input('cari');
        $status = $request->input('status');
        $user   = Auth::user();
        $anggotaId = $user->roleModel?->slug === 'anggota' ? $user->anggota_id : null;

        $pinjaman = Pinjaman::with('anggota')
            ->when($anggotaId, fn($q) => $q->where('anggota_id', $anggotaId))
            ->when($cari, function ($q) use ($cari) {
                $q->where('nomor_pinjaman', 'like', "%$cari%")
                    ->orWhereHas('anggota', fn($aq) => $aq->where('nama', 'like', "%$cari%"));
            })
            ->when($status, fn($q) => $q->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $statsPinjaman = [
            'diajukan'   => Pinjaman::when($anggotaId, fn($q) => $q->where('anggota_id', $anggotaId))->whereIn('status', ['diajukan', 'diverifikasi'])->count(),
            'disetujui'  => Pinjaman::when($anggotaId, fn($q) => $q->where('anggota_id', $anggotaId))->where('status', 'disetujui')->count(),
            'lunas'      => Pinjaman::when($anggotaId, fn($q) => $q->where('anggota_id', $anggotaId))->where('status', 'lunas')->count(),
            'nilai_aktif'=> Pinjaman::when($anggotaId, fn($q) => $q->where('anggota_id', $anggotaId))->where('status', 'disetujui')->sum('jumlah_pinjaman'),
        ];

        return view('koperasi.pinjaman.index', compact('pinjaman', 'cari', 'status', 'statsPinjaman'));
    }

    public function create()
    {
        $user = Auth::user();
        $anggotaSaya = null;

        if ($user->roleModel?->slug === 'anggota') {
            $dataAnggota = Anggota::where('id', $user->anggota_id)->where('status', 'aktif')->first();

            if (!$dataAnggota) {
                return redirect()->route('pinjaman.index')
                    ->with('error', 'Data anggota Anda tidak ditemukan atau tidak aktif.');
            }

            $pinjamanAktif = $dataAnggota->pinjaman()
                ->whereIn('status', ['diajukan', 'diverifikasi', 'disetujui'])
                ->exists();

            if ($pinjamanAktif) {
                return redirect()->route('pinjaman.index')
                    ->with('pinjaman_aktif', true);
            }

            $anggotaSaya = $dataAnggota;
            $anggota = collect();
        } else {
            $anggota = Anggota::where('status', 'aktif')
                ->whereDoesntHave('pinjaman', fn($q) => $q->where('status', 'disetujui'))
                ->orderBy('nama')
                ->get();
        }

        $jenisPinjaman = collect(json_decode(
            \App\Models\Koperasi\Pengaturan::get('sp_jenis_pinjaman', '[]'),
            true
        ) ?: []);

        return view('koperasi.pinjaman.tambah', compact('anggota', 'jenisPinjaman', 'anggotaSaya'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'anggota_id'     => 'required|exists:anggota,id',
            'jenis_pinjaman' => 'nullable|string|max:100',
            'jumlah_pinjaman'=> 'required|numeric|min:100000',
            'jangka_waktu'   => 'required|integer|min:1|max:60',
            'bunga'          => 'required|numeric|min:0|max:30',
            'tujuan'         => 'required|string',
        ], [
            'anggota_id.required'      => 'Anggota wajib dipilih.',
            'jumlah_pinjaman.required' => 'Jumlah pinjaman wajib diisi.',
            'jumlah_pinjaman.min'      => 'Jumlah minimal Rp 100.000.',
            'jangka_waktu.required'    => 'Jangka waktu wajib diisi.',
            'bunga.required'           => 'Bunga wajib diisi.',
            'tujuan.required'          => 'Tujuan pinjaman wajib diisi.',
        ]);

        $validated['nomor_pinjaman']   = $this->generateNomor();
        $validated['tanggal_pengajuan'] = today();
        $validated['status']            = 'diajukan';
        $validated['user_id']           = Auth::id();

        // Hitung cicilan per bulan (flat rate)
        $pokok  = $validated['jumlah_pinjaman'] / $validated['jangka_waktu'];
        $bunga  = ($validated['jumlah_pinjaman'] * $validated['bunga'] / 100);
        $validated['cicilan_per_bulan'] = round($pokok + $bunga, 2);

        $pinjaman = Pinjaman::create($validated);
        $pinjaman->load('anggota');

        $this->catatLog('tambah', 'Pinjaman', $pinjaman->id,
            $pinjaman->anggota->nama ?? '-',
            'Pengajuan pinjaman ' . $pinjaman->nomor_pinjaman . ' Rp ' . number_format($pinjaman->jumlah_pinjaman, 0, ',', '.'),
            null, $pinjaman->toArray());

        return redirect()->route('pinjaman.index')
            ->with('sukses', 'Pengajuan pinjaman berhasil disimpan.');
    }

    public function show(Pinjaman $pinjaman)
    {
        $user = Auth::user();
        if ($user->role === 'anggota' && $user->anggota_id !== $pinjaman->anggota_id) {
            abort(403);
        }
        $pinjaman->load(['anggota', 'angsuran', 'petugas']);
        return view('koperasi.pinjaman.detail', compact('pinjaman'));
    }

    public function edit(Pinjaman $pinjaman)
    {
        $anggota = Anggota::where('status', 'aktif')->orderBy('nama')->get();
        return view('koperasi.pinjaman.ubah', compact('pinjaman', 'anggota'));
    }

    public function update(Request $request, Pinjaman $pinjaman)
    {
        $validated = $request->validate([
            'status'              => 'required|in:diajukan,diverifikasi,disetujui,ditolak,lunas',
            'tanggal_verifikasi'  => 'nullable|date',
            'tanggal_persetujuan' => 'nullable|date',
        ]);

        $statusLama = $pinjaman->status;
        $dataLama   = $pinjaman->toArray();
        $pinjaman->load('anggota');
        $pinjaman->update($validated);

        $aksi = match ($validated['status']) {
            'diverifikasi' => 'verifikasi',
            'disetujui'    => 'setujui',
            'ditolak'      => 'tolak',
            default        => 'ubah',
        };
        $this->catatLog($aksi, 'Pinjaman', $pinjaman->id,
            $pinjaman->anggota->nama ?? '-',
            'Status pinjaman ' . $pinjaman->nomor_pinjaman . ' diubah dari ' . $statusLama . ' ke ' . $validated['status'],
            $dataLama, $pinjaman->fresh()->toArray());

        // Buat jadwal angsuran ketika pinjaman disetujui
        if ($validated['status'] === 'disetujui' && $statusLama !== 'disetujui') {
            $this->buatJadwalAngsuran($pinjaman);
        }

        return redirect()->route('pinjaman.show', $pinjaman)
            ->with('sukses', 'Status pinjaman berhasil diperbarui.');
    }

    /** Aksi cepat: verifikasi pinjaman (diajukan GåÆ diverifikasi) */
    public function verifikasi(Pinjaman $pinjaman)
    {
        if ($pinjaman->status !== 'diajukan') {
            return back()->with('galat', 'Pinjaman ini tidak bisa diverifikasi (status: ' . $pinjaman->status . ').');
        }

        $pinjaman->load('anggota');
        $dataLama = $pinjaman->toArray();
        $pinjaman->update([
            'status'             => 'diverifikasi',
            'tanggal_verifikasi' => today(),
            'verifikator_id'     => Auth::id(),
        ]);

        $this->catatLog('verifikasi', 'Pinjaman', $pinjaman->id,
            $pinjaman->anggota->nama ?? '-',
            'Pinjaman ' . $pinjaman->nomor_pinjaman . ' diverifikasi',
            $dataLama, $pinjaman->fresh()->toArray());

        return redirect()->route('pinjaman.show', $pinjaman)
            ->with('sukses', 'Pinjaman berhasil diverifikasi dan siap untuk disetujui.');
    }

    /** Aksi cepat: setujui pinjaman (diverifikasi GåÆ disetujui) */
    public function setujui(Request $request, Pinjaman $pinjaman)
    {
        if ($pinjaman->status !== 'diverifikasi') {
            return back()->with('galat', 'Pinjaman harus diverifikasi terlebih dahulu sebelum disetujui.');
        }

        $request->validate(['tanggal_persetujuan' => 'nullable|date']);

        $pinjaman->load('anggota');
        $dataLama = $pinjaman->toArray();
        $pinjaman->update([
            'status'              => 'disetujui',
            'tanggal_persetujuan' => $request->input('tanggal_persetujuan', today()),
        ]);

        $this->catatLog('setujui', 'Pinjaman', $pinjaman->id,
            $pinjaman->anggota->nama ?? '-',
            'Pinjaman ' . $pinjaman->nomor_pinjaman . ' disetujui',
            $dataLama, $pinjaman->fresh()->toArray());

        $this->buatJadwalAngsuran($pinjaman->fresh());

        return redirect()->route('pinjaman.show', $pinjaman)
            ->with('sukses', 'Pinjaman berhasil disetujui. Jadwal angsuran telah dibuat.');
    }

    /** Aksi cepat: tolak pinjaman */
    public function tolak(Request $request, Pinjaman $pinjaman)
    {
        if (!in_array($pinjaman->status, ['diajukan', 'diverifikasi'])) {
            return back()->with('galat', 'Pinjaman ini tidak bisa ditolak.');
        }

        $pinjaman->load('anggota');
        $dataLama = $pinjaman->toArray();
        $pinjaman->update(['status' => 'ditolak']);

        $this->catatLog('tolak', 'Pinjaman', $pinjaman->id,
            $pinjaman->anggota->nama ?? '-',
            'Pinjaman ' . $pinjaman->nomor_pinjaman . ' ditolak',
            $dataLama, $pinjaman->fresh()->toArray());

        return redirect()->route('pinjaman.show', $pinjaman)
            ->with('sukses', 'Pinjaman telah ditolak.');
    }

    public function destroy(Pinjaman $pinjaman)
    {
        $this->authorize('pinjaman.hapus');
        $pinjaman->load('anggota');
        $dataLama = $pinjaman->toArray();
        $label    = $pinjaman->anggota->nama ?? '-';
        $nomor    = $pinjaman->nomor_pinjaman;

        $pinjaman->delete();

        $this->catatLog('hapus', 'Pinjaman', null, $label,
            'Pinjaman ' . $nomor . ' dihapus', $dataLama);

        return redirect()->route('pinjaman.index')
            ->with('sukses', 'Data pinjaman berhasil dihapus.');
    }

    private function generateNomor(): string
    {
        $tahun  = Carbon::now()->format('Y');
        $bulan  = Carbon::now()->format('m');
        $urutan = Pinjaman::whereYear('tanggal_pengajuan', $tahun)->count() + 1;
        return 'PIN-' . $tahun . $bulan . '-' . str_pad($urutan, 4, '0', STR_PAD_LEFT);
    }

    private function buatJadwalAngsuran(Pinjaman $pinjaman): void
    {
        $pokok         = $pinjaman->jumlah_pinjaman / $pinjaman->jangka_waktu;
        $bunga         = ($pinjaman->jumlah_pinjaman * $pinjaman->bunga / 100);
        $cicilan       = round($pokok + $bunga, 2);
        $tanggalMulai  = $pinjaman->tanggal_persetujuan ?? today();

        for ($i = 1; $i <= $pinjaman->jangka_waktu; $i++) {
            Angsuran::create([
                'pinjaman_id'       => $pinjaman->id,
                'ke'                => $i,
                'jumlah_angsuran'   => $cicilan,
                'pokok'             => round($pokok, 2),
                'bunga'             => round($bunga, 2),
                'tanggal_jatuh_tempo' => Carbon::parse($tanggalMulai)->addMonths($i),
                'status'            => 'belum',
            ]);
        }
    }
}
