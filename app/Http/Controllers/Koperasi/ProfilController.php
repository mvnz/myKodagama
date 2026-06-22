<?php

namespace App\Http\Controllers\Koperasi;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfilController extends Controller
{
    public function show()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->load('anggota');
        return view('koperasi.profil.show', ['user' => $user]);
    }

    public function update(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'name'    => 'required|string|max:100',
            'email'   => 'required|email|max:150|unique:users,email,' . $user->id,
            'no_wa'   => 'nullable|string|max:20|regex:/^[0-9+\-\s]+$/',
        ]);

        $user->update($validated);

        return back()->with('sukses', 'Profil berhasil diperbarui.');
    }

    public function gantiPassword(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate([
            'password_lama'     => 'required',
            'password_baru'     => ['required', 'confirmed', Password::min(8)],
        ], [
            'password_lama.required'  => 'Password lama wajib diisi.',
            'password_baru.required'  => 'Password baru wajib diisi.',
            'password_baru.confirmed' => 'Konfirmasi password tidak cocok.',
            'password_baru.min'       => 'Password minimal 8 karakter.',
        ]);

        if (!Hash::check($request->password_lama, $user->password)) {
            return back()->withErrors(['password_lama' => 'Password lama tidak sesuai.'])->withInput();
        }

        $user->update(['password' => Hash::make($request->password_baru)]);

        return back()->with('sukses_password', 'Password berhasil diubah.');
    }
}
