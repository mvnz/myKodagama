<?php

namespace App\Http\Controllers\Koperasi;

use App\Support\CsvImportReader;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\CatatAktivitas;
use App\Models\Koperasi\Anggota;
use App\Models\Koperasi\Simpanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SimpananController extends Controller
{
	use CatatAktivitas;

	public function index(Request $request)
	{
		$cari = $request->input('cari');
		$jenis = $request->input('jenis');
		$allowedSort = ['created_at', 'jenis_simpanan', 'jumlah', 'tanggal'];
		$sortBy  = in_array($request->input('sort'), $allowedSort, true) ? $request->input('sort') : 'created_at';
		$sortDir = $request->input('dir') === 'asc' ? 'asc' : 'desc';
		$user = Auth::user();
		$anggotaId = $this->isAnggotaUser($user) ? $user->anggota_id : null;

		$simpanan = Simpanan::with('anggota')
			->when($anggotaId, fn ($q) => $q->where('anggota_id', $anggotaId))
			->when($cari, function ($q) use ($cari) {
				$q->whereHas('anggota', fn ($aq) => $aq->where('nama', 'like', "%$cari%")
					->orWhere('nomor_anggota', 'like', "%$cari%"));
			})
			->when($jenis, fn ($q) => $q->where('jenis_simpanan', $jenis))
			->orderBy($sortBy, $sortDir)
			->paginate(15)
			->withQueryString();

		$totalPerJenis = Simpanan::selectRaw('jenis_simpanan, SUM(jumlah) as total')
			->when($anggotaId, fn ($q) => $q->where('anggota_id', $anggotaId))
			->groupBy('jenis_simpanan')
			->pluck('total', 'jenis_simpanan');

		$totalSimpanan = $totalPerJenis->sum();
		$jumlahTransaksi = Simpanan::when($anggotaId, fn ($q) => $q->where('anggota_id', $anggotaId))->count();
		$transaksiBuilanIni = Simpanan::when($anggotaId, fn ($q) => $q->where('anggota_id', $anggotaId))
			->whereMonth('tanggal', now()->month)
			->whereYear('tanggal', now()->year)
			->count();

		return view('koperasi.simpanan.index', compact('simpanan', 'cari', 'jenis', 'totalPerJenis', 'totalSimpanan', 'jumlahTransaksi', 'transaksiBuilanIni', 'sortBy', 'sortDir'));
	}

	public function create()
	{
		$anggota = Anggota::where('status', 'aktif')->orderBy('nama')->get();

		return view('koperasi.simpanan.tambah', compact('anggota'));
	}

	public function store(Request $request)
	{
		$this->authorize('simpanan.tambah');

		$validated = $request->validate([
			'anggota_id' => 'required|exists:anggota,id',
			'jenis_simpanan' => 'required|in:pokok,wajib,sukarela',
			'jumlah' => 'required|numeric|min:1000',
			'tanggal' => 'required|date',
			'keterangan' => 'nullable|string|max:255',
		], [
			'anggota_id.required' => 'Anggota wajib dipilih.',
			'jenis_simpanan.required' => 'Jenis simpanan wajib dipilih.',
			'jumlah.required' => 'Jumlah wajib diisi.',
			'jumlah.min' => 'Jumlah minimal Rp 1.000.',
			'tanggal.required' => 'Tanggal wajib diisi.',
		]);

		$validated['user_id'] = Auth::id();
		$anggota = Anggota::findOrFail((int) $validated['anggota_id']);
		$validated['jenis_anggota'] = $anggota->jenis_anggota;
		$simpanan = Simpanan::create($validated);
		$simpanan->load('anggota');

		$this->catatLog(
			'tambah',
			'Simpanan',
			$simpanan->id,
			$simpanan->anggota->nama ?? '-',
			'Simpanan ' . $simpanan->jenis_simpanan . ' Rp ' . number_format($simpanan->jumlah, 0, ',', '.') . ' dicatat',
			null,
			$simpanan->toArray()
		);

		return redirect()->route('simpanan.index')
			->with('sukses', 'Simpanan berhasil dicatat.');
	}

	public function show(Simpanan $simpanan)
	{
		$user = Auth::user();
		if ($this->isAnggotaUser($user) && $user->anggota_id !== $simpanan->anggota_id) {
			abort(403);
		}

		$simpanan->load('anggota', 'petugas');

		return view('koperasi.simpanan.detail', compact('simpanan'));
	}

	public function edit(Simpanan $simpanan)
	{
		$user = Auth::user();
		if ($this->isAnggotaUser($user) && $user->anggota_id !== $simpanan->anggota_id) {
			abort(403);
		}

		$anggota = Anggota::where('status', 'aktif')->orderBy('nama')->get();

		return view('koperasi.simpanan.ubah', compact('simpanan', 'anggota'));
	}

	public function update(Request $request, Simpanan $simpanan)
	{
		$user = Auth::user();
		if ($this->isAnggotaUser($user) && $user->anggota_id !== $simpanan->anggota_id) {
			abort(403);
		}

		$validated = $request->validate([
			'anggota_id' => 'required|exists:anggota,id',
			'jenis_simpanan' => 'required|in:pokok,wajib,sukarela',
			'jumlah' => 'required|numeric|min:1000',
			'tanggal' => 'required|date',
			'keterangan' => 'nullable|string|max:255',
		]);

		if (empty($simpanan->jenis_anggota)) {
			$anggota = Anggota::findOrFail((int) $validated['anggota_id']);
			$validated['jenis_anggota'] = $anggota->jenis_anggota;
		}

		$dataLama = $simpanan->toArray();
		$simpanan->update($validated);
		$simpanan->load('anggota');

		$this->catatLog(
			'ubah',
			'Simpanan',
			$simpanan->id,
			$simpanan->anggota->nama ?? '-',
			'Data simpanan diperbarui',
			$dataLama,
			$simpanan->fresh()->toArray()
		);

		return redirect()->route('simpanan.index')
			->with('sukses', 'Data simpanan berhasil diperbarui.');
	}

	public function destroy(Simpanan $simpanan)
	{
		$user = Auth::user();
		if ($this->isAnggotaUser($user) && $user->anggota_id !== $simpanan->anggota_id) {
			abort(403);
		}

		$simpanan->load('anggota');
		$dataLama = $simpanan->toArray();
		$label = $simpanan->anggota->nama ?? '-';

		$simpanan->delete();

		$this->catatLog(
			'hapus',
			'Simpanan',
			null,
			$label,
			'Simpanan ' . $simpanan->jenis_simpanan . ' dihapus',
			$dataLama
		);

		return redirect()->route('simpanan.index')
			->with('sukses', 'Data simpanan berhasil dihapus.');
	}

	public function importForm()
	{
		return view('koperasi.simpanan.import');
	}

	public function import(Request $request)
	{
		$request->validate([
			'file' => 'required|file|mimes:csv,txt|max:5120',
		], [
			'file.required' => 'File CSV wajib diunggah.',
			'file.mimes' => 'File harus berformat CSV.',
			'file.max' => 'Ukuran file maksimal 5 MB.',
		]);

		$rows = CsvImportReader::read($request->file('file')->getRealPath(), [
			'no_anggota' => 'nomor_anggota',
			'nomor' => 'nomor_anggota',
		]);
		if ($rows === []) {
			return redirect()->back()->with('error', 'File CSV kosong.');
		}

		$berhasil = [];
		$dilewati = [];
		$error = [];

		foreach ($rows as ['baris' => $baris, 'data' => $data]) {
			$nomorAnggota = $data['nomor_anggota'] ?? null;
			$jenisSimpanan = isset($data['jenis_simpanan']) ? strtolower($data['jenis_simpanan']) : null;
			$jumlah = $data['jumlah'] ?? null;
			$tanggal = $data['tanggal'] ?? null;
			$keterangan = $data['keterangan'] ?? null;

			try {
				$anggota = Anggota::where('nomor_anggota', $nomorAnggota)->first();
				if (!$anggota) {
					$dilewati[] = [
						'baris' => $baris,
						'nomor' => $nomorAnggota ?? '-',
						'alasan' => 'Anggota tidak ditemukan.',
					];
					continue;
				}

				$validator = \Illuminate\Support\Facades\Validator::make([
					'jenis_simpanan' => $jenisSimpanan,
					'jumlah' => $jumlah,
					'tanggal' => $tanggal,
				], [
					'jenis_simpanan' => 'required|in:pokok,wajib,sukarela',
					'jumlah' => 'required|numeric|min:1000',
					'tanggal' => 'required|date_format:Y-m-d',
				], [
					'jenis_simpanan.required' => 'Jenis simpanan wajib diisi.',
					'jenis_simpanan.in' => 'Jenis simpanan tidak valid (pokok/wajib/sukarela).',
					'jumlah.required' => 'Jumlah wajib diisi.',
					'jumlah.numeric' => 'Jumlah harus berupa angka.',
					'jumlah.min' => 'Jumlah minimal 1000.',
					'tanggal.required' => 'Tanggal wajib diisi.',
					'tanggal.date_format' => 'Format tanggal harus YYYY-MM-DD.',
				]);

				if ($validator->fails()) {
					$error[] = [
						'baris' => $baris,
						'nomor' => $nomorAnggota ?? '-',
						'pesan' => implode('; ', $validator->errors()->all()),
					];
					continue;
				}

				$simpanan = Simpanan::create([
					'anggota_id' => $anggota->id,
					'jenis_simpanan' => $jenisSimpanan,
					'jumlah' => (int) $jumlah,
					'tanggal' => $tanggal,
					'keterangan' => $keterangan ?: null,
					'user_id' => Auth::id(),
					'jenis_anggota' => $anggota->jenis_anggota,
				]);

				$berhasil[] = [
					'baris' => $baris,
					'nama' => $anggota->nama,
					'jenis' => $jenisSimpanan,
					'jumlah' => (int) $jumlah,
				];

				$this->catatLog(
					'tambah',
					'Simpanan',
					$simpanan->id,
					$anggota->nama,
					'Simpanan ' . $jenisSimpanan . ' Rp ' . number_format($jumlah, 0, ',', '.') . ' diimport',
					null,
					$simpanan->toArray()
				);
			} catch (\Exception $e) {
				$error[] = [
					'baris' => $baris,
					'nomor' => $nomorAnggota ?? '-',
					'pesan' => $e->getMessage(),
				];
			}
		}

		if ($error === []) {
			$request->session()->forget('import_errors.simpanan');
		} else {
			$request->session()->put('import_errors.simpanan', $error);
		}

		return view('koperasi.simpanan.import-hasil', compact('berhasil', 'dilewati', 'error'));
	}

	private function isAnggotaUser($user): bool
	{
		return ($user->roleModel?->slug ?? $user->role) === 'anggota';
	}
}