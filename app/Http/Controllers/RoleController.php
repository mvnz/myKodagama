<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use App\Http\Controllers\Concerns\CatatAktivitas;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    use CatatAktivitas;
    public function index()
    {
        $this->authorize('role.kelola');
        $roles = Role::withCount('permissions')->latest()->get();

        $statsRole = [
            'total'       => $roles->count(),
            'total_perm'  => Permission::count(),
            'admin'       => $roles->where('slug', 'admin')->count(),
            'custom'      => $roles->where('slug', '!=', 'admin')->count(),
        ];

        return view('role.index', compact('roles', 'statsRole'));
    }

    public function create()
    {
        $permissions = Permission::orderBy('grup')->orderBy('nama')->get()->groupBy('grup');
        return view('role.tambah', compact('permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama'        => 'required|string|max:100',
            'deskripsi'   => 'nullable|string|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ], [
            'nama.required' => 'Nama role wajib diisi.',
            'nama.max'      => 'Nama role maksimal 100 karakter.',
        ]);

        $slug = Str::slug($request->nama);
        $slugOriginal = $slug;
        $counter = 1;
        while (Role::where('slug', $slug)->exists()) {
            $slug = $slugOriginal . '-' . $counter++;
        }

        $role = Role::create([
            'nama'      => $request->nama,
            'slug'      => $slug,
            'deskripsi' => $request->deskripsi,
        ]);

        if ($request->filled('permissions')) {
            $role->permissions()->sync($request->permissions);
        }

        $this->catatLog('tambah', 'Role', $role->id, $role->nama,
            'Role baru "' . $role->nama . '" ditambahkan');

        return redirect()->route('role.index')
            ->with('sukses', 'Role "' . $role->nama . '" berhasil ditambahkan.');
    }

    public function show(Role $role)
    {
        $role->load('permissions');
        $permissions = Permission::orderBy('grup')->orderBy('nama')->get()->groupBy('grup');
        return view('role.detail', compact('role', 'permissions'));
    }

    public function edit(Role $role)
    {
        $role->load('permissions');
        $permissions = Permission::orderBy('grup')->orderBy('nama')->get()->groupBy('grup');
        $selectedPermissions = $role->permissions->pluck('id')->toArray();
        return view('role.ubah', compact('role', 'permissions', 'selectedPermissions'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'nama'          => 'required|string|max:100',
            'deskripsi'     => 'nullable|string|max:255',
            'permissions'   => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ], [
            'nama.required' => 'Nama role wajib diisi.',
            'nama.max'      => 'Nama role maksimal 100 karakter.',
        ]);

        $role->update([
            'nama'      => $request->nama,
            'deskripsi' => $request->deskripsi,
        ]);

        $role->permissions()->sync($request->permissions ?? []);

        $this->catatLog('ubah', 'Role', $role->id, $role->nama,
            'Role "' . $role->nama . '" diperbarui');

        return redirect()->route('role.index')
            ->with('sukses', 'Role "' . $role->nama . '" berhasil diperbarui.');
    }

    public function destroy(Role $role)
    {
        $role->permissions()->detach();
        $nama = $role->nama;
        $id   = $role->id;
        $role->delete();

        $this->catatLog('hapus', 'Role', null, $nama,
            'Role "' . $nama . '" dihapus');

        return redirect()->route('role.index')
            ->with('sukses', 'Role "' . $nama . '" berhasil dihapus.');
    }
}
