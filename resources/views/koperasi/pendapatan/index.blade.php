@extends('layouts.aplikasi')

@section('judul', 'Pendapatan')

@section('breadcrumb')
  <li class="breadcrumb-item active">Pendapatan</li>
@endsection

@section('konten')

@if(session('sukses'))
  <div class="alert alert-success alert-dismissible fade show py-2">{{ session('sukses') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
@endif

{{-- Stat cards --}}
<div class="row g-3 mb-4">
  <div class="col-sm-6 col-lg-4">
    <div class="kstat-card kstat-green">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-graph-up-arrow"></i></div>
      <div class="kstat-value">Rp {{ number_format($totalTahun, 0, ',', '.') }}</div>
      <div class="kstat-label">Total Pendapatan {{ $tahun }}</div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-4">
    <div class="kstat-card kstat-blue">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-funnel"></i></div>
      <div class="kstat-value">Rp {{ number_format($totalFilter, 0, ',', '.') }}</div>
      <div class="kstat-label">Total Filter Aktif</div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-4">
    <div class="kstat-card kstat-amber">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-list-ul"></i></div>
      <div class="kstat-value">{{ $pendapatan->total() }}</div>
      <div class="kstat-label">Jumlah Transaksi</div>
    </div>
  </div>
</div>

{{-- Filter --}}
<div class="form-card mb-4">
  <form method="GET" class="row g-3 align-items-end">
    <div class="col-md-2">
      <label class="form-label">Tahun</label>
      <select name="tahun" class="form-select">
        @for($y = date('Y'); $y >= 2020; $y--)
          <option value="{{ $y }}" @selected($tahun == $y)>{{ $y }}</option>
        @endfor
      </select>
    </div>
    <div class="col-md-2">
      <label class="form-label">Bulan</label>
      <select name="bulan" class="form-select">
        <option value="">Semua</option>
        @foreach(['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'] as $m => $nm)
          <option value="{{ $m }}" @selected($bulan == $m)>{{ $nm }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label">Kategori</label>
      <select name="kategori" class="form-select">
        <option value="">Semua</option>
        @foreach($labelKategori as $k => $l)
          <option value="{{ $k }}" @selected($kategori == $k)>{{ $l }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-md-auto d-flex gap-2 align-items-end">
      <button type="submit" class="btn btn-primary">
        <i class="bi bi-funnel me-1"></i> Filter
      </button>
      @can('pendapatan.kelola')
      <a href="{{ route('pendapatan.create') }}" class="btn btn-success">
        <i class="bi bi-plus me-1"></i> Tambah
      </a>
      @endcan
      <a href="{{ route('pendapatan.cetak', array_filter(request()->only(['tahun','bulan','kategori']))) }}" class="btn btn-outline-secondary" target="_blank">
        <i class="bi bi-printer me-1"></i> Cetak
      </a>
    </div>
  </form>
</div>

<div class="row g-4">

  {{-- Rekap Kategori --}}
  @if($rekapKategori->isNotEmpty())
  <div class="col-lg-4">
    <div class="form-card">
      <h6 class="fw-700 mb-3 is-5ca88772">Rekap Kategori {{ $tahun }}</h6>
      @foreach($rekapKategori->sortByDesc(fn($v) => $v) as $kat => $total)
        @php $label = $labelKategori[$kat] ?? $kat; @endphp
        <div class="d-flex justify-content-between align-items-center py-1 border-bottom is-1e28117f">
          <span>{{ $label }}</span>
          <span class="fw-600 text-success">Rp {{ number_format($total, 0, ',', '.') }}</span>
        </div>
      @endforeach
    </div>
  </div>
  @endif

  <div class="{{ $rekapKategori->isNotEmpty() ? 'col-lg-8' : 'col-12' }}">

    {{-- Tabel --}}
    <div class="form-card p-0 overflow-hidden">
      <div class="table-responsive">
        @php
          $sUrl = fn($k) => request()->fullUrlWithQuery(['sort' => $k, 'dir' => ($sortBy===$k && $sortDir==='asc') ? 'desc' : 'asc']);
          $sIco = fn($k) => $sortBy===$k
            ? ($sortDir==='asc'
                ? '<i class="bi bi-sort-up ms-1 is-0a789cad"></i>'
                : '<i class="bi bi-sort-down ms-1 is-0a789cad"></i>')
            : '<i class="bi bi-arrow-down-up text-muted opacity-50 ms-1 is-bc9efe64"></i>';
        @endphp
        <table class="koperasi-table w-100">
          <thead>
            <tr>
              <th><a href="{{ $sUrl('tanggal') }}" class="text-decoration-none text-reset d-inline-flex align-items-center">Tanggal {!! $sIco('tanggal') !!}</a></th>
              <th><a href="{{ $sUrl('judul') }}" class="text-decoration-none text-reset d-inline-flex align-items-center">Judul {!! $sIco('judul') !!}</a></th>
              <th><a href="{{ $sUrl('kategori') }}" class="text-decoration-none text-reset d-inline-flex align-items-center">Kategori {!! $sIco('kategori') !!}</a></th>
              <th class="text-end"><a href="{{ $sUrl('jumlah') }}" class="text-decoration-none text-reset d-inline-flex align-items-center justify-content-end">Jumlah {!! $sIco('jumlah') !!}</a></th>
              <th>Dicatat</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @forelse($pendapatan as $p)
            <tr>
              <td>
                <span class="is-7e7d71c3">
                  <i class="bi bi-calendar3 is-3b8e2be5"></i>
                  {{ \Carbon\Carbon::parse($p->tanggal)->isoFormat('D MMM Y') }}
                </span>
              </td>
              <td>{{ $p->judul }}</td>
              <td><span class="badge bg-success-subtle text-success-emphasis">{{ $p->labelKategori }}</span></td>
              <td class="text-end fw-600">Rp {{ number_format($p->jumlah, 0, ',', '.') }}</td>
              <td class="text-muted small">{{ $p->pencatat?->name ?? '-' }}</td>
              <td class="text-nowrap">
                <a href="{{ route('pendapatan.show', $p) }}" class="btn btn-outline-primary btn-xs py-0 px-2" title="Detail"><i class="bi bi-eye"></i></a>
                @can('pendapatan.kelola')
                <a href="{{ route('pendapatan.edit', $p) }}" class="btn btn-outline-secondary btn-xs py-0 px-2" title="Edit"><i class="bi bi-pencil"></i></a>
                <form action="{{ route('pendapatan.destroy', $p) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Hapus data ini?')">
                  @csrf @method('DELETE')
                  <button class="btn btn-outline-danger btn-xs py-0 px-2" title="Hapus"><i class="bi bi-trash"></i></button>
                </form>
                @endcan
              </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center text-muted py-4">Belum ada data pendapatan.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <div class="mt-3">{{ $pendapatan->links() }}</div>

  </div>
</div>

@endsection
