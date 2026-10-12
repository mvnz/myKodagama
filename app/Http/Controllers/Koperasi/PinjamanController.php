<?php

namespace App\Http\Controllers\Koperasi;

use App\Http\Controllers\Controller;
use App\Support\CsvImportReader;

use App\Models\Koperasi\Pinjaman;
use App\Models\Koperasi\Anggota;
use App\Models\Koperasi\Angsuran;
use App\Models\Koperasi\Pengaturan;
use App\Http\Controllers\Concerns\CatatAktivitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class PinjamanController extends Controller
{
    use CatatAktivitas;

    public function index(Request $request)
    {
        $cari   = $request->input('cari');
        $status = $request->input('status');
        $allowedSort = ['created_at', 'nomor_pinjaman', 'jumlah_pinjaman', 'jangka_waktu', 'status', 'tanggal_pengajuan'];
        $sortBy  = in_array($request->input('sort'), $allowedSort, true) ? $request->input('sort') : 'created_at';
        $sortDir = $request->input('dir') === 'asc' ? 'asc' : 'desc';
        $user   = Auth::user();
        $anggotaId = $user->roleModel?->slug === 'anggota' ? $user->anggota_id : null;

        $pinjaman = Pinjaman::with('anggota')
            ->when($anggotaId, fn($q) => $q->where('anggota_id', $anggotaId))
            ->when($cari, function ($q) use ($cari) {
                $q->where('nomor_pinjaman', 'like', "%$cari%")
                    ->orWhereHas('anggota', fn($aq) => $aq->where('nama', 'like', "%$cari%"));
            })
            ->when($status, fn($q) => $q->where('status', $status))
            ->orderBy($sortBy, $sortDir)
            ->paginate(15)
            ->withQueryString();

        $statsPinjaman = [
            'diajukan'   => Pinjaman::when($anggotaId, fn($q) => $q->where('anggota_id', $anggotaId))->whereIn('status', ['diajukan', 'diverifikasi'])->count(),
            'disetujui'  => Pinjaman::when($anggotaId, fn($q) => $q->where('anggota_id', $anggotaId))->where('status', 'disetujui')->count(),
            'lunas'      => Pinjaman::when($anggotaId, fn($q) => $q->where('anggota_id', $anggotaId))->where('status', 'lunas')->count(),
            'nilai_aktif'=> Pinjaman::when($anggotaId, fn($q) => $q->where('anggota_id', $anggotaId))->where('status', 'disetujui')->sum('jumlah_pinjaman'),
        ];

        return view('koperasi.pinjaman.index', compact('pinjaman', 'cari', 'status', 'statsPinjaman', 'sortBy', 'sortDir'));
    }

    public function create()
    {
        $user = Auth::user();
        $anggotaSaya = null;
        $anggotaJenisAktif = '';

        if ($this->isAnggotaUser($user)) {
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
            $anggotaJenisAktif = (string) ($dataAnggota->jenis_anggota ?? '');
            $anggota = collect();
        } else {
            $anggota = Anggota::where('status', 'aktif')
                ->whereDoesntHave('pinjaman', fn($q) => $q->where('status', 'disetujui'))
                ->orderBy('nama')
                ->get();

            $anggotaJenisAktif = (string) old('anggota_jenis_aktif', request('anggota_jenis_aktif', ''));
        }

        $jenisPinjamanMaster = collect(json_decode(Pengaturan::get('sp_jenis_pinjaman', '[]'), true) ?: [])
            ->map(function ($jp) {
                return [
                    'jenis_anggota' => (string) ($jp['jenis_anggota'] ?? ''),
                    'nama' => (string) ($jp['nama'] ?? ''),
                    'bunga' => (float) ($jp['bunga'] ?? 0),
                    'min_pinjaman' => (int) ($jp['min_pinjaman'] ?? 0),
                    'maks_pinjaman' => (int) ($jp['maks_pinjaman'] ?? 0),
                    'min_jangka' => (int) ($jp['min_jangka'] ?? 1),
                    'maks_jangka' => (int) ($jp['maks_jangka'] ?? 60),
                ];
            })
            ->filter(fn($jp) => $jp['nama'] !== '')
            ->values();

        $jenisPinjaman = $jenisPinjamanMaster
            ->filter(fn($jp) => $jp['jenis_anggota'] === '' || $jp['jenis_anggota'] === $anggotaJenisAktif)
            ->values();

        return view('koperasi.pinjaman.tambah', compact('anggota', 'jenisPinjaman', 'anggotaSaya', 'jenisPinjamanMaster', 'anggotaJenisAktif'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'anggota_id'     => 'required|exists:anggota,id',
            'jenis_pinjaman' => 'nullable|string|max:220',
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

        $anggota = Anggota::findOrFail((int) $validated['anggota_id']);
        $aturanJenis = collect(json_decode(Pengaturan::get('sp_jenis_pinjaman', '[]'), true) ?: []);

        if (!empty($validated['jenis_pinjaman'])) {
            [$namaJenisDipilih, $jenisAnggotaDipilih] = array_pad(explode('||', (string) $validated['jenis_pinjaman'], 2), 2, '');

            $jenisDipilih = $aturanJenis->first(function ($jp) use ($namaJenisDipilih, $jenisAnggotaDipilih) {
                $nama = (string) ($jp['nama'] ?? '');
                $jenisAnggotaAturan = (string) ($jp['jenis_anggota'] ?? '');

                return $nama === $namaJenisDipilih && $jenisAnggotaAturan === $jenisAnggotaDipilih;
            });

            if (!$jenisDipilih) {
                $jenisDipilih = $aturanJenis->first(function ($jp) use ($namaJenisDipilih, $anggota) {
                    $nama = (string) ($jp['nama'] ?? '');
                    $jenisAnggotaAturan = (string) ($jp['jenis_anggota'] ?? '');

                    return $nama === $namaJenisDipilih
                        && ($jenisAnggotaAturan === '' || $jenisAnggotaAturan === (string) $anggota->jenis_anggota);
                });
            }

            if (!$jenisDipilih) {
                throw ValidationException::withMessages([
                    'jenis_pinjaman' => 'Jenis pinjaman tidak tersedia untuk jenis anggota ini.',
                ]);
            }

            $validated['jenis_pinjaman'] = $namaJenisDipilih;

            $minPinjaman = (int) ($jenisDipilih['min_pinjaman'] ?? 0);
            $maksPinjaman = (int) ($jenisDipilih['maks_pinjaman'] ?? 0);
            $minJangka = (int) ($jenisDipilih['min_jangka'] ?? 1);
            $maksJangka = (int) ($jenisDipilih['maks_jangka'] ?? 60);
            $bungaAturan = (float) ($jenisDipilih['bunga'] ?? 0);

            if ($validated['jumlah_pinjaman'] < $minPinjaman) {
                throw ValidationException::withMessages([
                    'jumlah_pinjaman' => 'Jumlah pinjaman di bawah minimum jenis pinjaman untuk anggota ini.',
                ]);
            }

            if ($maksPinjaman > 0 && $validated['jumlah_pinjaman'] > $maksPinjaman) {
                throw ValidationException::withMessages([
                    'jumlah_pinjaman' => 'Jumlah pinjaman melebihi maksimum jenis pinjaman untuk anggota ini.',
                ]);
            }

            if ($validated['jangka_waktu'] < $minJangka || $validated['jangka_waktu'] > $maksJangka) {
                throw ValidationException::withMessages([
                    'jangka_waktu' => 'Jangka waktu tidak sesuai batas jenis pinjaman untuk anggota ini.',
                ]);
            }

            if (abs(((float) $validated['bunga']) - $bungaAturan) > 0.0001) {
                throw ValidationException::withMessages([
                    'bunga' => 'Bunga tidak sesuai ketentuan jenis pinjaman untuk anggota ini.',
                ]);
            }
        }

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

    private function isAnggotaUser($user): bool
    {
        return ($user->roleModel?->slug ?? $user->role) === 'anggota';
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

    /** Aksi cepat: verifikasi pinjaman (diajukan G�� diverifikasi) */
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

    /** Aksi cepat: setujui pinjaman (diverifikasi G�� disetujui) */
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

    public function importForm()
    {
        $this->authorizeImport();

        return view('koperasi.pinjaman.import');
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
            'no_anggota' => 'nomor_anggota',
            'no_pinjaman' => 'nomor_pinjaman',
        ]);
        if ($rows === []) {
            return redirect()->back()->with('error', 'File CSV kosong.');
        }

        $berhasil = [];
        $dilewati = [];
        $error = [];
        $statusAktif = ['diajukan', 'diverifikasi', 'disetujui'];

        foreach ($rows as ['baris' => $baris, 'data' => $data]) {
            $data += array_fill_keys(['nomor_pinjaman', 'jenis_pinjaman', 'tanggal_persetujuan', 'angsuran_terbayar'], '');
            $nomorAnggota = $data['nomor_anggota'] ?? '';
            $data['status'] = strtolower(trim($data['status'] ?? '')) ?: 'disetujui';
            $data['bunga'] = str_replace(',', '.', $data['bunga'] ?? '');

            try {
                $validator = Validator::make($data, [
                    'nomor_anggota' => 'required|string',
                    'nomor_pinjaman' => 'nullable|string|max:50|unique:pinjaman,nomor_pinjaman',
                    'jenis_pinjaman' => 'nullable|string|max:220',
                    'jumlah_pinjaman' => 'required|numeric|min:100000',
                    'jangka_waktu' => 'required|integer|min:1|max:60',
                    'bunga' => 'required|numeric|min:0|max:30',
                    'tujuan' => 'required|string',
                    'tanggal_pengajuan' => 'required|date_format:Y-m-d',
                    'tanggal_persetujuan' => 'nullable|date_format:Y-m-d',
                    'status' => 'required|in:diajukan,diverifikasi,disetujui,lunas,ditolak',
                    'angsuran_terbayar' => 'nullable|integer|min:0',
                ], [
                    'nomor_anggota.required' => 'Nomor anggota wajib diisi.',
                    'nomor_pinjaman.unique' => 'Nomor pinjaman sudah terdaftar.',
                    'jumlah_pinjaman.required' => 'Jumlah pinjaman wajib diisi.',
                    'jumlah_pinjaman.numeric' => 'Jumlah pinjaman harus berupa angka.',
                    'jumlah_pinjaman.min' => 'Jumlah minimal Rp 100.000.',
                    'jangka_waktu.required' => 'Jangka waktu wajib diisi.',
                    'jangka_waktu.integer' => 'Jangka waktu harus berupa angka bulat (bulan).',
                    'jangka_waktu.min' => 'Jangka waktu minimal 1 bulan.',
                    'jangka_waktu.max' => 'Jangka waktu maksimal 60 bulan.',
                    'bunga.required' => 'Bunga wajib diisi.',
                    'bunga.numeric' => 'Bunga harus berupa angka.',
                    'bunga.max' => 'Bunga maksimal 30%.',
                    'tujuan.required' => 'Tujuan pinjaman wajib diisi.',
                    'tanggal_pengajuan.required' => 'Tanggal pengajuan wajib diisi.',
                    'tanggal_pengajuan.date_format' => 'Format tanggal pengajuan harus YYYY-MM-DD.',
                    'tanggal_persetujuan.date_format' => 'Format tanggal persetujuan harus YYYY-MM-DD.',
                    'status.in' => 'Status tidak valid. Isi dengan diajukan, diverifikasi, disetujui, lunas, atau ditolak.',
                    'angsuran_terbayar.integer' => 'Angsuran terbayar harus berupa angka bulat.',
                ]);

                if ($validator->fails()) {
                    $error[] = [
                        'baris' => $baris,
                        'nomor' => $nomorAnggota ?: '-',
                        'pesan' => implode('; ', $validator->errors()->all()),
                    ];
                    continue;
                }

                $anggota = Anggota::where('nomor_anggota', $nomorAnggota)->first();
                if (! $anggota) {
                    $dilewati[] = ['baris' => $baris, 'nomor' => $nomorAnggota, 'alasan' => 'Anggota tidak ditemukan.'];
                    continue;
                }

                $status = $data['status'];
                if (in_array($status, $statusAktif, true)
                    && $anggota->pinjaman()->whereIn('status', $statusAktif)->exists()) {
                    $dilewati[] = [
                        'baris' => $baris,
                        'nomor' => $nomorAnggota,
                        'alasan' => 'Anggota masih memiliki pinjaman yang berjalan atau sedang diajukan.',
                    ];
                    continue;
                }

                $jumlah = (float) $data['jumlah_pinjaman'];
                $jangka = (int) $data['jangka_waktu'];
                $bunga = (float) $data['bunga'];
                $punyaJadwal = in_array($status, ['disetujui', 'lunas'], true);
                $terbayar = $status === 'lunas' ? $jangka : min((int) ($data['angsuran_terbayar'] ?? 0), $jangka);
                if ($status === 'disetujui' && $terbayar >= $jangka) {
                    $status = 'lunas';
                }

                $pinjaman = DB::transaction(function () use ($data, $anggota, $jumlah, $jangka, $bunga, $status, $punyaJadwal, $terbayar) {
                    $pinjaman = Pinjaman::create([
                        'nomor_pinjaman' => $data['nomor_pinjaman'] ?: $this->nomorPinjamanUnik(),
                        'anggota_id' => $anggota->id,
                        'jenis_pinjaman' => $data['jenis_pinjaman'] ?: null,
                        'jumlah_pinjaman' => $jumlah,
                        'jangka_waktu' => $jangka,
                        'bunga' => $bunga,
                        'cicilan_per_bulan' => round($jumlah / $jangka + $jumlah * $bunga / 100, 2),
                        'tujuan' => $data['tujuan'],
                        'status' => $status,
                        'tanggal_pengajuan' => $data['tanggal_pengajuan'],
                        'tanggal_persetujuan' => $punyaJadwal
                            ? ($data['tanggal_persetujuan'] ?: $data['tanggal_pengajuan'])
                            : null,
                        'user_id' => Auth::id(),
                    ]);

                    if ($punyaJadwal) {
                        $this->buatJadwalAngsuran($pinjaman, $terbayar);
                    }

                    return $pinjaman;
                });

                $berhasil[] = [
                    'baris' => $baris,
                    'nama' => $anggota->nama,
                    'nomor' => $pinjaman->nomor_pinjaman,
                    'jumlah' => (int) $jumlah,
                    'status' => $status,
                ];

                $this->catatLog(
                    'tambah',
                    'Pinjaman',
                    $pinjaman->id,
                    $anggota->nama,
                    'Pinjaman ' . $pinjaman->nomor_pinjaman . ' Rp ' . number_format($jumlah, 0, ',', '.') . ' diimport dari CSV',
                    null,
                    $pinjaman->toArray()
                );
            } catch (\Exception $e) {
                $error[] = ['baris' => $baris, 'nomor' => $nomorAnggota ?: '-', 'pesan' => $e->getMessage()];
            }
        }

        if ($error === []) {
            $request->session()->forget('import_errors.pinjaman');
        } else {
            $request->session()->put('import_errors.pinjaman', $error);
        }

        return view('koperasi.pinjaman.import-hasil', compact('berhasil', 'dilewati', 'error'));
    }

    private function authorizeImport(): void
    {
        $this->authorize('pinjaman.tambah');
        abort_if($this->isAnggotaUser(Auth::user()), 403);
    }

    private function nomorPinjamanUnik(): string
    {
        $nomor = $this->generateNomor();
        $urutan = (int) substr($nomor, -4);

        while (Pinjaman::where('nomor_pinjaman', $nomor)->exists()) {
            $urutan++;
            $nomor = 'PIN-' . Carbon::now()->format('Ym') . '-' . str_pad($urutan, 4, '0', STR_PAD_LEFT);
        }

        return $nomor;
    }
    private function generateNomor(): string
    {
        $tahun  = Carbon::now()->format('Y');
        $bulan  = Carbon::now()->format('m');
        $urutan = Pinjaman::whereYear('tanggal_pengajuan', $tahun)->count() + 1;
        return 'PIN-' . $tahun . $bulan . '-' . str_pad($urutan, 4, '0', STR_PAD_LEFT);
    }

    private function buatJadwalAngsuran(Pinjaman $pinjaman, int $terbayar = 0): void
    {
        $pokok         = $pinjaman->jumlah_pinjaman / $pinjaman->jangka_waktu;
        $bunga         = ($pinjaman->jumlah_pinjaman * $pinjaman->bunga / 100);
        $cicilan       = round($pokok + $bunga, 2);
        $tanggalMulai  = $pinjaman->tanggal_persetujuan ?? today();

        for ($i = 1; $i <= $pinjaman->jangka_waktu; $i++) {
            $jatuhTempo = Carbon::parse($tanggalMulai)->addMonths($i);
            $sudah = $i <= $terbayar;

            Angsuran::create([
                'pinjaman_id'       => $pinjaman->id,
                'ke'                => $i,
                'jumlah_angsuran'   => $cicilan,
                'pokok'             => round($pokok, 2),
                'bunga'             => round($bunga, 2),
                'tanggal_jatuh_tempo' => $jatuhTempo,
                'tanggal_bayar'     => $sudah ? $jatuhTempo : null,
                'status'            => $sudah ? 'sudah' : 'belum',
            ]);
        }
    }
}
