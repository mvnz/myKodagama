<?php

namespace App\Http\Controllers\Koperasi;

use App\Http\Controllers\Controller;

use App\Models\Koperasi\Angsuran;
use App\Models\Koperasi\Pinjaman;
use App\Support\CsvImportReader;
use App\Http\Controllers\Concerns\CatatAktivitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AngsuranController extends Controller
{
    use CatatAktivitas;
    public function index(Request $request)
    {
        $cari   = $request->input('cari');
        $status = $request->input('status');
        $allowedSort = ['created_at', 'ke', 'tanggal_jatuh_tempo', 'tanggal_bayar', 'status'];
        $sortBy  = in_array($request->input('sort'), $allowedSort, true) ? $request->input('sort') : 'created_at';
        $sortDir = $request->input('dir') === 'asc' ? 'asc' : 'desc';
        $user   = Auth::user();
        $anggotaId = $user->roleModel?->slug === 'anggota' ? $user->anggota_id : null;

        $angsuran = Angsuran::with('pinjaman.anggota')
            ->when($anggotaId, fn($q) => $q->whereHas('pinjaman', fn($pq) => $pq->where('anggota_id', $anggotaId)))
            ->when($cari, function ($q) use ($cari) {
                $q->whereHas('pinjaman', function($pq) use ($cari) {
                    $pq->where('nomor_pinjaman', 'like', "%$cari%")
                        ->orWhereHas('anggota', fn($aq) => $aq->where('nama', 'like', "%$cari%"));
                });
            })
            ->when($status, fn($q) => $q->where('status', $status))
            ->orderBy($sortBy, $sortDir)
            ->paginate(15)
            ->withQueryString();

        $statsAngsuran = [
            'total'     => Angsuran::when($anggotaId, fn($q) => $q->whereHas('pinjaman', fn($pq) => $pq->where('anggota_id', $anggotaId)))->count(),
            'sudah'     => Angsuran::when($anggotaId, fn($q) => $q->whereHas('pinjaman', fn($pq) => $pq->where('anggota_id', $anggotaId)))->where('status', 'sudah')->count(),
            'belum'     => Angsuran::when($anggotaId, fn($q) => $q->whereHas('pinjaman', fn($pq) => $pq->where('anggota_id', $anggotaId)))->where('status', 'belum')->count(),
            'terlambat' => Angsuran::when($anggotaId, fn($q) => $q->whereHas('pinjaman', fn($pq) => $pq->where('anggota_id', $anggotaId)))->where('status', 'belum')->where('tanggal_jatuh_tempo', '<', today())->count(),
        ];

        return view('koperasi.angsuran.index', compact('angsuran', 'cari', 'status', 'statsAngsuran', 'sortBy', 'sortDir'));
    }

    public function show(Angsuran $angsuran)
    {
        $angsuran->load('pinjaman.anggota');
        return view('koperasi.angsuran.detail', compact('angsuran'));
    }

    public function bayar(Request $request, Angsuran $angsuran)
    {
        if ($angsuran->status === 'sudah') {
            return back()->with('galat', 'Angsuran ini sudah dibayar.');
        }

        $request->validate([
            'tanggal_bayar' => ['nullable', 'date'],
        ]);

        $tanggalBayar = $request->input('tanggal_bayar', today()->toDateString());
        $denda        = $angsuran->hitungDenda($tanggalBayar);
        $hariTerlambat = 0;

        $jatuhTempo = $angsuran->tanggal_jatuh_tempo;
        if (\Illuminate\Support\Carbon::parse($tanggalBayar)->gt($jatuhTempo)) {
            $hariTerlambat = $jatuhTempo->diffInDays(\Illuminate\Support\Carbon::parse($tanggalBayar));
        }

        $angsuran->load('pinjaman.anggota');
        $angsuran->update([
            'status'         => 'sudah',
            'tanggal_bayar'  => $tanggalBayar,
            'denda'          => $denda,
            'hari_terlambat' => $hariTerlambat,
        ]);

        $pinjaman = $angsuran->pinjaman;
        $keterangan = 'Angsuran ke-' . $angsuran->ke . ' pinjaman ' . $pinjaman->nomor_pinjaman . ' dibayar';
        if ($denda > 0) {
            $keterangan .= ' (terlambat ' . $hariTerlambat . ' hari, denda Rp ' . number_format($denda, 0, ',', '.') . ')';
        }
        $this->catatLog('bayar', 'Angsuran', $angsuran->id,
            $pinjaman->anggota->nama ?? '-', $keterangan);

        // Cek apakah semua angsuran sudah lunas
        if ($pinjaman->angsuran()->where('status', 'belum')->count() === 0) {
            $pinjaman->update(['status' => 'lunas']);
        }

        $pesan = 'Pembayaran angsuran berhasil dicatat.';
        if ($denda > 0) {
            $pesan .= ' Denda keterlambatan: Rp ' . number_format($denda, 0, ',', '.') . ' (' . $hariTerlambat . ' hari terlambat).';
        }

        return back()->with('sukses', $pesan);
    }

    public function importForm()
    {
        $this->authorizeImport();

        return view('koperasi.angsuran.import');
    }

    public function import(Request $request)
    {
        $this->authorizeImport();

        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:5120',
        ], [
            'file.required' => 'File CSV wajib diunggah.',
            'file.mimes' => 'File harus berformat CSV.',
            'file.max' => 'Ukuran file maksimal 5 MB.',
        ]);

        $rows = CsvImportReader::read($request->file('file')->getRealPath(), [
            'no_pinjaman' => 'nomor_pinjaman',
            'angsuran_ke' => 'ke',
        ]);
        if ($rows === []) {
            return redirect()->back()->with('error', 'File CSV kosong.');
        }

        $berhasil = [];
        $dilewati = [];
        $error = [];

        foreach ($rows as ['baris' => $baris, 'data' => $data]) {
            $data += array_fill_keys(['nomor_pinjaman', 'ke', 'tanggal_bayar', 'denda'], '');
            $nomor = $data['nomor_pinjaman'];
            $ke = $data['ke'];
            $data['denda'] = str_replace(',', '.', $data['denda']);

            try {
                $validator = Validator::make($data, [
                    'nomor_pinjaman' => 'required|string',
                    'ke' => 'required|integer|min:1',
                    'tanggal_bayar' => 'required|date_format:Y-m-d',
                    'denda' => 'nullable|numeric|min:0',
                ], [
                    'nomor_pinjaman.required' => 'Nomor pinjaman wajib diisi.',
                    'ke.required' => 'Angsuran ke- wajib diisi.',
                    'ke.integer' => 'Angsuran ke- harus berupa angka bulat.',
                    'ke.min' => 'Angsuran ke- minimal 1.',
                    'tanggal_bayar.required' => 'Tanggal bayar wajib diisi.',
                    'tanggal_bayar.date_format' => 'Format tanggal bayar harus YYYY-MM-DD.',
                    'denda.numeric' => 'Denda harus berupa angka.',
                    'denda.min' => 'Denda tidak boleh negatif.',
                ]);

                if ($validator->fails()) {
                    $error[] = ['baris' => $baris, 'nomor' => $nomor ?: '-', 'ke' => $ke, 'pesan' => implode('; ', $validator->errors()->all())];
                    continue;
                }

                $pinjaman = Pinjaman::with('anggota')->where('nomor_pinjaman', $nomor)->first();
                if (! $pinjaman) {
                    $dilewati[] = ['baris' => $baris, 'nomor' => $nomor, 'ke' => $ke, 'alasan' => 'Pinjaman tidak ditemukan.'];
                    continue;
                }

                $angsuran = $pinjaman->angsuran()->where('ke', (int) $ke)->first();
                if (! $angsuran) {
                    $error[] = ['baris' => $baris, 'nomor' => $nomor, 'ke' => $ke, 'pesan' => 'Angsuran ke-' . $ke . ' tidak ada pada jadwal pinjaman ini.'];
                    continue;
                }
                if ($angsuran->status === 'sudah') {
                    $dilewati[] = ['baris' => $baris, 'nomor' => $nomor, 'ke' => $ke, 'alasan' => 'Angsuran sudah dibayar.'];
                    continue;
                }

                $tanggalBayar = $data['tanggal_bayar'];
                $denda = $data['denda'] !== '' ? (float) $data['denda'] : $angsuran->hitungDenda($tanggalBayar);
                $bayar = \Illuminate\Support\Carbon::parse($tanggalBayar);
                $hariTerlambat = $bayar->gt($angsuran->tanggal_jatuh_tempo)
                    ? (int) $angsuran->tanggal_jatuh_tempo->diffInDays($bayar)
                    : 0;

                DB::transaction(function () use ($angsuran, $pinjaman, $tanggalBayar, $denda, $hariTerlambat) {
                    $angsuran->update([
                        'status' => 'sudah',
                        'tanggal_bayar' => $tanggalBayar,
                        'denda' => $denda,
                        'hari_terlambat' => $hariTerlambat,
                    ]);

                    if ($pinjaman->angsuran()->where('status', 'belum')->doesntExist()) {
                        $pinjaman->update(['status' => 'lunas']);
                    }
                });

                $berhasil[] = [
                    'baris' => $baris,
                    'nama' => $pinjaman->anggota->nama ?? '-',
                    'nomor' => $nomor,
                    'ke' => $angsuran->ke,
                    'jumlah' => (float) $angsuran->jumlah_angsuran + $denda,
                    'tanggal' => $tanggalBayar,
                ];

                $this->catatLog('bayar', 'Angsuran', $angsuran->id, $pinjaman->anggota->nama ?? '-',
                    'Angsuran ke-' . $angsuran->ke . ' pinjaman ' . $nomor . ' dibayar (import CSV)');
            } catch (\Exception $e) {
                $error[] = ['baris' => $baris, 'nomor' => $nomor ?: '-', 'ke' => $ke, 'pesan' => $e->getMessage()];
            }
        }

        if ($error === []) {
            $request->session()->forget('import_errors.angsuran');
        } else {
            $request->session()->put('import_errors.angsuran', $error);
        }

        return view('koperasi.angsuran.import-hasil', compact('berhasil', 'dilewati', 'error'));
    }

    private function authorizeImport(): void
    {
        $this->authorize('angsuran.bayar');
        abort_if(($this->roleSlug() === 'anggota'), 403);
    }

    private function roleSlug(): ?string
    {
        $user = Auth::user();

        return $user->roleModel?->slug ?? $user->role;
    }

    public function create() {}    public function store(Request $request) {}
    public function edit(Angsuran $angsuran) {}
    public function update(Request $request, Angsuran $angsuran) {}
    public function destroy(Angsuran $angsuran) {}
}
