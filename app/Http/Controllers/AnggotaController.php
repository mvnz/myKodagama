<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\Pengaturan;
use App\Models\UnitKerja;
use App\Http\Controllers\Concerns\CatatAktivitas;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class AnggotaController extends Controller
{
    use CatatAktivitas;
    public function index(Request $request)
    {
        $this->authorize('anggota.lihat');
        $cari   = $request->input('cari');
        $status = $request->input('status');

        $anggota = Anggota::query()
            ->with(['unitKerja', 'user'])
            ->when($cari, fn($q) => $q->where('nama', 'like', "%$cari%")
                ->orWhere('nomor_anggota', 'like', "%$cari%")
                ->orWhere('nik', 'like', "%$cari%"))
            ->when($status, fn($q) => $q->where('status', $status))
            ->latest()
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

        return view('anggota.index', compact('anggota', 'cari', 'status', 'statsAnggota'));
    }

    public function create()
    {
        $nomorBaru   = $this->generateNomor();
        $unitKerja   = UnitKerja::where('aktif', true)->orderBy('nama')->get();
        $jenisAnggota = collect(json_decode(Pengaturan::get('sp_simpanan_wajib_per_jenis', '[]'), true) ?: [])
            ->pluck('jenis')->filter()->values();
        return view('anggota.tambah', compact('nomorBaru', 'unitKerja', 'jenisAnggota'));
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
        return view('anggota.detail', compact('anggota'));
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
        $wajibPerJenis = collect(json_decode(\App\Models\Pengaturan::get('sp_simpanan_wajib_per_jenis', '[]'), true) ?: [])
            ->firstWhere('jenis', $anggota->jenis_anggota);

        return view('anggota.rekap', compact(
            'anggota', 'simpananRekap', 'pinjamanAktif', 'angsuranMendatang', 'wajibPerJenis'
        ));
    }

    public function edit(Anggota $anggota)
    {
        $unitKerja    = UnitKerja::where('aktif', true)->orderBy('nama')->get();
        $jenisAnggota = collect(json_decode(Pengaturan::get('sp_simpanan_wajib_per_jenis', '[]'), true) ?: [])
            ->pluck('jenis')->filter()->values();
        $roles = \App\Models\Role::orderBy('nama')->get();
        return view('anggota.ubah', compact('anggota', 'unitKerja', 'jenisAnggota', 'roles'));
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
            $user = \App\Models\User::create([
                'name'       => $anggota->nama,
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
}
