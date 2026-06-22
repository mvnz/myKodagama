@extends('layouts.aplikasi')

@section('judul', 'Unit Kerja')

@section('konten')

{{-- Page Banner --}}
<div class="dashboard-welcome mb-4">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
      <div class="welcome-label mb-1">Manajemen Organisasi</div>
      <h4 class="mb-1 fw-700">Unit Kerja</h4>
      <p class="mb-0 welcome-date">
        <i class="bi bi-calendar3 me-1"></i>
        {{ now()->translatedFormat('l, d F Y') }}
      </p>
    </div>
    <div class="welcome-icon-wrap">
      <i class="bi bi-building-fill"></i>
    </div>
  </div>
</div>

{{-- Stat Cards --}}
<div class="row g-4 mb-4">
  <div class="col-xl-3 col-sm-6">
    <div class="kstat-card kstat-blue">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-building-fill"></i></div>
      <div class="kstat-value">{{ number_format($statsUnitKerja['total']) }}</div>
      <div class="kstat-label">Total Unit Kerja</div>
      <div class="kstat-trend"><i class="bi bi-diagram-3-fill me-1"></i> Semua divisi</div>
    </div>
  </div>
  <div class="col-xl-3 col-sm-6">
    <div class="kstat-card kstat-green">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-building-check"></i></div>
      <div class="kstat-value">{{ number_format($statsUnitKerja['aktif']) }}</div>
      <div class="kstat-label">Unit Aktif</div>
      <div class="kstat-trend"><i class="bi bi-check-circle-fill me-1"></i> Sedang beroperasi</div>
    </div>
  </div>
  <div class="col-xl-3 col-sm-6">
    <div class="kstat-card kstat-amber">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-building-slash"></i></div>
      <div class="kstat-value">{{ number_format($statsUnitKerja['tidak_aktif']) }}</div>
      <div class="kstat-label">Tidak Aktif</div>
      <div class="kstat-trend"><i class="bi bi-pause-circle-fill me-1"></i> Nonaktif</div>
    </div>
  </div>
  <div class="col-xl-3 col-sm-6">
    <div class="kstat-card kstat-red">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-calendar-plus-fill"></i></div>
      <div class="kstat-value">{{ number_format($statsUnitKerja['terbaru']) }}</div>
      <div class="kstat-label">Baru Bulan Ini</div>
      <div class="kstat-trend"><i class="bi bi-calendar-month me-1"></i> {{ now()->translatedFormat('F Y') }}</div>
    </div>
  </div>
</div>

<div class="form-card mb-4">
  <form method="GET" action="{{ route('unit-kerja.index') }}" class="row g-3 align-items-end">
    <div class="col">
      <label class="form-label">Cari Unit Kerja</label>
      <div class="input-group">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input type="text" name="cari" class="form-control" placeholder="Nama, kode, atau singkatan..."
          value="{{ $cari ?? '' }}">
      </div>
    </div>
    <div class="col-md-auto d-flex gap-2 align-items-end">
      <button type="submit" class="btn btn-primary">
        <i class="bi bi-funnel me-1"></i> Filter
      </button>
      <a href="{{ route('unit-kerja.create') }}" class="btn btn-success">
        <i class="bi bi-plus me-1"></i> Tambah
      </a>
      <a href="{{ route('import.unit-kerja.form') }}" class="btn btn-warning">
        <i class="bi bi-box-arrow-in-down me-1"></i> Import
      </a>
    </div>
  </form>
</div>

@if(session('sukses'))
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle-fill me-2"></i>{{ session('sukses') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
@endif

<div class="form-card p-0 overflow-hidden">
  <div class="p-4 border-bottom d-flex align-items-center justify-content-between">
    <h6 class="mb-0 fw-700 is-5ca88772">
      <i class="bi bi-building-fill me-2 is-bc519fd0"></i>Daftar Unit Kerja
      <span class="badge bg-primary ms-2">{{ $unitKerja->total() }}</span>
    </h6>
    @if($cari)
      <a href="{{ route('unit-kerja.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-x-circle me-1"></i> Reset
      </a>
    @endif
  </div>
  <div class="table-responsive">
    @php
      $sUrl = fn($k) => request()->fullUrlWithQuery(['sort' => $k, 'dir' => ($sortBy===$k && $sortDir==='asc') ? 'desc' : 'asc']);
      $sIco = fn($k) => $sortBy===$k
        ? ($sortDir==='asc'
            ? '<i class="bi bi-sort-up ms-1 is-0a789cad"></i>'
            : '<i class="bi bi-sort-down ms-1 is-0a789cad"></i>')
        : '<i class="bi bi-arrow-down-up text-muted opacity-50 ms-1 is-bc9efe64"></i>';
    @endphp
    <table class="koperasi-table mb-0">
      <thead>
        <tr>
          <th>#</th>
          <th><a href="{{ $sUrl('kode') }}" class="text-decoration-none text-reset d-inline-flex align-items-center">Kode {!! $sIco('kode') !!}</a></th>
          <th><a href="{{ $sUrl('nama') }}" class="text-decoration-none text-reset d-inline-flex align-items-center">Nama Unit Kerja {!! $sIco('nama') !!}</a></th>
          <th><a href="{{ $sUrl('singkatan') }}" class="text-decoration-none text-reset d-inline-flex align-items-center">Singkatan {!! $sIco('singkatan') !!}</a></th>
          <th>Keterangan</th>
          <th class="text-center"><a href="{{ $sUrl('aktif') }}" class="text-decoration-none text-reset d-inline-flex align-items-center justify-content-center">Status {!! $sIco('aktif') !!}</a></th>
          <th class="text-center">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($unitKerja as $item)
          <tr>
            <td class="text-muted">{{ $unitKerja->firstItem() + $loop->index }}</td>
            <td><code>{{ $item->kode }}</code></td>
            <td>
              <div class="is-6467c5ff">{{ $item->nama }}</div>
            </td>
            <td>{{ $item->singkatan ?? '-' }}</td>
            <td class="text-muted is-1ecf141d">
              {{ Str::limit($item->keterangan, 60) ?? '-' }}
            </td>
            <td class="text-center">
              <span class="badge-status {{ $item->aktif ? 'badge-aktif' : 'badge-tidak' }}">
                {{ $item->aktif ? 'Aktif' : 'Tidak Aktif' }}
              </span>
            </td>
            <td>
              <div class="d-flex gap-1 justify-content-center">
                <a href="{{ route('unit-kerja.edit', $item) }}" class="btn btn-sm btn-outline-warning" title="Ubah">
                  <i class="bi bi-pencil"></i>
                </a>
                <form action="{{ route('unit-kerja.destroy', $item) }}" method="POST" class="d-inline"
                  onsubmit="return confirm('Hapus unit kerja {{ $item->nama }}?')">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="text-center py-5 text-muted">
              <i class="bi bi-building display-4 d-block mb-2 opacity-25"></i>
              Belum ada unit kerja
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($unitKerja->hasPages())
    <div class="p-4 border-top">
      {{ $unitKerja->withQueryString()->links() }}
    </div>
  @endif
</div>
@endsection
