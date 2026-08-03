<?php

namespace App\Http\Controllers;

use App\Models\Koperasi\Anggota;
use App\Models\Koperasi\Pengaturan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class AuthController extends Controller
{
    public function tampilMasuk()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        $pengaturan = Pengaturan::semuaNilai();
        return view('auth.masuk', compact('pengaturan'));
    }

    public function masuk(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ], [
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $kredensial = $request->only('email', 'password');
        $ingat = $request->boolean('ingat');

        if (Auth::attempt($kredensial, $ingat)) {
            $request->session()->regenerate();
            $user = Auth::user();

            // Blokir login jika anggota terhubung tapi statusnya tidak aktif
            if ($user->anggota_id) {
                $anggota = Anggota::find($user->anggota_id);
                if (!$anggota || $anggota->status !== 'aktif') {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    return back()->withErrors([
                        'email' => 'Akun Anda tidak dapat digunakan karena status keanggotaan tidak aktif.',
                    ])->onlyInput('email');
                }
            }

            $roleNama = $user->roleModel?->nama ?? 'Pengguna';
            return redirect()->intended(route('dashboard'))
                ->with('sukses', 'Selamat datang, ' . $user->name . '! (' . $roleNama . ')');
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    public function keluar(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('beranda')
            ->with('sukses', 'Anda berhasil keluar.');
    }

    public function tampilDaftar()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.daftar');
    }

    public function daftar(Request $request)
    {
        $request->validate([
            'nama'    => ['required', 'string', 'max:100'],
            'telepon' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s]+$/'],
            'email'   => ['required', 'email', 'max:150', 'unique:anggota,email'],
        ], [
            'nama.required'    => 'Nama lengkap wajib diisi.',
            'telepon.required' => 'Nomor WhatsApp wajib diisi.',
            'telepon.regex'    => 'Format nomor tidak valid (hanya angka, +, -).',
            'email.required'   => 'Email wajib diisi.',
            'email.email'      => 'Format email tidak valid.',
            'email.unique'     => 'Email sudah terdaftar.',
        ]);

        Anggota::create([
            'nomor_anggota'    => Anggota::generateNomor(),
            'nama'             => $request->nama,
            'telepon'          => $request->telepon,
            'email'            => $request->email,
            'status'           => 'menunggu',
            'tanggal_bergabung'=> Carbon::today(),
        ]);

        return redirect()->route('masuk')
            ->with('status', 'Pendaftaran berhasil! Silakan tunggu konfirmasi dari petugas koperasi.');
    }
}
