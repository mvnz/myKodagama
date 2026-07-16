@extends('layouts.aplikasi')

@section('judul', 'Laporan Laba Rugi')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('laporan.keuangan') }}">Laporan</a></li>
  <li class="breadcrumb-item active">Laba Rugi</li>
@endsection

@section('konten')

{{-- Page Banner --}}
<div class="dashboard-welcome mb-4">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
      <div class="welcome-label mb-1">Laporan Keuangan</div>
      <h4 class="mb-1 fw-700">Laporan Laba Rugi</h4>
      <p class="mb-0 welcome-date">
        <i class="bi bi-calendar3 me-1"></i>
        {{ now()->translatedFormat('l, d F Y') }}
      </p>
    </div>
    <div class="welcome-icon-wrap">
      <i class="bi bi-file-earmark-bar-graph-fill"></i>
    </div>
  </div>
</div>

{{-- Filter --}}
<div class="form-card mb-4 d-print-none">
  <form method="GET" action="{{ route('laporan.laba-rugi') }}" class="row g-3 align-items-end">

    <div class="col-md-3">
      <label class="form-label fw-600">Tahun</label>
      <select name="tahun" class="form-select">
        @foreach($tahunList as $y)
          <option value="{{ $y }}" @selected($tahun == $y)>{{ $y }}</option>
        @endforeach
      </select>
    </div>

    <div class="col-md-3">
      <label class="form-label fw-600">Bulan <span class="text-muted fw-400">(opsional)</span></label>
      <select name="bulan" class="form-select">
        <option value="">Semua Bulan</option>
        @foreach(['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'] as $m => $nm)
          <option value="{{ $m }}" @selected($bulan == $m)>{{ $nm }}</option>
        @endforeach
      </select>
    </div>

    <div class="col-md-2">
      <button type="submit" class="btn btn-primary w-100">
        <i class="bi bi-funnel me-1"></i> Tampilkan
      </button>
    </div>

    <div class="col-md-2">
      <a href="{{ route('laporan.laba-rugi') }}" class="btn btn-outline-secondary w-100">
        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
      </a>
    </div>

    <div class="col-md-2">
      <a href="{{ route('laporan.laba-rugi.cetak', ['tahun' => $tahun, 'bulan' => $bulan]) }}" target="_blank" class="btn btn-outline-success w-100">
        <i class="bi bi-printer me-1"></i> Cetak
      </a>
    </div>

  </form>
</div>

{{-- Stat cards --}}
<div class="row g-4 mb-4">
  <div class="col-xl-4 col-md-6">
    <div class="kstat-card kstat-green">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-graph-up-arrow"></i></div>
      <div class="kstat-value kstat-value-sm">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</div>
      <div class="kstat-label">Total Pendapatan</div>
      <div class="kstat-trend"><i class="bi bi-arrow-up-circle-fill me-1"></i> Pemasukan operasional</div>
    </div>
  </div>
  <div class="col-xl-4 col-md-6">
    <div class="kstat-card kstat-red">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-graph-down-arrow"></i></div>
      <div class="kstat-value kstat-value-sm">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</div>
      <div class="kstat-label">Total Pengeluaran</div>
      <div class="kstat-trend"><i class="bi bi-arrow-down-circle-fill me-1"></i> Pengeluaran operasional</div>
    </div>
  </div>
  <div class="col-xl-4 col-md-6">
    <div class="kstat-card {{ $shu >= 0 ? 'kstat-blue' : 'kstat-red' }}">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-cash-coin"></i></div>
      <div class="kstat-value kstat-value-sm">{{ $shu >= 0 ? '+' : '' }}Rp {{ number_format(abs($shu), 0, ',', '.') }}</div>
      <div class="kstat-label">SHU {{ $shu >= 0 ? '(Untung)' : '(Rugi)' }}</div>
      <div class="kstat-trend">
        <i class="bi bi-{{ $shu >= 0 ? 'patch-check-fill' : 'exclamation-triangle-fill' }} me-1"></i>
        {{ $shu >= 0 ? 'Surplus' : 'Defisit' }}
      </div>
    </div>
  </div>
</div>

<div class="row g-4">
  {{-- Pendapatan --}}
  <div class="col-lg-6">
    <div class="form-card h-100">
      <h6 class="fw-700 mb-4 d-flex align-items-center gap-2 is-bc519fd0">
        <i class="bi bi-plus-circle-fill text-success"></i> Rincian Pendapatan
      </h6>

      {{-- Bunga --}}
      <div class="d-flex justify-content-between mb-1">
        <span class="fw-600 small">Bunga Pinjaman (diterima)</span>
        <span class="fw-700 small">Rp {{ number_format($bungaMasuk, 0, ',', '.') }}</span>
      </div>

      {{-- Pendapatan lain --}}
      @foreach($pendapatanLain->sortByDesc(fn($v)=>$v) as $kat => $total)
        <div class="d-flex justify-content-between mb-1 mt-2">
          <span class="fw-600 small text-muted ps-2">{{ $labelKategoriPendapatan[$kat] ?? $kat }}</span>
          <span class="small">Rp {{ number_format($total, 0, ',', '.') }}</span>
        </div>
      @endforeach

      <hr class="my-3">

      <h6 class="fw-700 mb-3 small text-uppercase text-muted">Rekap Pendapatan</h6>
      <table class="table table-sm mb-0 is-1e28117f">
        <tbody>
          <tr>
            <td class="text-muted">Bunga Pinjaman</td>
            <td class="text-end fw-600 text-success">+ Rp {{ number_format($bungaMasuk, 0, ',', '.') }}</td>
          </tr>
          @foreach($pendapatanLain->sortByDesc(fn($v)=>$v) as $kat => $total)
          <tr>
            <td class="text-muted">{{ $labelKategoriPendapatan[$kat] ?? $kat }}</td>
            <td class="text-end fw-600 text-success">+ Rp {{ number_format($total, 0, ',', '.') }}</td>
          </tr>
          @endforeach
          <tr class="border-top">
            <td class="fw-700">Total Pendapatan</td>
            <td class="text-end fw-700 text-success">+ Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  {{-- Pengeluaran --}}
  <div class="col-lg-6">
    <div class="form-card h-100">
      <h6 class="fw-700 mb-4 d-flex align-items-center gap-2 is-bc519fd0">
        <i class="bi bi-dash-circle-fill text-danger"></i> Rincian Pengeluaran
      </h6>

      @forelse($pengeluaran->sortByDesc(fn($v)=>$v) as $kat => $total)
        <div class="d-flex justify-content-between mb-1">
          <span class="fw-600 small">{{ $labelKategoriPengeluaran[$kat] ?? $kat }}</span>
          <span class="fw-700 small">Rp {{ number_format($total, 0, ',', '.') }}</span>
        </div>
      @empty
        <div class="text-muted small">Belum ada data pengeluaran.</div>
      @endforelse

      <hr class="my-3">

      <h6 class="fw-700 mb-3 small text-uppercase text-muted">Rekap Laba Rugi</h6>
      <table class="table table-sm mb-0 is-1e28117f">
        <tbody>
          <tr>
            <td class="text-muted">Total Pendapatan</td>
            <td class="text-end fw-600 text-success">+ Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</td>
          </tr>
          <tr>
            <td class="text-muted">Total Pengeluaran</td>
            <td class="text-end fw-600 text-danger">− Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</td>
          </tr>
          <tr class="border-top">
            <td class="fw-700">Sisa Hasil Usaha (SHU)</td>
            <td class="text-end fw-700 {{ $shu >= 0 ? 'text-success' : 'text-danger' }}">
              {{ $shu >= 0 ? '+' : '−' }} Rp {{ number_format(abs($shu), 0, ',', '.') }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
