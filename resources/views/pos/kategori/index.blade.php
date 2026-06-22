@extends('layouts.pos')
@section('judul','Kategori Produk')
@section('breadcrumb')<li class="breadcrumb-item active">Kategori</li>@endsection

@section('konten')

{{-- Header --}}
<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="fw-bold mb-0 is-feb6723d">
    <i class="bi bi-tags-fill me-2 is-f571fe9b"></i>Kategori Produk
  </h5>
  <div class="d-flex gap-2 flex-wrap">
    <a href="{{ route('pos.produk.index') }}#scanner-stok" class="btn btn-sm btn-success">
      <i class="bi bi-upc-scan me-1"></i>Scanner Stok
    </a>
    <a href="{{ route('pos.produk.index') }}" class="btn btn-sm btn-outline-secondary">
      <i class="bi bi-box-seam me-1"></i>Data Produk
    </a>
    <a href="{{ route('pos.toko.index') }}" class="btn btn-sm btn-outline-secondary">
      <i class="bi bi-truck me-1"></i>Supplier
    </a>
  </div>
</div>

{{-- Stats --}}
@php $totalProduk = $kategoris->sum('produk_count'); @endphp
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="kstat-card kstat-amber">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <i class="kstat-icon bi bi-tags"></i>
      <div class="kstat-value kstat-value-sm">{{ $kategoris->count() }}</div>
      <div class="kstat-label">Total Kategori</div>
      <div class="kstat-trend"><i class="bi bi-tags-fill me-1"></i> Semua kategori</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="kstat-card kstat-teal">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <i class="kstat-icon bi bi-box-seam"></i>
      <div class="kstat-value kstat-value-sm">{{ $totalProduk }}</div>
      <div class="kstat-label">Total Produk</div>
      <div class="kstat-trend"><i class="bi bi-box-seam-fill me-1"></i> Dalam kategori</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="kstat-card kstat-green">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <i class="kstat-icon bi bi-bar-chart"></i>
      <div class="kstat-value kstat-value-sm">{{ $kategoris->where('produk_count', '>', 0)->count() }}</div>
      <div class="kstat-label">Kategori Berisi</div>
      <div class="kstat-trend"><i class="bi bi-check-circle-fill me-1"></i> Ada produknya</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="kstat-card kstat-red">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <i class="kstat-icon bi bi-slash-circle"></i>
      <div class="kstat-value kstat-value-sm">{{ $kategoris->where('produk_count', 0)->count() }}</div>
      <div class="kstat-label">Kategori Kosong</div>
      <div class="kstat-trend"><i class="bi bi-exclamation me-1"></i> Belum ada produk</div>
    </div>
  </div>
</div>

<div class="row g-4">
  {{-- Form Tambah --}}
  <div class="col-md-4 col-lg-3">
    <div class="form-card h-auto">
      <h6 class="fw-700 mb-4 is-feb6723d">
        <i class="bi bi-plus-circle-fill me-2 is-f571fe9b"></i>Tambah Kategori
      </h6>


      <form method="POST" action="{{ route('pos.kategori.store') }}">
        @csrf
        <div class="mb-3">
          <label class="form-label fw-600">Nama Kategori <span class="text-danger">*</span></label>
          <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror"
                 value="{{ old('nama') }}" placeholder="cth. Minuman" required>
          @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-4">
          <label class="form-label fw-600">Urutan Tampil</label>
          <input type="number" name="urutan" class="form-control" value="{{ old('urutan', 0) }}" min="0">
          <div class="form-text">Angka kecil tampil lebih awal.</div>
        </div>
        <button type="submit" class="btn-simpan w-100">
          <i class="bi bi-plus-lg me-1"></i>Simpan Kategori
        </button>
      </form>
    </div>
  </div>

  {{-- Daftar Kategori --}}
  <div class="col-md-8 col-lg-9">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white d-flex align-items-center justify-content-between py-3 px-4 border-bottom">
        <h6 class="mb-0 fw-700 is-feb6723d">
          <i class="bi bi-list-ul me-2 is-f571fe9b"></i>Daftar Kategori
          <span class="badge ms-1 is-81903676">{{ $kategoris->count() }}</span>
        </h6>
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
          <thead class="is-98f358ac">
            <tr>
              <th>#</th>
              <th><a href="{{ $sUrl('nama') }}" class="text-decoration-none text-reset d-inline-flex align-items-center">Nama Kategori {!! $sIco('nama') !!}</a></th>
              <th class="text-center"><a href="{{ $sUrl('urutan') }}" class="text-decoration-none text-reset d-inline-flex align-items-center justify-content-center">Urutan {!! $sIco('urutan') !!}</a></th>
              <th class="text-center"><a href="{{ $sUrl('produk_count') }}" class="text-decoration-none text-reset d-inline-flex align-items-center justify-content-center">Jumlah Produk {!! $sIco('produk_count') !!}</a></th>
              <th class="text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($kategoris as $i => $kat)
            <tr>
              <td class="text-muted is-bb0425a5">{{ $i + 1 }}</td>
              <td>
                <div class="fw-600 is-feb6723d">{{ $kat->nama }}</div>
                <div class="text-muted is-640fb1f6">{{ $kat->slug }}</div>
              </td>
              <td class="text-center">
                <span class="badge bg-light text-dark border is-6c660c40">{{ $kat->urutan }}</span>
              </td>
              <td class="text-center">
                <span class="badge is-fs-08 {{ $kat->produk_count > 0 ? 'bg-primary-subtle text-primary' : 'bg-secondary-subtle text-secondary' }}">
                  {{ $kat->produk_count }} produk
                </span>
              </td>
              <td class="text-center">
                <button class="btn btn-sm btn-outline-warning py-0 px-2 me-1"
                        data-bs-toggle="modal" data-bs-target="#modalEdit{{ $kat->id }}" title="Edit">
                  <i class="bi bi-pencil"></i>
                </button>
                <form method="POST" action="{{ route('pos.kategori.destroy', $kat) }}" class="d-inline"
                      onsubmit="return confirm('Hapus kategori {{ addslashes($kat->nama) }}?')">
                  @csrf @method('DELETE')
                  <button class="btn btn-sm btn-outline-danger py-0 px-2" title="Hapus">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="5" class="text-center text-muted py-4">
                <i class="bi bi-tags is-a4ea9cc4"></i>
                <div class="mt-2">Belum ada kategori. Tambahkan kategori pertama!</div>
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

{{-- Modal Edit --}}
@foreach($kategoris as $kat)
<div class="modal fade" id="modalEdit{{ $kat->id }}" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-bottom is-6abcdaa6">
        <h5 class="modal-title fw-700 is-feb6723d">
          <i class="bi bi-pencil-square me-2 is-f571fe9b"></i>Edit Kategori
        </h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="{{ route('pos.kategori.update', $kat) }}">
        @csrf @method('PUT')
        <div class="modal-body p-4">
          <div class="mb-3">
            <label class="form-label fw-600">Nama Kategori <span class="text-danger">*</span></label>
            <input type="text" name="nama" class="form-control" value="{{ $kat->nama }}" required>
          </div>
          <div class="mb-1">
            <label class="form-label fw-600">Urutan Tampil</label>
            <input type="number" name="urutan" class="form-control" value="{{ $kat->urutan }}" min="0">
          </div>
        </div>
        <div class="modal-footer border-top">
          <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn-simpan">
            <i class="bi bi-check-lg me-1"></i>Simpan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endforeach
@endsection
