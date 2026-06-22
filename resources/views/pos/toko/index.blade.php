@extends('layouts.pos')
@section('judul','Supplier')
@section('breadcrumb')<li class="breadcrumb-item active">Supplier</li>@endsection

@section('konten')

{{-- Page Banner --}}
<div class="dashboard-welcome mb-4 is-5d93e480">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
      <div class="welcome-label mb-1">Manajemen POS</div>
      <h4 class="mb-1 fw-700">Data Supplier</h4>
      <p class="mb-0 welcome-date"><i class="bi bi-calendar3 me-1"></i>{{ now()->translatedFormat('l, d F Y') }}</p>
    </div>
    <div class="welcome-icon-wrap"><i class="bi bi-truck"></i></div>
  </div>
</div>

<div class="d-flex gap-2 flex-wrap mb-4">
  <a href="{{ route('pos.produk.index') }}#scanner-stok" class="btn btn-sm btn-success">
    <i class="bi bi-upc-scan me-1"></i>Scanner Stok
  </a>
  <a href="{{ route('pos.produk.index') }}" class="btn btn-sm btn-outline-secondary">
    <i class="bi bi-box-seam me-1"></i>Data Produk
  </a>
  <a href="{{ route('pos.kategori.index') }}" class="btn btn-sm btn-outline-secondary">
    <i class="bi bi-tags me-1"></i>Kategori
  </a>
</div>

{{-- Stats --}}
<div class="row g-4 mb-4">
  <div class="col-xl-3 col-sm-6">
    <div class="kstat-card kstat-amber">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-truck"></i></div>
      <div class="kstat-value">{{ $suppliers->total() }}</div>
      <div class="kstat-label">Total Supplier</div>
      <div class="kstat-trend"><i class="bi bi-truck me-1"></i> Mitra terdaftar</div>
    </div>
  </div>
  <div class="col-xl-3 col-sm-6">
    <div class="kstat-card kstat-teal">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-box-seam"></i></div>
      <div class="kstat-value">{{ $suppliers->sum('produk_count') }}</div>
      <div class="kstat-label">Total Produk</div>
      <div class="kstat-trend"><i class="bi bi-box-seam-fill me-1"></i> Dari semua supplier</div>
    </div>
  </div>
  <div class="col-xl-3 col-sm-6">
    <div class="kstat-card kstat-green">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-wallet2"></i></div>
      <div class="kstat-value">Rp {{ number_format($suppliers->sum('saldo'), 0, ',', '.') }}</div>
      <div class="kstat-label">Total Saldo</div>
      <div class="kstat-trend"><i class="bi bi-cash-stack me-1"></i> Saldo aktif</div>
    </div>
  </div>
  <div class="col-xl-3 col-sm-6">
    <div class="kstat-card kstat-red">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-slash-circle"></i></div>
      <div class="kstat-value">{{ $suppliers->where('produk_count', 0)->count() }}</div>
      <div class="kstat-label">Tanpa Produk</div>
      <div class="kstat-trend"><i class="bi bi-exclamation me-1"></i> Perlu ditambah produk</div>
    </div>
  </div>
</div>

{{-- Filter --}}
<div class="form-card mb-4">
  <form method="GET" action="{{ route('pos.toko.index') }}" class="row g-3 align-items-end">
    <div class="col">
      <label class="form-label">Cari Supplier</label>
      <div class="input-group">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="Cari nama, pemilik, atau kota...">
      </div>
    </div>
    <div class="col-md-auto d-flex gap-2 align-items-end">
      <button type="submit" class="btn btn-primary">
        <i class="bi bi-funnel me-1"></i> Filter
      </button>
      <a href="{{ route('pos.toko.create') }}" class="btn btn-success">
        <i class="bi bi-plus me-1"></i> Tambah Supplier
      </a>
      @if($q)
      <a href="{{ route('pos.toko.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-x-circle me-1"></i> Reset
      </a>
      @endif
    </div>
  </form>
</div>

<div class="form-card p-0 overflow-hidden">
  <div class="p-4 border-bottom d-flex align-items-center justify-content-between">
    <h6 class="mb-0 fw-700 is-feb6723d">
      <i class="bi bi-truck me-2 is-f571fe9b"></i>Daftar Supplier
      <span class="badge ms-2 is-81903676">{{ $suppliers->total() }}</span>
    </h6>
    @if($q)
    <a href="{{ route('pos.toko.index') }}" class="btn btn-sm btn-outline-secondary">
      <i class="bi bi-x-circle me-1"></i> Reset Filter
    </a>
    @endif
  </div>
  <div class="table-responsive">
    @php
      $sUrl = fn($k) => request()->fullUrlWithQuery(['sort' => $k, 'dir' => ($sortBy===$k && $sortDir==='asc') ? 'desc' : 'asc']);
      $sIco = fn($k) => $sortBy===$k
        ? ($sortDir==='asc'
            ? '<i class="bi bi-sort-up ms-1 is-47a921fc"></i>'
            : '<i class="bi bi-sort-down ms-1 is-47a921fc"></i>')
        : '<i class="bi bi-arrow-down-up text-muted opacity-50 ms-1 is-bc9efe64"></i>';
    @endphp
    <table class="table tabel-koperasi mb-0">
      <thead>
        <tr>
          <th>#</th>
          <th><a href="{{ $sUrl('nama') }}" class="text-decoration-none text-reset d-inline-flex align-items-center">Nama Supplier {!! $sIco('nama') !!}</a></th>
          <th><a href="{{ $sUrl('pemilik') }}" class="text-decoration-none text-reset d-inline-flex align-items-center">Pemilik {!! $sIco('pemilik') !!}</a></th>
          <th>Kontak</th>
          <th class="text-center">Produk</th>
          <th class="text-end"><a href="{{ $sUrl('saldo') }}" class="text-decoration-none text-reset d-inline-flex align-items-center justify-content-end">Saldo {!! $sIco('saldo') !!}</a></th>
          <th class="text-center">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($suppliers as $sup)
        <tr>
          <td class="text-muted is-bb0425a5">{{ $suppliers->firstItem() + $loop->index }}</td>
          <td>
            <a href="{{ route('pos.toko.show', $sup) }}" class="fw-600 text-decoration-none is-feb6723d">
              {{ $sup->nama }}
            </a>
            @if($sup->kota)
              <div class="text-muted is-640fb1f6"><i class="bi bi-geo-alt me-1"></i>{{ $sup->kota }}</div>
            @endif
          </td>
          <td>{{ $sup->pemilik ?? '—' }}</td>
          <td class="is-9ee0bb07">
            @if($sup->telepon)<div><i class="bi bi-telephone me-1 text-muted"></i>{{ $sup->telepon }}</div>@endif
            @if($sup->email)<div><i class="bi bi-envelope me-1 text-muted"></i>{{ $sup->email }}</div>@endif
            @if(!$sup->telepon && !$sup->email)<span class="text-muted">—</span>@endif
          </td>
          <td class="text-center">
            <span class="badge {{ $sup->produk_count > 0 ? 'bg-primary-subtle text-primary' : 'bg-secondary-subtle text-secondary' }}">
              {{ $sup->produk_count }}
            </span>
          </td>
          <td class="text-end fw-600 is-feb6723d">
            Rp {{ number_format($sup->saldo, 0, ',', '.') }}
          </td>
          <td class="text-center">
            <a href="{{ route('pos.toko.show', $sup) }}" class="btn btn-sm btn-outline-primary py-0 px-2 me-1" title="Detail">
              <i class="bi bi-eye"></i>
            </a>
            <a href="{{ route('pos.toko.edit', $sup) }}" class="btn btn-sm btn-outline-warning py-0 px-2 me-1" title="Edit">
              <i class="bi bi-pencil"></i>
            </a>
            <form method="POST" action="{{ route('pos.toko.destroy', $sup) }}" class="d-inline"
                  onsubmit="return confirm('Hapus supplier {{ addslashes($sup->nama) }}?')">
              @csrf @method('DELETE')
              <button class="btn btn-sm btn-outline-danger py-0 px-2" title="Hapus">
                <i class="bi bi-trash"></i>
              </button>
            </form>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="7" class="text-center text-muted py-4">
            <i class="bi bi-truck is-a4ea9cc4"></i>
            <div class="mt-2">Belum ada supplier. <a href="{{ route('pos.toko.create') }}" class="text-warning">Tambah sekarang</a></div>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($suppliers->hasPages())
  <div class="p-3 border-top">{{ $suppliers->links() }}</div>
  @endif
</div>

@endsection
