@extends('layouts.aplikasi')

@section('judul', 'Monitoring Simpanan Wajib')

@section('breadcrumb')
  <li class="breadcrumb-item">Monitoring</li>
  <li class="breadcrumb-item active">Simpanan Wajib</li>
@endsection

@section('konten')

@php
  $namaBulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
  $tahunSekarang = now()->year;
  $total = $sudahSetor->count() + $belumSetor->count();
  $persen = $total > 0 ? round($sudahSetor->count() / $total * 100) : 0;
@endphp

{{-- Page Banner --}}
<div class="dashboard-welcome mb-4">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
      <div class="welcome-label mb-1">Monitoring Keuangan</div>
      <h4 class="mb-1 fw-700">Monitoring Simpanan Wajib</h4>
      <p class="mb-0 welcome-date">
        <i class="bi bi-calendar3 me-1"></i>
        {{ now()->translatedFormat('l, d F Y') }}
      </p>
    </div>
    <div class="welcome-icon-wrap">
      <i class="bi bi-piggy-bank-fill"></i>
    </div>
  </div>
</div>

{{-- Stat Cards --}}
<div class="row g-4 mb-4">
  <div class="col-xl-3 col-sm-6">
    <div class="kstat-card kstat-blue">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-people-fill"></i></div>
      <div class="kstat-value">{{ number_format($total) }}</div>
      <div class="kstat-label">Total Anggota Aktif</div>
      <div class="kstat-trend"><i class="bi bi-calendar-month me-1"></i> {{ $namaBulan[$bulan] }} {{ $tahun }}</div>
    </div>
  </div>
  <div class="col-xl-3 col-sm-6">
    <div class="kstat-card kstat-green">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-check-circle-fill"></i></div>
      <div class="kstat-value">{{ number_format($sudahSetor->count()) }}</div>
      <div class="kstat-label">Sudah Setor</div>
      <div class="kstat-trend"><i class="bi bi-check2-all me-1"></i> Sudah menyetor</div>
    </div>
  </div>
  <div class="col-xl-3 col-sm-6">
    <div class="kstat-card kstat-red">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-x-circle-fill"></i></div>
      <div class="kstat-value">{{ number_format($belumSetor->count()) }}</div>
      <div class="kstat-label">Belum Setor</div>
      <div class="kstat-trend"><i class="bi bi-exclamation-triangle-fill me-1"></i> Perlu ditindaklanjuti</div>
    </div>
  </div>
  <div class="col-xl-3 col-sm-6">
    <div class="kstat-card kstat-amber">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-percent"></i></div>
      <div class="kstat-value">{{ $persen }}%</div>
      <div class="kstat-label">Tingkat Kepatuhan</div>
      <div class="kstat-trend"><i class="bi bi-bar-chart-fill me-1"></i> Bulan ini</div>
    </div>
  </div>
</div>

{{-- ─── FILTER ─── --}}
<form method="GET" action="{{ route('monitoring.simpanan') }}" class="form-card mb-4">
  <div class="row g-3 align-items-end">
    <div class="col-sm-4 col-md-3">
      <label class="form-label fw-600">Bulan</label>
      <select name="bulan" class="form-select">
        @for($m = 1; $m <= 12; $m++)
          <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>{{ $namaBulan[$m] }}</option>
        @endfor
      </select>
    </div>
    <div class="col-sm-4 col-md-3">
      <label class="form-label fw-600">Tahun</label>
      <select name="tahun" class="form-select">
        @for($y = $tahunSekarang; $y >= $tahunSekarang - 5; $y--)
          <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
        @endfor
      </select>
    </div>
    <div class="col-md-auto">
      <button type="submit" class="btn btn-primary">
        <i class="bi bi-funnel me-1"></i> Filter
      </button>
    </div>
  </div>
</form>

<div class="row g-4">

  {{-- ─── BELUM SETOR ─── --}}
  <div class="col-lg-7">
    <div class="form-card p-0 overflow-hidden">
      <div class="d-flex align-items-center gap-2 p-3 border-bottom is-7c743ce2"
       >
        <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0 is-828367f4"
         >
          <i class="bi bi-exclamation-triangle-fill"></i>
        </div>
        <div class="flex-grow-1">
          <h6 class="mb-0 fw-700 is-217480ed">Belum Setor Simpanan Wajib</h6>
          <small class="text-muted" id="belumSetorCount">{{ $namaBulan[$bulan] }} {{ $tahun }} · {{ $belumSetor->count() }} anggota</small>
        </div>
      </div>
      <div class="px-3 py-2 border-bottom is-4f725a30">
        <div class="input-group input-group-sm">
          <span class="input-group-text bg-white border-end-0 is-f02c5199"><i class="bi bi-search"></i></span>
          <input type="text" id="cariNama" class="form-control border-start-0 ps-0" placeholder="Cari nama anggota..." autocomplete="off">
        </div>
      </div>

      @if($belumSetor->isEmpty())
        <div class="text-center py-5 text-muted">
          <i class="bi bi-check2-circle d-block mb-2 is-942b1d4d"></i>
          <div>Semua anggota sudah setor bulan ini!</div>
        </div>
      @else
        <div class="table-responsive">
          <table class="koperasi-table">
            <thead>
              <tr>
                <th>No</th>
                <th>Nama Anggota</th>
                <th>No. Anggota</th>
                <th>Unit Kerja</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody id="belumSetorBody">
              @foreach($belumSetor as $i => $a)
              <tr>
                <td class="text-muted is-0de4e77b">{{ $i + 1 }}</td>
                <td>
                  <div class="fw-600">{{ $a->nama }}</div>
                  @if($a->telepon)
                    <div class="is-4aaa0b4b"><i class="bi bi-telephone me-1"></i>{{ $a->telepon }}</div>
                  @endif
                </td>
                <td><code class="is-0bf00d29">{{ $a->nomor_anggota }}</code></td>
                <td class="is-b0bb4131">{{ $a->unitKerja->nama ?? '—' }}</td>
                <td>
                  <a href="{{ route('simpanan.create') }}?anggota_id={{ $a->id }}" class="btn btn-xs btn-success" title="Input Simpanan">
                    <i class="bi bi-plus-circle"></i>
                  </a>
                  <a href="{{ route('anggota.show', $a) }}" class="btn btn-xs btn-outline-secondary" title="Detail Anggota">
                    <i class="bi bi-person"></i>
                  </a>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </div>
  </div>

  {{-- ─── SUDAH SETOR ─── --}}
  <div class="col-lg-5">
    <div class="form-card p-0 overflow-hidden">
      <div class="d-flex align-items-center gap-2 p-3 border-bottom is-1e862477"
       >
        <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0 is-e71c2790"
         >
          <i class="bi bi-check-circle-fill"></i>
        </div>
        <div>
          <h6 class="mb-0 fw-700 is-16cde0ce">Sudah Setor</h6>
          <small class="text-muted">{{ $namaBulan[$bulan] }} {{ $tahun }} · {{ $sudahSetor->count() }} anggota</small>
        </div>
      </div>

      @if($sudahSetor->isEmpty())
        <div class="text-center py-5 text-muted">
          <i class="bi bi-inbox d-block mb-2 is-a4ea9cc4"></i>
          <div class="is-1e28117f">Belum ada yang setor bulan ini</div>
        </div>
      @else
        <div class="table-responsive">
          <table class="koperasi-table">
            <thead>
              <tr>
                <th>Nama</th>
                <th class="text-end">Jumlah</th>
              </tr>
            </thead>
            <tbody>
              @foreach($sudahSetor as $a)
              <tr>
                <td>
                  <div class="fw-600 is-1e28117f">{{ $a->nama }}</div>
                  <div class="is-22508fb3">{{ $a->nomor_anggota }}</div>
                </td>
                <td class="text-end">
                  @foreach($a->simpanan as $s)
                    <div class="is-2edf9f0c">Rp {{ number_format($s->jumlah,0,',','.') }}</div>
                  @endforeach
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </div>
  </div>

</div>

@push('js')
<script src="{{ asset('js/pages/monitoring-simpanan.js') }}"></script>
@endpush

@endsection