<?php

namespace App\Http\Controllers;

use App\Models\Pengaturan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class PengaturanController extends Controller
{
    public function index(): View
    {
        $this->authorize('pengaturan.aplikasi');
        $pengaturan = Pengaturan::orderBy('grup')->orderBy('id')->get()->keyBy('kunci');

        return view('pengaturan.aplikasi', compact('pengaturan'));
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'pengaturan'            => ['required', 'array'],
            'pengaturan.*'          => ['nullable', 'string', 'max:500'],
            'pengaturan.deskripsi'  => ['nullable', 'string', 'max:1000'],
        ]);

        foreach ($request->input('pengaturan', []) as $kunci => $nilai) {
            Pengaturan::set($kunci, $nilai);
        }

        return redirect()->route('pengaturan.aplikasi')
            ->with('sukses', 'Pengaturan aplikasi berhasil disimpan.');
    }

    public function simpanPinjam(): View
    {
        $pengaturan = Pengaturan::where('grup', 'simpan_pinjam')
            ->orderBy('id')
            ->get()
            ->keyBy('kunci');

        $wajibPerJenis = json_decode(
            $pengaturan->get('sp_simpanan_wajib_per_jenis')?->nilai ?? '[]',
            true
        ) ?: [['jenis' => '', 'nominal' => 0]];

        $jenisPinjaman = json_decode(
            $pengaturan->get('sp_jenis_pinjaman')?->nilai ?? '[]',
            true
        ) ?: [];

        return view('pengaturan.simpan-pinjam', compact('pengaturan', 'wajibPerJenis', 'jenisPinjaman'));
    }

    public function updateSimpanPinjam(Request $request): RedirectResponse
    {
        $request->validate([
            'pengaturan'                       => ['nullable', 'array'],
            'pengaturan.*'                     => ['nullable', 'numeric', 'min:0'],
            'wajib_per_jenis'                  => ['nullable', 'array'],
            'wajib_per_jenis.*.jenis'          => ['nullable', 'string', 'max:100'],
            'wajib_per_jenis.*.nominal'        => ['nullable', 'numeric', 'min:0'],
            'jenis_pinjaman'                   => ['nullable', 'array'],
            'jenis_pinjaman.*.nama'            => ['nullable', 'string', 'max:100'],
            'jenis_pinjaman.*.bunga'           => ['nullable', 'numeric', 'min:0', 'max:100'],
            'jenis_pinjaman.*.min_pinjaman'    => ['nullable', 'numeric', 'min:0'],
            'jenis_pinjaman.*.maks_pinjaman'   => ['nullable', 'numeric', 'min:0'],
            'jenis_pinjaman.*.min_jangka'      => ['nullable', 'integer', 'min:1'],
            'jenis_pinjaman.*.maks_jangka'     => ['nullable', 'integer', 'min:1'],
        ]);

        foreach ($request->input('pengaturan', []) as $kunci => $nilai) {
            Pengaturan::set($kunci, $nilai, 'simpan_pinjam');
        }

        // Simpan wajib per jenis sebagai JSON
        $wajibPerJenis = collect($request->input('wajib_per_jenis', []))
            ->filter(fn($r) => !empty($r['jenis']))
            ->map(fn($r) => [
                'jenis'   => trim($r['jenis']),
                'nominal' => (int) ($r['nominal'] ?? 0),
            ])
            ->values()
            ->toArray();

        Pengaturan::set('sp_simpanan_wajib_per_jenis', json_encode($wajibPerJenis), 'simpan_pinjam');

        // Simpan jenis pinjaman sebagai JSON
        $jenisPinjaman = collect($request->input('jenis_pinjaman', []))
            ->filter(fn($r) => !empty($r['nama']))
            ->map(fn($r) => [
                'nama'         => trim($r['nama']),
                'bunga'        => (float) ($r['bunga'] ?? 0),
                'min_pinjaman' => (int) ($r['min_pinjaman'] ?? 0),
                'maks_pinjaman'=> (int) ($r['maks_pinjaman'] ?? 0),
                'min_jangka'   => (int) ($r['min_jangka'] ?? 1),
                'maks_jangka'  => (int) ($r['maks_jangka'] ?? 12),
            ])
            ->values()
            ->toArray();

        Pengaturan::set('sp_jenis_pinjaman', json_encode($jenisPinjaman), 'simpan_pinjam');

        return redirect()->route('pengaturan.simpan-pinjam')
            ->with('sukses', 'Pengaturan simpan pinjam berhasil disimpan.');
    }

    public function pengurus(): View
    {
        $pengaturan = Pengaturan::where('grup', 'pengurus')
            ->orderBy('id')
            ->get()
            ->keyBy('kunci');

        $struktur = json_decode(Pengaturan::get('pgrs_struktur', '[]'), true);

        // Auto-migrasi dari format lama jika struktur baru masih kosong
        if (empty($struktur)) {
            $p = $pengaturan;
            $ketua = $p->get('pgrs_ketua_nama')?->nilai ?? '';
            if ($ketua) {
                $struktur[] = ['jabatan' => 'Ketua', 'nama' => $ketua, 'periode' => $p->get('pgrs_ketua_periode')?->nilai ?? '', 'baris' => 1];
            }
            $wakil = $p->get('pgrs_wakil_nama')?->nilai ?? '';
            if ($wakil) {
                $struktur[] = ['jabatan' => 'Wakil Ketua', 'nama' => $wakil, 'periode' => $p->get('pgrs_wakil_periode')?->nilai ?? '', 'baris' => 2];
            }
            foreach (json_decode($p->get('pgrs_sekretaris')?->nilai ?? '[]', true) as $s) {
                if (!empty($s['nama'])) $struktur[] = ['jabatan' => 'Sekretaris', 'nama' => $s['nama'], 'periode' => $s['periode'] ?? '', 'baris' => 3];
            }
            foreach (json_decode($p->get('pgrs_bendahara')?->nilai ?? '[]', true) as $b) {
                if (!empty($b['nama'])) $struktur[] = ['jabatan' => 'Bendahara', 'nama' => $b['nama'], 'periode' => $b['periode'] ?? '', 'baris' => 3];
            }
            for ($n = 1; $n <= 3; $n++) {
                $nama = $p->get("pgrs_pengawas{$n}_nama")?->nilai ?? '';
                if ($nama) $struktur[] = ['jabatan' => "Pengawas $n", 'nama' => $nama, 'periode' => '', 'baris' => 4];
            }
        }

        if (empty($struktur)) {
            $struktur = [['jabatan' => '', 'nama' => '', 'periode' => '', 'baris' => 1]];
        }

        return view('pengaturan.pengurus', compact('pengaturan', 'struktur'));
    }

    public function updatePengurus(Request $request): RedirectResponse
    {
        $request->validate([
            'struktur'              => ['nullable', 'array'],
            'struktur.*.jabatan'    => ['nullable', 'string', 'max:100'],
            'struktur.*.nama'       => ['nullable', 'string', 'max:100'],
            'struktur.*.periode'    => ['nullable', 'string', 'max:50'],
            'struktur.*.baris'      => ['nullable', 'integer', 'min:1', 'max:20'],
            'pengaturan'            => ['nullable', 'array'],
            'pengaturan.*'          => ['nullable', 'string', 'max:200'],
            'pengaturan.pgrs_sk_tanggal' => ['nullable', 'date'],
        ]);

        $struktur = collect($request->input('struktur', []))
            ->filter(fn($r) => !empty($r['jabatan']) || !empty($r['nama']))
            ->map(fn($r) => [
                'jabatan' => $r['jabatan'] ?? '',
                'nama'    => $r['nama'] ?? '',
                'periode' => $r['periode'] ?? '',
                'baris'   => (int) ($r['baris'] ?? 1),
            ])
            ->values()
            ->toArray();

        Pengaturan::set('pgrs_struktur', json_encode($struktur, JSON_UNESCAPED_UNICODE), 'pengurus');

        foreach ($request->input('pengaturan', []) as $kunci => $nilai) {
            Pengaturan::set($kunci, $nilai);
        }

        return redirect()->route('pengaturan.pengurus')
            ->with('sukses', 'Data pengurus koperasi berhasil disimpan.');
    }

    /* ====================================================================
     * NOTIFIKASI WHATSAPP
     * ==================================================================*/
    public function notifikasiWa(): View
    {
        $pengaturan = Pengaturan::where('grup', 'notifikasi_wa')
            ->get()->keyBy('kunci');

        return view('pengaturan.notifikasi-wa', compact('pengaturan'));
    }

    public function updateNotifikasiWa(Request $request): RedirectResponse
    {
        $request->validate([
            'wa_token'          => ['nullable', 'string', 'max:300'],
            'wa_nomor_pengirim' => ['nullable', 'string', 'max:20'],
        ]);

        $fields = [
            'wa_aktif', 'wa_provider', 'wa_token', 'wa_nomor_pengirim',
            'wa_notif_pinjaman_diajukan', 'wa_notif_pinjaman_disetujui',
            'wa_notif_pinjaman_ditolak',
            'wa_notif_angsuran_jatuh_tempo', 'wa_notif_angsuran_terlambat',
            'wa_notif_simpanan',
            'wa_template_pinjaman_diajukan', 'wa_template_pinjaman_disetujui',
            'wa_template_pinjaman_ditolak', 'wa_template_angsuran_jatuh_tempo',
        ];

        // Toggle fields (checkbox) — jika tidak ada di request berarti off
        $toggleFields = [
            'wa_aktif', 'wa_notif_pinjaman_diajukan', 'wa_notif_pinjaman_disetujui',
            'wa_notif_pinjaman_ditolak', 'wa_notif_angsuran_jatuh_tempo',
            'wa_notif_angsuran_terlambat', 'wa_notif_simpanan',
        ];

        foreach ($fields as $kunci) {
            $nilai = in_array($kunci, $toggleFields)
                ? ($request->has($kunci) ? '1' : '0')
                : $request->input($kunci, '');
            Pengaturan::set($kunci, $nilai, 'notifikasi_wa');
        }

        return redirect()->route('pengaturan.notifikasi-wa')
            ->with('sukses', 'Pengaturan notifikasi WhatsApp berhasil disimpan.');
    }

    public function testNotifikasiWa(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'nomor' => ['required', 'string', 'min:8', 'max:20'],
        ]);

        $token   = Pengaturan::get('wa_token');
        $pengirim = Pengaturan::get('wa_nomor_pengirim');

        if (!$token) {
            return response()->json(['sukses' => false, 'pesan' => 'Token API belum dikonfigurasi.']);
        }

        $nomor = preg_replace('/[^0-9]/', '', $request->nomor);
        if (str_starts_with($nomor, '0')) {
            $nomor = '62' . substr($nomor, 1);
        }

        $pesan = 'Halo! Ini adalah pesan uji coba notifikasi WhatsApp dari sistem koperasi. Konfigurasi berhasil.';

        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => $token,
            ])->post('https://api.fonnte.com/send', [
                'target'  => $nomor,
                'message' => $pesan,
            ]);

            $data = $response->json();

            if ($response->ok() && ($data['status'] ?? false)) {
                return response()->json(['sukses' => true, 'pesan' => 'Pesan uji berhasil dikirim ke ' . $nomor]);
            }

            return response()->json(['sukses' => false, 'pesan' => $data['reason'] ?? 'Pengiriman gagal.']);
        } catch (\Exception $e) {
            return response()->json(['sukses' => false, 'pesan' => 'Koneksi gagal: ' . $e->getMessage()]);
        }
    }
}
