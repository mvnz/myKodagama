@extends('layouts.pos')
@section('judul','Produk')
@section('breadcrumb')<li class="breadcrumb-item active">Produk</li>@endsection

@section('konten')
{{-- Page Banner --}}
<div class="dashboard-welcome mb-4 is-5d93e480">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
      <div class="welcome-label mb-1">Manajemen POS</div>
      <h4 class="mb-1 fw-700">Data Produk</h4>
      <p class="mb-0 welcome-date"><i class="bi bi-calendar3 me-1"></i>{{ now()->translatedFormat('l, d F Y') }}</p>
    </div>
    <div class="welcome-icon-wrap"><i class="bi bi-box-seam-fill"></i></div>
  </div>
</div>

{{-- Stat Cards --}}
<div class="row g-4 mb-4">
  <div class="col-xl-3 col-sm-6">
    <div class="kstat-card kstat-amber">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-box-seam"></i></div>
      <div class="kstat-value">{{ $stats->total ?? 0 }}</div>
      <div class="kstat-label">Total Produk</div>
      <div class="kstat-trend"><i class="bi bi-box me-1"></i> Semua produk</div>
    </div>
  </div>
  <div class="col-xl-3 col-sm-6">
    <div class="kstat-card kstat-green">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-check-circle-fill"></i></div>
      <div class="kstat-value">{{ $stats->aktif ?? 0 }}</div>
      <div class="kstat-label">Produk Aktif</div>
      <div class="kstat-trend"><i class="bi bi-check-circle-fill me-1"></i> Siap jual</div>
    </div>
  </div>
  <div class="col-xl-3 col-sm-6">
    <div class="kstat-card kstat-purple">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-pause-circle-fill"></i></div>
      <div class="kstat-value">{{ $stats->nonaktif ?? 0 }}</div>
      <div class="kstat-label">Non-aktif</div>
      <div class="kstat-trend"><i class="bi bi-pause me-1"></i> Dinonaktifkan</div>
    </div>
  </div>
  <div class="col-xl-3 col-sm-6">
    <div class="kstat-card kstat-red">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-exclamation-circle-fill"></i></div>
      <div class="kstat-value">{{ $stats->habis ?? 0 }}</div>
      <div class="kstat-label">Stok Habis</div>
      <div class="kstat-trend">
        @if(($stats->habis ?? 0) > 0)
          <i class="bi bi-exclamation-triangle-fill me-1"></i> Perlu restok
        @else
          <i class="bi bi-check-circle-fill me-1"></i> Semua tersedia
        @endif
      </div>
    </div>
  </div>
</div>

{{-- Filter --}}
<div class="form-card mb-4">
  <form method="GET" action="{{ route('pos.produk.index') }}" class="row g-3 align-items-end">
    <div class="col">
      <label class="form-label">Cari Produk</label>
      <div class="input-group">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="Cari nama, kode, atau barcode...">
      </div>
    </div>
    <div class="col-md-3">
      <label class="form-label">Kategori</label>
      <select name="kategori" class="form-select">
        <option value="">Semua Kategori</option>
        @foreach($kategoris as $kat)
        <option value="{{ $kat->id }}" {{ $katId == $kat->id ? 'selected':'' }}>{{ $kat->nama }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">Supplier</label>
      <select name="supplier" class="form-select">
        <option value="">Semua Supplier</option>
        @foreach($suppliers as $sup)
        <option value="{{ $sup->id }}" {{ ($supId ?? '') == $sup->id ? 'selected':'' }}>{{ $sup->nama }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-md-auto d-flex gap-2 align-items-end">
      <button type="submit" class="btn btn-primary">
        <i class="bi bi-funnel me-1"></i> Filter
      </button>
      @if(auth()->user()->hasPermission('pos.produk'))
      <a href="{{ route('pos.produk.create') }}" class="btn btn-success">
        <i class="bi bi-plus me-1"></i> Tambah
      </a>
      @endif
      @if($q || $katId || ($supId ?? null))
      <a href="{{ route('pos.produk.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-x-circle me-1"></i> Reset
      </a>
      @endif
    </div>
  </form>
</div>

<div class="form-card mb-4" id="scanner-stok">
  <form method="POST" action="{{ route('pos.produk.stok-scan') }}" class="row g-3 align-items-end" id="scanStockForm" autocomplete="off">
    @csrf
    <div class="col-md-5">
      <label class="form-label">Scan Barcode / Kode Produk</label>
      <div class="input-group">
        <span class="input-group-text"><i class="bi bi-upc-scan"></i></span>
        <input type="text" id="scanBarcodeInput" name="barcode" class="form-control" placeholder="Arahkan scanner lalu Enter" autofocus required>
      </div>
    </div>
    <div class="col-md-2">
      <label class="form-label">Qty Masuk</label>
      <input type="number" id="scanQtyInput" name="qty" min="1" value="1" class="form-control" required>
    </div>
    <div class="col-md-auto d-flex gap-2 align-items-end">
      <button type="submit" class="btn btn-success">
        <i class="bi bi-box-arrow-in-down me-1"></i> Tambah Stok
      </button>
    </div>
  </form>
  <div class="form-text mt-1">Mendukung scanner USB (keyboard wedge). Jika barcode kosong, bisa scan kode produk.</div>
  <div class="d-flex flex-wrap gap-2 mt-3">
    <a href="{{ route('pos.produk.create', old('barcode') ? ['barcode' => old('barcode')] : []) }}" class="btn btn-sm btn-outline-primary">
      <i class="bi bi-plus-circle me-1"></i>Tambah Produk Baru
    </a>
    <a href="{{ route('pos.kategori.index') }}" class="btn btn-sm btn-outline-secondary">
      <i class="bi bi-tags me-1"></i>Kelola Kategori
    </a>
    <a href="{{ route('pos.toko.index') }}" class="btn btn-sm btn-outline-secondary">
      <i class="bi bi-truck me-1"></i>Kelola Supplier
    </a>
  </div>
</div>

<div class="form-card p-0 overflow-hidden">
  <div class="p-4 border-bottom d-flex align-items-center justify-content-between">
    <h6 class="mb-0 fw-700 is-feb6723d">
      <i class="bi bi-box-seam-fill me-2 is-f571fe9b"></i>Daftar Produk
      <span class="badge ms-2 is-81903676">{{ $produk->total() }}</span>
    </h6>
    @if($q || $katId || ($supId ?? null))
    <a href="{{ route('pos.produk.index') }}" class="btn btn-sm btn-outline-secondary">
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
          <th><a href="{{ $sUrl('kode') }}" class="text-decoration-none text-reset d-inline-flex align-items-center">Kode {!! $sIco('kode') !!}</a></th>
          <th><a href="{{ $sUrl('barcode') }}" class="text-decoration-none text-reset d-inline-flex align-items-center">Barcode {!! $sIco('barcode') !!}</a></th>
          <th><a href="{{ $sUrl('nama') }}" class="text-decoration-none text-reset d-inline-flex align-items-center">Nama {!! $sIco('nama') !!}</a></th>
          <th>Kategori / Supplier</th>
          <th class="text-end"><a href="{{ $sUrl('harga_beli') }}" class="text-decoration-none text-reset d-inline-flex align-items-center justify-content-end">Harga Beli {!! $sIco('harga_beli') !!}</a></th>
          <th class="text-end"><a href="{{ $sUrl('harga_jual') }}" class="text-decoration-none text-reset d-inline-flex align-items-center justify-content-end">Harga Jual {!! $sIco('harga_jual') !!}</a></th>
          <th class="text-center"><a href="{{ $sUrl('stok') }}" class="text-decoration-none text-reset d-inline-flex align-items-center justify-content-center">Stok {!! $sIco('stok') !!}</a></th>
          <th class="text-center">Satuan</th>
          <th class="text-center"><a href="{{ $sUrl('is_aktif') }}" class="text-decoration-none text-reset d-inline-flex align-items-center justify-content-center">Status {!! $sIco('is_aktif') !!}</a></th>
          <th class="text-center">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($produk as $p)
        <tr>
          <td><code class="small">{{ $p->kode }}</code></td>
          <td>
            @if($p->barcode)
              <code class="small">{{ $p->barcode }}</code>
            @else
              <span class="text-muted small">-</span>
            @endif
          </td>
          <td class="fw-semibold">{{ $p->nama }}</td>
          <td>
            <div>{{ $p->kategori?->nama ?? '-' }}</div>
            @if($p->supplier)
              <div class="text-muted is-640fb1f6"><i class="bi bi-truck me-1"></i>{{ $p->supplier->nama }}</div>
            @endif
          </td>
          <td class="text-end">{{ number_format($p->harga_beli,0,',','.') }}</td>
          <td class="text-end fw-semibold">{{ number_format($p->harga_jual,0,',','.') }}</td>
          <td class="text-center">
            <span class="badge {{ $p->stok == 0 ? 'bg-danger' : ($p->stok <= 5 ? 'bg-warning text-dark':'bg-success') }}">
              {{ $p->stok }}
            </span>
          </td>
          <td class="text-center small text-muted">{{ $p->satuan }}</td>
          <td class="text-center">
            <span class="badge {{ $p->is_aktif ? 'bg-success-subtle text-success':'bg-secondary-subtle text-secondary' }}">
              {{ $p->is_aktif ? 'Aktif':'Non-aktif' }}
            </span>
          </td>
          <td class="text-center">
            <a href="{{ route('pos.produk.edit', $p) }}" class="btn btn-sm btn-outline-primary py-0 px-2">
              <i class="bi bi-pencil"></i>
            </a>
            <form method="POST" action="{{ route('pos.produk.destroy', $p) }}" class="d-inline"
                  onsubmit="return confirm('Hapus produk {{ $p->nama }}?')">
              @csrf @method('DELETE')
              <button class="btn btn-sm btn-outline-danger py-0 px-2"><i class="bi bi-trash"></i></button>
            </form>
          </td>
        </tr>
        @empty
        <tr><td colspan="10" class="text-center text-muted py-4">Tidak ada produk</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($produk->hasPages())
  <div class="p-3 border-top">{{ $produk->links() }}</div>
  @endif
</div>
@endsection

@push('js')
<script>
(function () {
  const form = document.getElementById('scanStockForm');
  const barcodeInput = document.getElementById('scanBarcodeInput');
  const qtyInput = document.getElementById('scanQtyInput');
  const FLAG = 'pos.scan.stock.pending';

  if (!form || !barcodeInput || !qtyInput) return;

  const focusScanInput = () => {
    barcodeInput.focus();
    barcodeInput.select();
  };

  // Keep focus in scanner field for high-throughput scanning.
  window.addEventListener('pageshow', focusScanInput);
  window.addEventListener('load', focusScanInput);

  barcodeInput.addEventListener('keydown', function (e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      form.requestSubmit();
    }
  });

  form.addEventListener('submit', function () {
    localStorage.setItem(FLAG, '1');
  });

  if (localStorage.getItem(FLAG) === '1') {
    localStorage.removeItem(FLAG);
    qtyInput.value = qtyInput.value || '1';
    focusScanInput();
  }
})();
</script>
@endpush
