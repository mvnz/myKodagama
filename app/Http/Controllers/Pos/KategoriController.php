<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\Pos\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class KategoriController extends Controller
{
    public function index(Request $request)
    {
        $allowedSort = ['nama', 'urutan', 'produk_count'];
        $sortBy  = in_array($request->input('sort'), $allowedSort) ? $request->input('sort') : 'urutan';
        $sortDir = $request->input('dir') === 'desc' ? 'desc' : 'asc';

        $kategoris = Kategori::withCount('produk')
            ->orderBy($sortBy === 'produk_count' ? 'produk_count' : $sortBy, $sortDir)
            ->get();

        return view('pos.kategori.index', compact('kategoris', 'sortBy', 'sortDir'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama'   => 'required|string|max:100',
            'urutan' => 'nullable|integer|min:0',
        ]);
        $data['slug']   = Str::slug($data['nama']);
        $data['urutan'] = $data['urutan'] ?? 0;

        Kategori::create($data);
        return back()->with('sukses', 'Kategori ditambahkan.');
    }

    public function update(Request $request, Kategori $kategori)
    {
        $data = $request->validate([
            'nama'   => 'required|string|max:100',
            'urutan' => 'nullable|integer|min:0',
        ]);
        $data['slug']   = Str::slug($data['nama']);
        $data['urutan'] = $data['urutan'] ?? $kategori->urutan;

        $kategori->update($data);
        return back()->with('sukses', 'Kategori diperbarui.');
    }

    public function destroy(Kategori $kategori)
    {
        if ($kategori->produk()->count()) {
            return back()->withErrors(['msg' => 'Kategori masih memiliki produk, tidak bisa dihapus.']);
        }
        $kategori->delete();
        return back()->with('sukses', 'Kategori dihapus.');
    }
}
