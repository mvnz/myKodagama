<?php

namespace App\Http\Controllers;

use App\Models\UnitKerja;
use App\Http\Controllers\Concerns\CatatAktivitas;
use Illuminate\Http\Request;

class UnitKerjaController extends Controller
{
    use CatatAktivitas;

    public function index(Request $request)
    {
        $cari = $request->input('cari');

        $unitKerja = UnitKerja::query()
            ->when($cari, fn($q) => $q->where('nama', 'like', "%$cari%")
                ->orWhere('kode', 'like', "%$cari%")
                ->orWhere('singkatan', 'like', "%$cari%"))
            ->orderBy('nama')
            ->paginate(20)
            ->withQueryString();

        $statsUnitKerja = [
            'total'      => UnitKerja::count(),
            'aktif'      => UnitKerja::where('aktif', true)->count(),
            'tidak_aktif'=> UnitKerja::where('aktif', false)->count(),
            'terbaru'    => UnitKerja::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)->count(),
        ];

        return view('unit-kerja.index', compact('unitKerja', 'cari', 'statsUnitKerja'));
    }

    public function create()
    {
        return view('unit-kerja.tambah');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode'       => 'required|string|max:20|unique:unit_kerja,kode',
            'nama'       => 'required|string|max:255',
            'singkatan'  => 'nullable|string|max:20',
            'keterangan' => 'nullable|string',
            'aktif'      => 'nullable|boolean',
        ], [
            'kode.required' => 'Kode unit kerja wajib diisi.',
            'kode.unique'   => 'Kode unit kerja sudah digunakan.',
            'nama.required' => 'Nama unit kerja wajib diisi.',
        ]);

        $validated['aktif'] = $request->boolean('aktif', true);

        $unit = UnitKerja::create($validated);

        $this->catatLog('tambah', 'Unit Kerja', $unit->id, $unit->nama,
            'Unit kerja baru ditambahkan: ' . $unit->kode,
            null, $unit->toArray());

        return redirect()->route('unit-kerja.index')
            ->with('sukses', 'Unit kerja berhasil ditambahkan.');
    }

    public function edit(UnitKerja $unitKerja)
    {
        return view('unit-kerja.ubah', compact('unitKerja'));
    }

    public function update(Request $request, UnitKerja $unitKerja)
    {
        $validated = $request->validate([
            'kode'       => 'required|string|max:20|unique:unit_kerja,kode,' . $unitKerja->id,
            'nama'       => 'required|string|max:255',
            'singkatan'  => 'nullable|string|max:20',
            'keterangan' => 'nullable|string',
            'aktif'      => 'nullable|boolean',
        ], [
            'kode.required' => 'Kode unit kerja wajib diisi.',
            'kode.unique'   => 'Kode unit kerja sudah digunakan.',
            'nama.required' => 'Nama unit kerja wajib diisi.',
        ]);

        $validated['aktif'] = $request->boolean('aktif', true);

        $dataLama = $unitKerja->toArray();
        $unitKerja->update($validated);

        $this->catatLog('ubah', 'Unit Kerja', $unitKerja->id, $unitKerja->nama,
            'Data unit kerja diperbarui: ' . $unitKerja->kode,
            $dataLama, $unitKerja->fresh()->toArray());

        return redirect()->route('unit-kerja.index')
            ->with('sukses', 'Unit kerja berhasil diperbarui.');
    }

    public function destroy(UnitKerja $unitKerja)
    {
        $dataLama = $unitKerja->toArray();
        $label    = $unitKerja->nama;
        $kode     = $unitKerja->kode;

        $unitKerja->delete();

        $this->catatLog('hapus', 'Unit Kerja', null, $label,
            'Unit kerja dihapus: ' . $kode, $dataLama);

        return redirect()->route('unit-kerja.index')
            ->with('sukses', 'Unit kerja berhasil dihapus.');
    }
}
