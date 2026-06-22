@extends('layouts.aplikasi')

@section('judul', 'Laporan Pertanggungjawaban')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('laporan.keuangan') }}">Laporan</a></li>
  <li class="breadcrumb-item active">LPJ</li>
@endsection

@section('konten')
@php
  $rp = fn($v) => 'Rp ' . number_format($v, 0, ',', '.');
  $namaKoperasi = $profil['nama_koperasi'] ?? 'Koperasi';

  // Cek kelengkapan data untuk masing-masing langkah
  $cekProfil    = !empty(trim($profil['nama_koperasi'] ?? ''));
  $cekPengurus  = !empty(trim($profil['pgrs_ketua_nama'] ?? ''));
  $cekAnggota   = $totalAnggota > 0;
  $cekSimpanan  = $totalSimpananKumulatif > 0;
  $cekPinjaman  = $jumlahPinjamanDiajukan > 0;
  $cekKeuangan  = ($totalPendapatan + $totalBiayaOps) > 0;
  $cekShu       = $shuRecord !== null;

  // LPJ siap cetak jika 5 langkah wajib (1-5) terpenuhi
  $siapCetak = $cekProfil && $cekPengurus && $cekAnggota && $cekSimpanan && $cekPinjaman;
  $jumlahSelesai = (int)$cekProfil + (int)$cekPengurus + (int)$cekAnggota
                 + (int)$cekSimpanan + (int)$cekPinjaman + (int)$cekKeuangan + (int)$cekShu;
@endphp

<div class="row justify-content-center">
<div class="col-12">

{{-- Banner --}}
<div class="dashboard-welcome mb-4">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
      <div class="welcome-label mb-1">Laporan</div>
      <h4 class="mb-1 fw-700">Laporan Pertanggungjawaban</h4>
      <p class="mb-0 welcome-date">
        <i class="bi bi-calendar3 me-1"></i>
        Periode Tahun <strong>{{ $tahun }}</strong>
      </p>
    </div>
    <div class="welcome-icon-wrap"><i class="bi bi-journal-richtext"></i></div>
  </div>
</div>

{{-- Filter --}}
<div class="form-card mb-4">
  <form method="GET" action="{{ route('laporan.lpj') }}" class="row g-3 align-items-end">
    <div class="col-md-3">
      <label class="form-label fw-600">Tahun</label>
      <select name="tahun" class="form-select">
        @foreach($tahunList as $y)
          <option value="{{ $y }}" @selected($tahun == $y)>{{ $y }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-md-auto">
      <button type="submit" class="btn btn-primary">
        <i class="bi bi-funnel me-1"></i> Tampilkan
      </button>
      <a href="{{ route('laporan.lpj.cetak', ['tahun' => $tahun]) }}" target="_blank"
         class="btn btn-outline-danger ms-2">
        <i class="bi bi-printer me-1"></i> Cetak / PDF
      </a>
    </div>
  </form>
</div>

{{-- ══ STAT CARDS ══════════════════════════════════════════════════════ --}}
<div class="row g-3 mb-4">
  <div class="col-xl-3 col-md-6">
    <div class="kstat-card kstat-blue">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-people-fill"></i></div>
      <div class="kstat-value">{{ $anggotaAktif }}</div>
      <div class="kstat-label">Anggota Aktif</div>
      <div class="kstat-trend"><i class="bi bi-person-plus me-1"></i>{{ $anggotaBaru }} bergabung tahun ini</div>
    </div>
  </div>
  <div class="col-xl-3 col-md-6">
    <div class="kstat-card kstat-green">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-piggy-bank-fill"></i></div>
      <div class="kstat-value kstat-value-sm">{{ $rp($totalSimpananTahun) }}</div>
      <div class="kstat-label">Simpanan Masuk</div>
      <div class="kstat-trend"><i class="bi bi-bank me-1"></i>Kumulatif {{ $rp($totalSimpananKumulatif) }}</div>
    </div>
  </div>
  <div class="col-xl-3 col-md-6">
    <div class="kstat-card kstat-amber">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-cash-coin"></i></div>
      <div class="kstat-value kstat-value-sm">{{ $rp($nilaiPinjamanDicairkan) }}</div>
      <div class="kstat-label">Pinjaman Dicairkan</div>
      <div class="kstat-trend"><i class="bi bi-check2-circle me-1"></i>{{ $jumlahPinjamanDisetujui }} pinjaman</div>
    </div>
  </div>
  <div class="col-xl-3 col-md-6">
    <div class="kstat-card {{ $shuBersih >= 0 ? 'kstat-purple' : 'kstat-red' }}">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-pie-chart-fill"></i></div>
      <div class="kstat-value kstat-value-sm">{{ $rp($shuBersih) }}</div>
      <div class="kstat-label">SHU Bersih</div>
      <div class="kstat-trend">
        @if($shuRecord)
          <i class="bi bi-check-circle me-1"></i>Sudah ditetapkan
        @else
          <i class="bi bi-clock me-1"></i>Belum ditetapkan
        @endif
      </div>
    </div>
  </div>
</div>

{{-- ══ SECTION I: KEANGGOTAAN ════════════════════════════════════════ --}}
<div class="form-card mb-4">
  <h6 class="fw-700 mb-4 d-flex align-items-center gap-2 is-bc519fd0">
    <i class="bi bi-people-fill"></i> I. Perkembangan Keanggotaan
  </h6>
  <div class="table-responsive">
    <table class="table table-bordered table-sm mb-0">
      <thead class="table-light">
        <tr><th>Keterangan</th><th class="text-end is-2f01efdd">Jumlah</th></tr>
      </thead>
      <tbody>
        <tr><td>Anggota awal tahun {{ $tahun }}</td><td class="text-end fw-600">{{ $anggotaAwalTahun }} orang</td></tr>
        <tr><td>Anggota baru masuk tahun {{ $tahun }}</td><td class="text-end fw-600 text-success">+ {{ $anggotaBaru }} orang</td></tr>
        <tr class="table-primary"><td class="fw-700">Anggota akhir tahun {{ $tahun }}</td><td class="text-end fw-700">{{ $totalAnggota }} orang</td></tr>
        <tr><td class="ps-4 text-muted">— Aktif</td><td class="text-end">{{ $anggotaAktif }} orang</td></tr>
        <tr><td class="ps-4 text-muted">— Tidak Aktif / Keluar</td><td class="text-end">{{ $anggotaTidakAktif }} orang</td></tr>
      </tbody>
    </table>
  </div>
</div>

{{-- ══ SECTION II: SIMPANAN ══════════════════════════════════════════ --}}
<div class="form-card mb-4">
  <h6 class="fw-700 mb-4 d-flex align-items-center gap-2 is-bc519fd0">
    <i class="bi bi-piggy-bank-fill"></i> II. Perkembangan Simpanan Tahun {{ $tahun }}
  </h6>
  <div class="table-responsive">
    <table class="table table-bordered table-sm mb-0">
      <thead class="table-light">
        <tr><th>Jenis Simpanan</th><th class="text-end is-8f77872c">Jumlah Diterima (Rp)</th></tr>
      </thead>
      <tbody>
        <tr><td>Simpanan Pokok</td><td class="text-end">{{ number_format($simpananPokok, 0, ',', '.') }}</td></tr>
        <tr><td>Simpanan Wajib</td><td class="text-end">{{ number_format($simpananWajib, 0, ',', '.') }}</td></tr>
        <tr><td>Simpanan Sukarela</td><td class="text-end">{{ number_format($simpananSukarela, 0, ',', '.') }}</td></tr>
        <tr class="table-primary fw-700"><td>Total Simpanan Tahun {{ $tahun }}</td><td class="text-end">{{ number_format($totalSimpananTahun, 0, ',', '.') }}</td></tr>
        <tr class="table-success fw-700"><td>Total Simpanan Kumulatif (s.d. akhir {{ $tahun }})</td><td class="text-end">{{ number_format($totalSimpananKumulatif, 0, ',', '.') }}</td></tr>
      </tbody>
    </table>
  </div>
</div>

{{-- ══ SECTION III: PINJAMAN ═════════════════════════════════════════ --}}
<div class="form-card mb-4">
  <h6 class="fw-700 mb-4 d-flex align-items-center gap-2 is-bc519fd0">
    <i class="bi bi-cash-coin"></i> III. Perkembangan Pinjaman & Angsuran Tahun {{ $tahun }}
  </h6>
  <div class="row g-4">
    <div class="col-md-6">
      <div class="fw-600 mb-2 text-muted is-1e28117f">PINJAMAN</div>
      <div class="table-responsive">
        <table class="table table-bordered table-sm mb-0">
          <tbody>
            <tr><td>Pinjaman diajukan</td><td class="text-end fw-600">{{ $jumlahPinjamanDiajukan }}</td></tr>
            <tr><td>Pinjaman disetujui & dicairkan</td><td class="text-end fw-600">{{ $jumlahPinjamanDisetujui }}</td></tr>
            <tr class="table-primary"><td class="fw-700">Total nilai dicairkan</td><td class="text-end fw-700">{{ number_format($nilaiPinjamanDicairkan, 0, ',', '.') }}</td></tr>
            <tr><td>Pinjaman aktif (saat ini)</td><td class="text-end">{{ $jumlahPinjamanAktif }}</td></tr>
            <tr><td>Outstanding pinjaman aktif</td><td class="text-end">{{ number_format($nilaiPinjamanAktif, 0, ',', '.') }}</td></tr>
          </tbody>
        </table>
      </div>
    </div>
    <div class="col-md-6">
      <div class="fw-600 mb-2 text-muted is-1e28117f">ANGSURAN</div>
      <div class="table-responsive">
        <table class="table table-bordered table-sm mb-0">
          <tbody>
            <tr><td>Angsuran pokok diterima</td><td class="text-end fw-600">{{ number_format($totalPokokDiterima, 0, ',', '.') }}</td></tr>
            <tr><td>Pendapatan bunga diterima</td><td class="text-end fw-600">{{ number_format($totalBungaDiterima, 0, ',', '.') }}</td></tr>
            <tr class="table-primary"><td class="fw-700">Total angsuran diterima</td><td class="text-end fw-700">{{ number_format($totalAngsuranDiterima, 0, ',', '.') }}</td></tr>
            <tr><td class="text-warning-emphasis">Angsuran tertunggak (aktif)</td><td class="text-end text-warning-emphasis">{{ number_format($totalAngsuranTunggak, 0, ',', '.') }}</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

{{-- ══ SECTION IV: ARUS KAS ══════════════════════════════════════════ --}}
<div class="form-card mb-4">
  <h6 class="fw-700 mb-4 d-flex align-items-center gap-2 is-bc519fd0">
    <i class="bi bi-arrow-left-right"></i> IV. Ringkasan Arus Kas Tahun {{ $tahun }}
  </h6>
  <div class="table-responsive">
    <table class="table table-bordered table-sm mb-0">
      <thead class="table-light">
        <tr><th>Uraian</th><th class="text-end is-69e6838d">Jumlah (Rp)</th></tr>
      </thead>
      <tbody>
        <tr class="table-secondary"><td colspan="2" class="fw-700">A. Penerimaan</td></tr>
        <tr><td class="ps-4">Simpanan Anggota</td><td class="text-end">{{ number_format($totalSimpananTahun, 0, ',', '.') }}</td></tr>
        <tr><td class="ps-4">Angsuran Pinjaman</td><td class="text-end">{{ number_format($totalAngsuranDiterima, 0, ',', '.') }}</td></tr>
        <tr class="fw-700"><td class="ps-2">Jumlah Penerimaan</td><td class="text-end">{{ number_format($totalPemasukan, 0, ',', '.') }}</td></tr>

        <tr class="table-secondary"><td colspan="2" class="fw-700">B. Pengeluaran</td></tr>
        <tr><td class="ps-4">Pencairan Pinjaman</td><td class="text-end">{{ number_format($totalPengeluaran, 0, ',', '.') }}</td></tr>
        <tr class="fw-700"><td class="ps-2">Jumlah Pengeluaran</td><td class="text-end">{{ number_format($totalPengeluaran, 0, ',', '.') }}</td></tr>

        <tr class="{{ $arusKasBersih >= 0 ? 'table-success' : 'table-danger' }} fw-700">
          <td>{{ $arusKasBersih >= 0 ? 'SURPLUS' : 'DEFISIT' }} ARUS KAS</td>
          <td class="text-end">{{ $arusKasBersih >= 0 ? '' : '(' }}{{ number_format(abs($arusKasBersih), 0, ',', '.') }}{{ $arusKasBersih >= 0 ? '' : ')' }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

{{-- ══ SECTION V: LABA RUGI & SHU ═══════════════════════════════════ --}}
<div class="form-card mb-4">
  <h6 class="fw-700 mb-4 d-flex align-items-center gap-2 is-bc519fd0">
    <i class="bi bi-bar-chart-fill"></i> V. Laba Rugi & Sisa Hasil Usaha Tahun {{ $tahun }}
  </h6>
  <div class="table-responsive">
    <table class="table table-bordered table-sm mb-0">
      <thead class="table-light">
        <tr><th>Uraian</th><th class="text-end is-69e6838d">Jumlah (Rp)</th></tr>
      </thead>
      <tbody>
        <tr class="table-secondary"><td colspan="2" class="fw-700">Pendapatan</td></tr>
        <tr><td class="ps-4">Pendapatan Bunga Pinjaman</td><td class="text-end">{{ number_format($bungaMasuk, 0, ',', '.') }}</td></tr>
        <tr><td class="ps-4">Pendapatan Lainnya</td><td class="text-end">{{ number_format($pendapatanLain, 0, ',', '.') }}</td></tr>
        <tr class="fw-700"><td class="ps-2">Total Pendapatan</td><td class="text-end">{{ number_format($totalPendapatan, 0, ',', '.') }}</td></tr>

        <tr class="table-secondary"><td colspan="2" class="fw-700">Biaya Operasional</td></tr>
        <tr class="fw-700"><td class="ps-2">Total Biaya</td><td class="text-end">{{ number_format($totalBiayaOps, 0, ',', '.') }}</td></tr>

        <tr class="{{ $shuBersih >= 0 ? 'table-success' : 'table-danger' }} fw-700">
          <td>SHU BERSIH</td>
          <td class="text-end">
            {{ $shuBersih >= 0 ? '' : '(' }}{{ number_format(abs($shuBersih), 0, ',', '.') }}{{ $shuBersih >= 0 ? '' : ')' }}
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  @if($shuRecord)
  <div class="mt-3 p-3 rounded-3 is-7359c1bc">
    <div class="d-flex align-items-center gap-2">
      <i class="bi bi-check-circle-fill text-success"></i>
      <span class="fw-600">SHU Tahun {{ $tahun }} telah ditetapkan:</span>
      <span class="text-success fw-700">Rp {{ number_format($shuRecord->total_shu, 0, ',', '.') }}</span>
    </div>
  </div>
  @else
  <div class="mt-3 p-3 rounded-3 is-a36464af">
    <div class="d-flex align-items-center gap-2">
      <i class="bi bi-info-circle-fill is-0c118ea4"></i>
      <span class="is-9a2b98e5">SHU tahun {{ $tahun }} belum ditetapkan di modul SHU.</span>
      <a href="{{ route('shu.index') }}" class="btn btn-sm btn-warning ms-auto">
        <i class="bi bi-pie-chart me-1"></i> Ke Modul SHU
      </a>
    </div>
  </div>
  @endif
</div>

{{-- Footer action --}}
<div class="d-flex justify-content-end gap-2 mb-4">
  <a href="{{ route('laporan.lpj.cetak', ['tahun' => $tahun]) }}" target="_blank"
     class="btn btn-danger">
    <i class="bi bi-printer me-1"></i> Cetak LPJ Formal
  </a>
</div>

</div>
</div>
@endsection
