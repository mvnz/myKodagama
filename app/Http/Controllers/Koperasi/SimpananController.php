<?php

namespace App\Http\Controllers\Koperasi;

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

	private function isAnggotaUser($user): bool
	{
		return ($user->roleModel?->slug ?? $user->role) === 'anggota';
	}
}