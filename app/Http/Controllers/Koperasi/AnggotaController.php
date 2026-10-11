<?php

namespace App\Http\Controllers\Koperasi;

use App\Http\Controllers\Controller;

use App\Models\Koperasi\Anggota;
use App\Models\Koperasi\Pengaturan;
use App\Models\Koperasi\UnitKerja;
use App\Http\Controllers\Concerns\CatatAktivitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AnggotaController extends Controller
{
    use CatatAktivitas;
    public function index(Request $request)
    {
        $this->authorize('anggota.lihat');
        $cari   = $request->input('cari');
        $status = $request->input('status');
        $allowedSort = ['created_at', 'nomor_anggota', 'nama', 'tanggal_bergabung', 'status'];
        $sortBy  = in_array($request->input('sort'), $allowedSort, true) ? $request->input('sort') : 'created_at';
        $sortDir = $request->input('dir') === 'asc' ? 'asc' : 'desc';

        $anggota = Anggota::query()
            ->with(['unitKerja', 'user'])
            ->when($cari, fn($q) => $q->where('nama', 'like', "%$cari%")
                ->orWhere('nomor_anggota', 'like', "%$cari%")
                ->orWhere('nik', 'like', "%$cari%"))
            ->when($status, fn($q) => $q->where('status', $status))
            ->orderBy($sortBy, $sortDir)
            ->paginate(15)
            ->withQueryString();

        $statsAnggota = [
            'aktif'       => Anggota::where('status', 'aktif')->count(),
            'tidak_aktif' => Anggota::where('status', 'tidak_aktif')->count(),
            'menunggu'    => Anggota::where('status', 'menunggu')->count(),
            'baru'        => Anggota::where('status', 'aktif')
                ->whereMonth('tanggal_bergabung', now()->month)
                ->whereYear('tanggal_bergabung', now()->year)->count(),
        ];

        return view('koperasi.anggota.index', compact('anggota', 'cari', 'status', 'statsAnggota', 'sortBy', 'sortDir'));
    }

    public function create()
    {
        $nomorBaru   = $this->generateNomor();
        $unitKerja   = UnitKerja::where('aktif', true)->orderBy('nama')->get();
        $jenisAnggota = collect(json_decode(Pengaturan::get('sp_simpanan_wajib_per_jenis', '[]'), true) ?: [])
            ->pluck('jenis')->filter()->values();
        return view('koperasi.anggota.tambah', compact('nomorBaru', 'unitKerja', 'jenisAnggota'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nomor_anggota'    => 'required|unique:anggota,nomor_anggota',
            'nama'             => 'required|string|max:255',
            'jenis_anggota'    => 'nullable|string|max:100',
            'nik'              => 'required|string|size:16|unique:anggota,nik',
            'tempat_lahir'     => 'required|string|max:100',
            'tanggal_lahir'    => 'required|date|before:today',
            'jenis_kelamin'    => 'required|in:Laki-laki,Perempuan',
            'alamat'           => 'required|string',
            'telepon'          => 'required|string|max:20',
            'unit_kerja_id'    => 'nullable|exists:unit_kerja,id',
            'email'            => 'nullable|email|unique:anggota,email',
            'status'           => 'required|in:aktif,tidak_aktif',
            'tanggal_bergabung'=> 'required|date',
            'foto'             => 'nullable|image|max:2048',
        ], $this->pesanValidasi());

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('anggota', 'public');
        }

        $anggota = Anggota::create($validated);

        $this->catatLog('tambah', 'Anggota', $anggota->id, $anggota->nama,
            'Anggota baru ditambahkan: ' . $anggota->nomor_anggota,
            null, $anggota->toArray());

        return redirect()->route('anggota.index')
            ->with('sukses', 'Anggota berhasil ditambahkan.');
    }

    public function show(Anggota $anggota)
    {
        $anggota->load(['simpanan', 'pinjaman.angsuran']);
        return view('koperasi.anggota.detail', compact('anggota'));
    }

    public function rekap(Anggota $anggota)
    {
        $anggota->load([
            'simpanan' => fn($q) => $q->orderBy('tanggal', 'desc'),
            'pinjaman' => fn($q) => $q->withCount('angsuran')->orderBy('tanggal_pengajuan', 'desc'),
            'pinjaman.angsuran' => fn($q) => $q->orderBy('ke'),
            'unitKerja',
        ]);

        // Ringkasan simpanan per jenis
        $simpananRekap = $anggota->simpanan
            ->groupBy('jenis_simpanan')
            ->map(fn($items) => [
                'jumlah' => $items->count(),
                'total'  => $items->sum('jumlah'),
            ]);

        // Pinjaman aktif (disetujui) dan angsuran mendatang
        $pinjamanAktif = $anggota->pinjaman->whereIn('status', ['disetujui', 'diajukan']);

        $angsuranMendatang = $anggota->pinjaman
            ->flatMap(fn($p) => $p->angsuran->where('status', 'belum'))
            ->sortBy('tanggal_jatuh_tempo')
            ->take(5);

        // Wajib per jenis
        $wajibPerJenis = collect(json_decode(\App\Models\Koperasi\Pengaturan::get('sp_simpanan_wajib_per_jenis', '[]'), true) ?: [])
            ->firstWhere('jenis', $anggota->jenis_anggota);

        return view('koperasi.anggota.rekap', compact(
            'anggota', 'simpananRekap', 'pinjamanAktif', 'angsuranMendatang', 'wajibPerJenis'
        ));
    }

    public function kartu(Anggota $anggota)
    {
        $anggota->load(['simpanan', 'pinjaman.angsuran', 'unitKerja']);

        $totalPokok   = $anggota->simpanan->where('jenis_simpanan', 'pokok')->sum('jumlah');
        $totalWajib   = $anggota->simpanan->where('jenis_simpanan', 'wajib')->sum('jumlah');
        $totalSukarel = $anggota->simpanan->where('jenis_simpanan', 'sukarela')->sum('jumlah');
        $pinjamanAktif = $anggota->pinjaman->whereIn('status', ['disetujui', 'diajukan']);

        return view('koperasi.anggota.kartu', compact(
            'anggota', 'totalPokok', 'totalWajib', 'totalSukarel', 'pinjamanAktif'
        ));
    }

    public function edit(Anggota $anggota)
    {
        $unitKerja    = UnitKerja::where('aktif', true)->orderBy('nama')->get();
        $jenisAnggota = collect(json_decode(Pengaturan::get('sp_simpanan_wajib_per_jenis', '[]'), true) ?: [])
            ->pluck('jenis')->filter()->values();
        $roles = \App\Models\Role::orderBy('nama')->get();
        return view('koperasi.anggota.ubah', compact('anggota', 'unitKerja', 'jenisAnggota', 'roles'));
    }

    public function update(Request $request, Anggota $anggota)
    {
        $userTerkait = $anggota->user;
        $buatAkunBaru = ! $userTerkait && $request->filled('login_email');

        $validated = $request->validate([
            'nomor_anggota'    => 'required|unique:anggota,nomor_anggota,' . $anggota->id,
            'nama'             => 'required|string|max:255',
            'jenis_anggota'    => 'nullable|string|max:100',
            'nik'              => 'required|string|size:16|unique:anggota,nik,' . $anggota->id,
            'tempat_lahir'     => 'required|string|max:100',
            'tanggal_lahir'    => 'required|date|before:today',
            'jenis_kelamin'    => 'required|in:Laki-laki,Perempuan',
            'alamat'           => 'required|string',
            'telepon'          => 'required|string|max:20',
            'unit_kerja_id'    => 'nullable|exists:unit_kerja,id',
            'email'            => 'nullable|email|unique:anggota,email,' . $anggota->id,
            'status'           => 'required|in:aktif,tidak_aktif',
            'tanggal_bergabung'=> 'required|date',
            'foto'             => 'nullable|image|max:2048',
            'login_email'      => [
                $userTerkait ? 'required' : 'nullable',
                'email',
                'unique:users,email,' . ($userTerkait?->id ?? 'NULL'),
            ],
            'login_role_id'    => [
                ($userTerkait || $buatAkunBaru) ? 'required' : 'nullable',
                'exists:roles,id',
            ],
            'password_baru'    => [
                $buatAkunBaru ? 'required' : 'nullable',
                'string', 'min:8', 'confirmed',
            ],
        ], array_merge($this->pesanValidasi(), [
            'login_email.required' => 'Email login wajib diisi.',
            'login_email.unique'   => 'Email login sudah dipakai akun lain.',
            'password_baru.required' => 'Password wajib diisi untuk membuat akun baru.',
            'password_baru.min'    => 'Password minimal 8 karakter.',
            'password_baru.confirmed' => 'Konfirmasi password tidak cocok.',
        ]));

        if ($request->hasFile('foto')) {
            if ($anggota->foto) {
                Storage::disk('public')->delete($anggota->foto);
            }
            $validated['foto'] = $request->file('foto')->store('anggota', 'public');
        }

        $dataLama = $anggota->toArray();
        $anggota->update(collect($validated)->except(['password_baru', 'login_email'])->toArray());

        $pesanTambahan = '';

        // Update email, role dan/atau password user yang sudah ada
        if ($userTerkait) {
            $roleObj    = \App\Models\Role::find($request->login_role_id);
            $updateUser = [
                'email'   => $request->login_email,
                'role_id' => $roleObj?->id,
                'role'    => $roleObj?->slug ?? $userTerkait->role,
            ];
            if ($request->filled('password_baru')) {
                $updateUser['password'] = \Illuminate\Support\Facades\Hash::make($request->password_baru);
                $pesanTambahan = ' Password login telah direset.';
            }
            $userTerkait->update($updateUser);
            $this->catatLog('ubah', 'User', $userTerkait->id, $anggota->nama, 'Akun login diperbarui oleh admin');
        }

        // Buat akun baru jika belum ada dan email diisi
        if ($buatAkunBaru) {
            $roleObj = \App\Models\Role::find($request->login_role_id)
                ?? \App\Models\Role::where('slug', 'anggota')->first();
            $username = $this->uniqueUsernameFromEmail((string) $request->login_email);
            $user = \App\Models\User::create([
                'name'       => $anggota->nama,
                'username'   => $username,
                'email'      => $request->login_email,
                'password'   => \Illuminate\Support\Facades\Hash::make($request->password_baru),
                'role'       => $roleObj?->slug ?? 'anggota',
                'role_id'    => $roleObj?->id,
                'anggota_id' => $anggota->id,
            ]);
            $pesanTambahan = ' Akun login berhasil dibuat.';
            $this->catatLog('tambah', 'User', $user->id, $anggota->nama, 'Akun login dibuat oleh admin');
        }

        $this->catatLog('ubah', 'Anggota', $anggota->id, $anggota->nama,
            'Data anggota diperbarui: ' . $anggota->nomor_anggota,
            $dataLama, $anggota->fresh()->toArray());

        return redirect()->route('anggota.show', $anggota)
            ->with('sukses', 'Data anggota berhasil diperbarui.' . $pesanTambahan);
    }

    public function destroy(Anggota $anggota)
    {
        $dataLama = $anggota->toArray();
        $label    = $anggota->nama;
        $nomor    = $anggota->nomor_anggota;

        if ($anggota->foto) {
            Storage::disk('public')->delete($anggota->foto);
        }
        $anggota->delete();

        $this->catatLog('hapus', 'Anggota', null, $label,
            'Anggota dihapus: ' . $nomor, $dataLama);

        return redirect()->route('anggota.index')
            ->with('sukses', 'Anggota berhasil dihapus.');
    }

    public function setujui(Anggota $anggota)
    {
        $anggota->update(['status' => 'aktif']);

        $this->catatLog('ubah', 'Anggota', $anggota->id, $anggota->nama,
            'Pendaftaran anggota disetujui: ' . $anggota->nomor_anggota);

        return redirect()->back()
            ->with('sukses', 'Pendaftaran ' . $anggota->nama . ' telah disetujui.');
    }

    public function tolak(Anggota $anggota)
    {
        $dataLama = $anggota->toArray();
        $label    = $anggota->nama;
        $nomor    = $anggota->nomor_anggota;

        $anggota->delete();

        $this->catatLog('hapus', 'Anggota', null, $label,
            'Pendaftaran anggota ditolak & dihapus: ' . $nomor, $dataLama);

        return redirect()->back()
            ->with('sukses', 'Pendaftaran ' . $label . ' telah ditolak.');
    }

    public function importForm()
    {
        return view('koperasi.anggota.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ], [
            'file.required' => 'File CSV wajib diunggah.',
            'file.mimes' => 'File harus berformat CSV.',
            'file.max' => 'Ukuran file maksimal 2 MB.',
        ]);

        $file = $request->file('file');
        $content = file_get_contents($file->getRealPath());

        $delimiters = [';', ','];
        $detectedDelimiter = null;
        foreach ($delimiters as $delim) {
            if (strpos($content, $delim) !== false) {
                $detectedDelimiter = $delim;
                break;
            }
        }
        $delimiter = $detectedDelimiter ?? ',';

        $lines = array_filter(preg_split('/\r?\n/', trim($content)));
        if (empty($lines)) {
            return redirect()->back()->with('error', 'File CSV kosong.');
        }

        $headers = str_getcsv(array_shift($lines), $delimiter);
        $aliasKolom = [
            'no_anggota' => 'nomor_anggota',
            'nomor' => 'nomor_anggota',
            'no_induk' => 'nomor_anggota',
        ];
        $headers = array_map(function ($h) use ($aliasKolom) {
            $h = strtolower(trim(preg_replace('/^\xEF\xBB\xBF/', '', $h)));
            $h = preg_replace('/[\s\-]+/', '_', $h);

            return $aliasKolom[$h] ?? $h;
        }, $headers);

        $berhasil = [];
        $dilewati = [];
        $error = [];
        $baris = 2;

        foreach ($lines as $line) {
            if (empty(trim($line))) {
                continue;
            }

            $row = str_getcsv($line, $delimiter);
            $row = array_map('trim', $row);

            if (count($row) !== count($headers)) {
                $row = array_pad($row, count($headers), '');
            }

            $data = array_combine($headers, array_slice($row, 0, count($headers)));

            // Toleran terhadap huruf besar/kecil, mis. "Aktif" atau "laki-laki"
            if (isset($data['status'])) {
                $data['status'] = strtolower(preg_replace('/[\s\-]+/', '_', $data['status']));
            }
            if (isset($data['jenis_kelamin'])) {
                $data['jenis_kelamin'] = match (strtolower($data['jenis_kelamin'])) {
                    'laki-laki', 'laki laki', 'l' => 'Laki-laki',
                    'perempuan', 'p' => 'Perempuan',
                    default => $data['jenis_kelamin'],
                };
            }
            $nomorAnggota = $data['nomor_anggota'] ?? null;
            $nama = $data['nama'] ?? null;
            $nik = $data['nik'] ?? null;
            $jenisKelamin = $data['jenis_kelamin'] ?? null;
            $tempatLahir = $data['tempat_lahir'] ?? null;
            $tanggalLahir = $data['tanggal_lahir'] ?? null;
            $alamat = $data['alamat'] ?? null;
            $telepon = $data['telepon'] ?? null;
            $tanggalBergabung = $data['tanggal_bergabung'] ?? null;
            $jenisAnggota = $data['jenis_anggota'] ?? null;
            $email = $data['email'] ?? null;
            $status = $data['status'] ?? 'aktif';

            try {
                $validator = \Illuminate\Support\Facades\Validator::make($data, [
                    'nomor_anggota' => 'required|string|max:50',
                    'nama' => 'required|string|max:255',
                    'nik' => 'required|string|size:16',
                    'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
                    'tempat_lahir' => 'required|string|max:100',
                    'tanggal_lahir' => 'required|date_format:Y-m-d|before:today',
                    'alamat' => 'required|string',
                    'telepon' => 'required|string|max:20',
                    'tanggal_bergabung' => 'required|date_format:Y-m-d',
                    'email' => 'nullable|email',
                    'status' => 'nullable|in:aktif,tidak_aktif',
                ], [
                    'nomor_anggota.required' => 'Nomor anggota wajib diisi di file CSV (kolom nomor_anggota atau no_anggota).',
                    'nama.required' => 'Nama wajib diisi.',
                    'nik.required' => 'NIK wajib diisi.',
                    'nik.size' => 'NIK harus 16 digit.',
                    'jenis_kelamin.required' => 'Jenis kelamin wajib diisi.',
                    'jenis_kelamin.in' => 'Jenis kelamin harus "Laki-laki" atau "Perempuan".',
                    'tempat_lahir.required' => 'Tempat lahir wajib diisi.',
                    'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
                    'tanggal_lahir.date_format' => 'Format tanggal lahir harus YYYY-MM-DD.',
                    'alamat.required' => 'Alamat wajib diisi.',
                    'telepon.required' => 'Telepon wajib diisi.',
                    'tanggal_bergabung.required' => 'Tanggal bergabung wajib diisi.',
                    'tanggal_bergabung.date_format' => 'Format tanggal bergabung harus YYYY-MM-DD.',
                    'email.email' => 'Email tidak valid.',
                    'status.in' => 'Status tidak valid. Isi dengan "aktif" atau "tidak_aktif", atau kosongkan kolom agar otomatis "aktif".',
                ]);

                if ($validator->fails()) {
                    $error[] = [
                        'baris' => $baris,
                        'nomor' => $nomorAnggota ?? '-',
                        'nama' => $nama ?? ($nomorAnggota ?? $nik ?? '-'),
                        'pesan' => implode('; ', $validator->errors()->all()),
                    ];
                    $baris++;
                    continue;
                }

                if (Anggota::where('nik', $nik)->exists()) {
                    $dilewati[] = [
                        'baris' => $baris,
                        'nama' => $nama,
                        'alasan' => 'NIK sudah terdaftar dalam sistem.',
                    ];
                    $baris++;
                    continue;
                }

                if (Anggota::where('nomor_anggota', $nomorAnggota)->exists()) {
                    $error[] = [
                        'baris' => $baris,
                        'nomor' => $nomorAnggota,
                        'nama' => $nama,
                        'pesan' => 'Nomor anggota sudah terdaftar.',
                    ];
                    $baris++;
                    continue;
                }

                if ($email && Anggota::where('email', $email)->exists()) {
                    $error[] = [
                        'baris' => $baris,
                        'nomor' => $nomorAnggota,
                        'nama' => $nama,
                        'pesan' => 'Email sudah terdaftar.',
                    ];
                    $baris++;
                    continue;
                }

                $anggota = Anggota::create([
                    'nomor_anggota' => $nomorAnggota,
                    'nama' => $nama,
                    'nik' => $nik,
                    'jenis_kelamin' => $jenisKelamin,
                    'tempat_lahir' => $tempatLahir,
                    'tanggal_lahir' => $tanggalLahir,
                    'alamat' => $alamat,
                    'telepon' => $telepon,
                    'tanggal_bergabung' => $tanggalBergabung,
                    'jenis_anggota' => $jenisAnggota ?: null,
                    'email' => $email ?: null,
                    'status' => $status ?: 'aktif',
                ]);

                $berhasil[] = [
                    'baris' => $baris,
                    'nama' => $nama,
                    'nomor' => $nomorAnggota,
                ];

                $this->catatLog(
                    'tambah',
                    'Anggota',
                    $anggota->id,
                    $anggota->nama,
                    'Anggota diimport dari CSV: ' . $nomorAnggota,
                    null,
                    $anggota->toArray()
                );
            } catch (\Exception $e) {
                $error[] = [
                    'baris' => $baris,
                    'nomor' => $nomorAnggota ?? '-',
                    'nama' => $nama ?? ($nomorAnggota ?? $nik ?? '-'),
                    'pesan' => $e->getMessage(),
                ];
            }

            $baris++;
        }

        if ($error === []) {
            $request->session()->forget('import_errors.anggota');
        } else {
            $request->session()->put('import_errors.anggota', $error);
        }

        return view('koperasi.anggota.import-hasil', compact('berhasil', 'dilewati', 'error'));
    }

    private function generateNomor(): string
    {
        return Anggota::generateNomor();
    }

    private function pesanValidasi(): array
    {
        return [
            'nama.required'          => 'Nama wajib diisi.',
            'nik.required'           => 'NIK wajib diisi.',
            'nik.size'               => 'NIK harus 16 digit.',
            'nik.unique'             => 'NIK sudah terdaftar.',
            'tempat_lahir.required'  => 'Tempat lahir wajib diisi.',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'alamat.required'        => 'Alamat wajib diisi.',
            'telepon.required'       => 'Telepon wajib diisi.',
            'unit_kerja_id.exists'   => 'Unit kerja tidak valid.',
            'email.unique'           => 'Email sudah terdaftar.',
            'foto.image'             => 'File harus berupa gambar.',
            'foto.max'               => 'Ukuran foto maksimal 2MB.',
        ];
    }

    private function uniqueUsernameFromEmail(string $email): string
    {
        $base = Str::before(Str::lower($email), '@');
        $base = preg_replace('/[^a-z0-9_-]+/', '_', $base) ?? '';
        $base = trim($base, '_-');

        if ($base === '') {
            $base = 'user';
        }

        $base = Str::limit($base, 50, '');
        $counter = 0;
        $candidate = $base;

        while (\App\Models\User::where('username', $candidate)->exists()) {
            $counter++;
            $suffix = '_' . $counter;
            $candidate = Str::limit($base, 50 - strlen($suffix), '') . $suffix;
        }

        return $candidate;
    }
}
