<?php

namespace App\Http\Controllers\Koperasi;

use App\Support\CsvImportReader;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\CatatAktivitas;
use App\Models\Koperasi\UnitKerja;
use Illuminate\Http\Request;

class UnitKerjaController extends Controller
{
	use CatatAktivitas;

	public function index(Request $request)
	{
		$cari = $request->input('cari');
		$allowedSort = ['created_at', 'kode', 'nama', 'singkatan', 'aktif'];
		$sortBy  = in_array($request->input('sort'), $allowedSort, true) ? $request->input('sort') : 'created_at';
		$sortDir = $request->input('dir') === 'asc' ? 'asc' : 'desc';

		$unitKerja = UnitKerja::query()
			->when($cari, fn ($q) => $q->where('nama', 'like', "%$cari%")
				->orWhere('kode', 'like', "%$cari%")
				->orWhere('singkatan', 'like', "%$cari%"))
			->orderBy($sortBy, $sortDir)
			->paginate(20)
			->withQueryString();

		$statsUnitKerja = [
			'total' => UnitKerja::count(),
			'aktif' => UnitKerja::where('aktif', true)->count(),
			'tidak_aktif' => UnitKerja::where('aktif', false)->count(),
			'terbaru' => UnitKerja::whereMonth('created_at', now()->month)
				->whereYear('created_at', now()->year)->count(),
		];

		return view('koperasi.unit-kerja.index', compact('unitKerja', 'cari', 'statsUnitKerja', 'sortBy', 'sortDir'));
	}

	public function create()
	{
		return view('koperasi.unit-kerja.tambah');
	}

	public function store(Request $request)
	{
		$validated = $request->validate([
			'kode' => 'required|string|max:20|unique:unit_kerja,kode',
			'nama' => 'required|string|max:255',
			'singkatan' => 'nullable|string|max:20',
			'keterangan' => 'nullable|string',
			'aktif' => 'nullable|boolean',
		], [
			'kode.required' => 'Kode unit kerja wajib diisi.',
			'kode.unique' => 'Kode unit kerja sudah digunakan.',
			'nama.required' => 'Nama unit kerja wajib diisi.',
		]);

		$validated['aktif'] = $request->boolean('aktif', true);

		$unit = UnitKerja::create($validated);

		$this->catatLog(
			'tambah',
			'Unit Kerja',
			$unit->id,
			$unit->nama,
			'Unit kerja baru ditambahkan: ' . $unit->kode,
			null,
			$unit->toArray()
		);

		return redirect()->route('unit-kerja.index')
			->with('sukses', 'Unit kerja berhasil ditambahkan.');
	}

	public function edit(UnitKerja $unitKerja)
	{
		return view('koperasi.unit-kerja.ubah', compact('unitKerja'));
	}

	public function update(Request $request, UnitKerja $unitKerja)
	{
		$validated = $request->validate([
			'kode' => 'required|string|max:20|unique:unit_kerja,kode,' . $unitKerja->id,
			'nama' => 'required|string|max:255',
			'singkatan' => 'nullable|string|max:20',
			'keterangan' => 'nullable|string',
			'aktif' => 'nullable|boolean',
		], [
			'kode.required' => 'Kode unit kerja wajib diisi.',
			'kode.unique' => 'Kode unit kerja sudah digunakan.',
			'nama.required' => 'Nama unit kerja wajib diisi.',
		]);

		$validated['aktif'] = $request->boolean('aktif', true);

		$dataLama = $unitKerja->toArray();
		$unitKerja->update($validated);

		$this->catatLog(
			'ubah',
			'Unit Kerja',
			$unitKerja->id,
			$unitKerja->nama,
			'Data unit kerja diperbarui: ' . $unitKerja->kode,
			$dataLama,
			$unitKerja->fresh()->toArray()
		);

		return redirect()->route('unit-kerja.index')
			->with('sukses', 'Unit kerja berhasil diperbarui.');
	}

	public function destroy(UnitKerja $unitKerja)
	{
		$dataLama = $unitKerja->toArray();
		$label = $unitKerja->nama;
		$kode = $unitKerja->kode;

		$unitKerja->delete();

		$this->catatLog('hapus', 'Unit Kerja', null, $label, 'Unit kerja dihapus: ' . $kode, $dataLama);

		return redirect()->route('unit-kerja.index')
			->with('sukses', 'Unit kerja berhasil dihapus.');
	}

	public function importForm()
	{
		return view('koperasi.unit-kerja.import');
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

		$rows = CsvImportReader::read($request->file('file')->getRealPath());
		if ($rows === []) {
			return redirect()->back()->with('error', 'File CSV kosong.');
		}

		$berhasil = [];
		$dilewati = [];
		$error = [];

		foreach ($rows as ['baris' => $baris, 'data' => $data]) {
			$kode = $data['kode'] ?? null;
			$nama = $data['nama'] ?? null;
			$singkatan = $data['singkatan'] ?? null;
			$keterangan = $data['keterangan'] ?? null;

			try {
				$validator = \Illuminate\Support\Facades\Validator::make($data, [
					'kode' => 'required|string|max:20',
					'nama' => 'required|string|max:255',
					'singkatan' => 'nullable|string|max:20',
				], [
					'kode.required' => 'Kode unit kerja wajib diisi.',
					'nama.required' => 'Nama unit kerja wajib diisi.',
				]);

				if ($validator->fails()) {
					$error[] = [
						'baris' => $baris,
						'kode' => $kode ?? '-',
						'pesan' => implode('; ', $validator->errors()->all()),
					];
					continue;
				}

				if (UnitKerja::where('kode', $kode)->exists()) {
					$dilewati[] = [
						'baris' => $baris,
						'kode' => $kode,
						'nama' => $nama,
						'alasan' => 'Kode unit kerja sudah terdaftar.',
					];
					continue;
				}

				$unitKerja = UnitKerja::create([
					'kode' => $kode,
					'nama' => $nama,
					'singkatan' => $singkatan ?: null,
					'keterangan' => $keterangan ?: null,
					'aktif' => true,
				]);

				$berhasil[] = [
					'baris' => $baris,
					'nama' => $nama,
					'kode' => $kode,
				];

				$this->catatLog(
					'tambah',
					'Unit Kerja',
					$unitKerja->id,
					$unitKerja->nama,
					'Unit kerja diimport dari CSV: ' . $kode,
					null,
					$unitKerja->toArray()
				);
			} catch (\Exception $e) {
				$error[] = [
					'baris' => $baris,
					'kode' => $kode ?? '-',
					'pesan' => $e->getMessage(),
				];
			}
		}

		if ($error === []) {
			$request->session()->forget('import_errors.unit-kerja');
		} else {
			$request->session()->put('import_errors.unit-kerja', $error);
		}

		return view('koperasi.unit-kerja.import-hasil', compact('berhasil', 'dilewati', 'error'));
	}
}