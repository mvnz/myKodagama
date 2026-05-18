@extends('layouts.aplikasi')

@section('judul', 'Detail SHU Tahun ' . $shu->tahun)

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('shu.index') }}">SHU</a></li>
  <li class="breadcrumb-item active">Tahun {{ $shu->tahun }}</li>
@endsection

@section('konten')

@php
  $totalJasaModal = $shu->anggota->sum('jasa_modal');
  $totalJasaUsaha = $shu->anggota->sum('jasa_usaha');
  $totalDidistribusi = $shu->anggota->sum('total_shu_anggota');
@endphp

<div class="dashboard-welcome mb-4">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
      <div class="welcome-label mb-1">SHU &mdash; Tahun {{ $shu->tahun }}</div>
      <h4 class="mb-1 fw-700">Detail Sisa Hasil Usaha</h4>
      <p class="mb-0 welcome-date">
        Status:
        @if($shu->status === 'final')
          <span class="badge bg-success ms-1">Final</span>
        @else
          <span class="badge bg-warning text-dark ms-1">Draft</span>
        @endif
      </p>
    </div>
    <div class="welcome-icon-wrap"><i class="bi bi-pie-chart-fill"></i></div>
  </div>
</div>

@if(session('sukses'))
  <div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle me-2"></i>{{ session('sukses') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
@endif
@if(session('error'))
  <div class="alert alert-danger alert-dismissible fade show">
    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
@endif

{{-- Stat Cards --}}
<div class="row g-3 mb-4">
  <div class="col-md-3 col-sm-6">
    <div class="kstat-card kstat-blue">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-arrow-up-circle"></i></div>
      <div class="kstat-value" style="font-size:1.1rem">Rp {{ number_format($shu->total_pendapatan, 0, ',', '.') }}</div>
      <div class="kstat-label">Total Pendapatan</div>
    </div>
  </div>
  <div class="col-md-3 col-sm-6">
    <div class="kstat-card kstat-red">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-arrow-down-circle"></i></div>
      <div class="kstat-value" style="font-size:1.1rem">Rp {{ number_format($shu->total_biaya, 0, ',', '.') }}</div>
      <div class="kstat-label">Total Biaya</div>
    </div>
  </div>
  <div class="col-md-3 col-sm-6">
    <div class="kstat-card kstat-green">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-pie-chart"></i></div>
      <div class="kstat-value" style="font-size:1.1rem">Rp {{ number_format($shu->total_shu, 0, ',', '.') }}</div>
      <div class="kstat-label">Total SHU</div>
    </div>
  </div>
  <div class="col-md-3 col-sm-6">
    <div class="kstat-card kstat-amber">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-people"></i></div>
      <div class="kstat-value">{{ $shu->anggota->count() }}</div>
      <div class="kstat-label">Anggota Dapat SHU</div>
    </div>
  </div>
</div>

{{-- Info & Aksi --}}
<div class="form-card mb-4">
  <div class="row g-4">
    <div class="col-md-6">
      <table class="table table-sm table-borderless mb-0">
        <tr><th style="width:40%">Tahun</th><td>{{ $shu->tahun }}</td></tr>
        <tr><th>% Jasa Modal</th><td>{{ $shu->persentase_jasa_modal }}%</td></tr>
        <tr><th>% Jasa Usaha</th><td>{{ $shu->persentase_jasa_usaha }}%</td></tr>
        <tr><th>Total Didistribusi</th><td class="text-success fw-700">Rp {{ number_format($totalDidistribusi, 0, ',', '.') }}</td></tr>
        <tr><th>Dibuat oleh</th><td>{{ $shu->pembuat->name ?? '-' }}</td></tr>
        <tr><th>Keterangan</th><td>{{ $shu->keterangan ?: '-' }}</td></tr>
      </table>
    </div>
    <div class="col-md-6 d-flex flex-column gap-2 justify-content-center">
      <a href="{{ route('shu.cetak', $shu) }}" class="btn btn-outline-success" target="_blank">
        <i class="bi bi-printer me-1"></i> Cetak Laporan
      </a>
      @can('shu.kelola')
        @if($shu->status === 'draft')
          <form action="{{ route('shu.finalisasi', $shu) }}" method="POST"
                onsubmit="return confirm('Finalisasi SHU tahun {{ $shu->tahun }}? Data tidak bisa diubah setelah final.')">
            @csrf
            <button class="btn btn-success w-100">
              <i class="bi bi-check-circle me-1"></i> Finalisasi SHU
            </button>
          </form>
          <form action="{{ route('shu.destroy', $shu) }}" method="POST"
                onsubmit="return confirm('Hapus data SHU tahun {{ $shu->tahun }}?')">
            @csrf @method('DELETE')
            <button class="btn btn-outline-danger w-100">
              <i class="bi bi-trash me-1"></i> Hapus Draft
            </button>
          </form>
        @endif
      @endcan
      <a href="{{ route('shu.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali
      </a>
    </div>
  </div>
</div>

{{-- Tabel Per Anggota --}}
<div class="form-card p-0 overflow-hidden">
  <div class="p-4 border-bottom">
    <h6 class="mb-0 fw-700" style="color:#112344">
      <i class="bi bi-table me-2" style="color:#175cdd"></i>Rincian SHU per Anggota
    </h6>
  </div>
  <div class="table-responsive">
    <table class="table koperasi-table mb-0">
      <thead>
        <tr>
          <th>No</th>
          <th>Nama Anggota</th>
          <th class="text-end">Total Simpanan</th>
          <th class="text-end">Total Pinjaman</th>
          <th class="text-end">Jasa Modal</th>
          <th class="text-end">Jasa Usaha</th>
          <th class="text-end fw-700">Total SHU</th>
        </tr>
      </thead>
      <tbody>
        @foreach($shu->anggota->sortByDesc('total_shu_anggota') as $i => $item)
          <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $item->anggota->nama ?? '-' }}</td>
            <td class="text-end">Rp {{ number_format($item->total_simpanan, 0, ',', '.') }}</td>
            <td class="text-end">Rp {{ number_format($item->total_pinjaman, 0, ',', '.') }}</td>
            <td class="text-end">Rp {{ number_format($item->jasa_modal, 0, ',', '.') }}</td>
            <td class="text-end">Rp {{ number_format($item->jasa_usaha, 0, ',', '.') }}</td>
            <td class="text-end fw-700 text-success">Rp {{ number_format($item->total_shu_anggota, 0, ',', '.') }}</td>
          </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr class="fw-700" style="background:#f8fafc">
          <td colspan="4" class="text-end">Total Didistribusi:</td>
          <td class="text-end">Rp {{ number_format($totalJasaModal, 0, ',', '.') }}</td>
          <td class="text-end">Rp {{ number_format($totalJasaUsaha, 0, ',', '.') }}</td>
          <td class="text-end text-success">Rp {{ number_format($totalDidistribusi, 0, ',', '.') }}</td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>

@endsection
