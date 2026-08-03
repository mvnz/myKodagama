<?php

namespace App\Http\Controllers\Koperasi;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\CatatAktivitas;
use App\Models\Koperasi\Anggota;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Str;

class UserController extends Controller
{
	use CatatAktivitas;

	public function index(Request $request)
	{
		$q = $request->input('q');
		$allowedSort = ['created_at', 'name', 'email'];
		$sortBy  = in_array($request->input('sort'), $allowedSort, true) ? $request->input('sort') : 'created_at';
		$sortDir = $request->input('dir') === 'asc' ? 'asc' : 'desc';
		$users = User::with(['roleModel', 'anggota'])
			->when($q, fn ($query) => $query
				->where('name', 'like', "%$q%")
				->orWhere('email', 'like', "%$q%")
				->orWhere('username', 'like', "%$q%"))
			->orderBy($sortBy, $sortDir)
			->paginate(15)
			->withQueryString();

		$statsUser = [
			'total' => User::count(),
			'admin' => User::where('role', 'admin')->count(),
			'petugas' => User::where('role', 'petugas')->count(),
			'anggota' => User::where('role', 'anggota')->count(),
		];

		return view('koperasi.user.index', compact('users', 'q', 'statsUser', 'sortBy', 'sortDir'));
	}

	public function create()
	{
		$roles = Role::orderBy('nama')->get();
		$anggota = Anggota::orderBy('nama')->whereDoesntHave('user')->get();

		return view('koperasi.user.tambah', compact('roles', 'anggota'));
	}

	public function store(Request $request)
	{
		$this->ensureUsernameFromEmail($request);

		$request->validate([
			'name' => ['required', 'string', 'max:100'],
			'username' => ['nullable', 'string', 'max:50', 'alpha_dash', 'unique:users,username'],
			'no_wa' => ['nullable', 'string', 'max:20'],
			'email' => ['required', 'email', 'max:150', 'unique:users,email'],
			'role_id' => ['required', 'exists:roles,id'],
			'anggota_id' => ['nullable', 'exists:anggota,id'],
			'password' => ['required', Password::min(8), 'confirmed'],
		], [
			'name.required' => 'Nama wajib diisi.',
			'username.unique' => 'Username sudah digunakan.',
			'email.required' => 'Email wajib diisi.',
			'email.unique' => 'Email sudah terdaftar.',
			'role_id.required' => 'Role wajib dipilih.',
			'password.required' => 'Password wajib diisi.',
			'password.confirmed' => 'Konfirmasi password tidak cocok.',
		]);

		$role = Role::findOrFail($request->role_id);

		$user = User::create([
			'name' => $request->name,
			'username' => $request->username,
			'no_wa' => $request->no_wa,
			'email' => $request->email,
			'role_id' => $role->id,
			'role' => $role->slug,
			'anggota_id' => $request->anggota_id ?: null,
			'password' => Hash::make($request->password),
		]);

		$this->catatLog('tambah', 'User', $user->id, $user->name, 'User baru "' . $user->name . '" ditambahkan');

		return redirect()->route('user.index')
			->with('sukses', 'User "' . $user->name . '" berhasil ditambahkan.');
	}

	public function edit(User $user)
	{
		$roles = Role::orderBy('nama')->get();
		$anggota = Anggota::orderBy('nama')
			->where(fn ($q) => $q->whereDoesntHave('user')->orWhere('id', $user->anggota_id))
			->get();

		return view('koperasi.user.edit', compact('user', 'roles', 'anggota'));
	}

	public function update(Request $request, User $user)
	{
		$this->ensureUsernameFromEmail($request, $user->id);

		$request->validate([
			'name' => ['required', 'string', 'max:100'],
			'username' => ['nullable', 'string', 'max:50', 'alpha_dash', 'unique:users,username,' . $user->id],
			'no_wa' => ['nullable', 'string', 'max:20'],
			'email' => ['required', 'email', 'max:150', 'unique:users,email,' . $user->id],
			'role_id' => ['required', 'exists:roles,id'],
			'anggota_id' => ['nullable', 'exists:anggota,id'],
			'password' => ['nullable', Password::min(8), 'confirmed'],
		], [
			'name.required' => 'Nama wajib diisi.',
			'username.unique' => 'Username sudah digunakan.',
			'email.required' => 'Email wajib diisi.',
			'email.unique' => 'Email sudah terdaftar.',
			'role_id.required' => 'Role wajib dipilih.',
		]);

		$role = Role::findOrFail($request->role_id);

		$data = [
			'name' => $request->name,
			'username' => $request->username,
			'no_wa' => $request->no_wa,
			'email' => $request->email,
			'role_id' => $role->id,
			'role' => $role->slug,
			'anggota_id' => $request->anggota_id ?: null,
		];

		if ($request->filled('password')) {
			$data['password'] = Hash::make($request->password);
		}

		$user->update($data);

		$this->catatLog('ubah', 'User', $user->id, $user->name, 'Data user "' . $user->name . '" diperbarui');

		return redirect()->route('user.index')
			->with('sukses', 'Data user "' . $user->name . '" berhasil diperbarui.');
	}

	public function destroy(User $user)
	{
		$nama = $user->name;
		$id = $user->id;
		$user->delete();

		$this->catatLog('hapus', 'User', $id, $nama, 'User "' . $nama . '" dihapus');

		return redirect()->route('user.index')
			->with('sukses', 'User "' . $nama . '" berhasil dihapus.');
	}

	private function ensureUsernameFromEmail(Request $request, ?int $ignoreUserId = null): void
	{
		$username = trim((string) $request->input('username', ''));
		$email = trim((string) $request->input('email', ''));

		if ($username !== '' || $email === '') {
			return;
		}

		$base = $this->buildUsernameBase($email);
		$request->merge([
			'username' => $this->buildUniqueUsername($base, $ignoreUserId),
		]);
	}

	private function buildUsernameBase(string $email): string
	{
		$localPart = Str::before(Str::lower($email), '@');
		$slug = preg_replace('/[^a-z0-9_-]+/', '_', $localPart) ?? '';
		$slug = trim($slug, '_-');

		if ($slug === '') {
			$slug = 'user';
		}

		return Str::limit($slug, 50, '');
	}

	private function buildUniqueUsername(string $base, ?int $ignoreUserId = null): string
	{
		$counter = 0;
		$candidate = $base;

		while ($this->usernameExists($candidate, $ignoreUserId)) {
			$counter++;
			$suffix = '_' . $counter;
			$candidate = Str::limit($base, 50 - strlen($suffix), '') . $suffix;
		}

		return $candidate;
	}

	private function usernameExists(string $username, ?int $ignoreUserId = null): bool
	{
		return User::query()
			->where('username', $username)
			->when($ignoreUserId, fn($q) => $q->where('id', '!=', $ignoreUserId))
			->exists();
	}
}