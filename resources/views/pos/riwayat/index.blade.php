@extends('layouts.pos')
@section('judul','Riwayat Transaksi')
@section('breadcrumb')<li class="breadcrumb-item active">Riwayat</li>@endsection

@section('konten')
{{-- Page Banner --}}
<div class="dashboard-welcome mb-4 is-5d93e480">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
      <div class="welcome-label mb-1">Manajemen POS</div>
      <h4 class="mb-1 fw-700">Riwayat Transaksi</h4>
      <p class="mb-0 welcome-date"><i class="bi bi-calendar3 me-1"></i>{{ now()->translatedFormat('l, d F Y') }}</p>
    </div>
    <div class="welcome-icon-wrap"><i class="bi bi-receipt-cutoff"></i></div>
  </div>
</div>

{{-- Stats --}}
<div class="row g-4 mb-4">
  <div class="col-xl-4 col-sm-6">
    <div class="kstat-card kstat-amber">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-receipt-cutoff"></i></div>
      <div class="kstat-value">{{ number_format($stats->jumlah ?? 0) }}</div>
      <div class="kstat-label">Total Transaksi</div>
      <div class="kstat-trend"><i class="bi bi-receipt me-1"></i> Semua transaksi</div>
    </div>
  </div>
  <div class="col-xl-4 col-sm-6">
    <div class="kstat-card kstat-green">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-cash-stack"></i></div>
      <div class="kstat-value">Rp {{ number_format($stats->omzet ?? 0, 0, ',', '.') }}</div>
      <div class="kstat-label">Total Omzet</div>
      <div class="kstat-trend"><i class="bi bi-graph-up me-1"></i> Pendapatan kotor</div>
    </div>
  </div>
  <div class="col-xl-4 col-sm-6">
    <div class="kstat-card kstat-red">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-tag-fill"></i></div>
      <div class="kstat-value">Rp {{ number_format($stats->total_diskon ?? 0, 0, ',', '.') }}</div>
      <div class="kstat-label">Total Diskon</div>
      <div class="kstat-trend"><i class="bi bi-tag-fill me-1"></i> Potongan harga</div>
    </div>
  </div>
</div>

{{-- Filter --}}
<div class="form-card mb-4">
  <form method="GET" action="{{ route('pos.riwayat.index') }}" class="row g-3 align-items-end">
    <div class="col-md-3">
      <label class="form-label">Dari Tanggal</label>
      <input type="date" name="dari" value="{{ $dari }}" class="form-control">
    </div>
    <div class="col-md-3">
      <label class="form-label">Sampai Tanggal</label>
      <input type="date" name="sampai" value="{{ $sampai }}" class="form-control">
    </div>
    <div class="col">
      <label class="form-label">Cari Transaksi</label>
      <div class="input-group">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="Cari nomor transaksi...">
      </div>
    </div>
    <div class="col-md-auto d-flex gap-2 align-items-end">
      <button type="submit" class="btn btn-primary">
        <i class="bi bi-funnel me-1"></i> Filter
      </button>
      @if($dari || $sampai || $q)
      <a href="{{ route('pos.riwayat.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-x-circle me-1"></i> Reset
      </a>
      @endif
    </div>
  </form>
</div>

<div class="form-card p-0 overflow-hidden">
  <div class="p-4 border-bottom d-flex align-items-center justify-content-between">
    <h6 class="mb-0 fw-700 is-feb6723d">
      <i class="bi bi-receipt-cutoff me-2 is-f571fe9b"></i>Daftar Transaksi
      <span class="badge ms-2 is-81903676">{{ $transaksi->total() }}</span>
    </h6>
    @if($dari || $sampai || $q)
    <a href="{{ route('pos.riwayat.index') }}" class="btn btn-sm btn-outline-secondary">
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
          <th><a href="{{ $sUrl('nomor') }}" class="text-decoration-none text-reset d-inline-flex align-items-center">Nomor {!! $sIco('nomor') !!}</a></th>
          <th><a href="{{ $sUrl('tanggal') }}" class="text-decoration-none text-reset d-inline-flex align-items-center">Tanggal {!! $sIco('tanggal') !!}</a></th>
          <th>Kasir</th>
          <th class="text-end">Subtotal</th>
          <th class="text-end">Diskon</th>
          <th class="text-end"><a href="{{ $sUrl('total') }}" class="text-decoration-none text-reset d-inline-flex align-items-center justify-content-end">Total {!! $sIco('total') !!}</a></th>
          <th class="text-center"><a href="{{ $sUrl('metode_bayar') }}" class="text-decoration-none text-reset d-inline-flex align-items-center justify-content-center">Metode {!! $sIco('metode_bayar') !!}</a></th>
          <th class="text-center">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($transaksi as $t)
        <tr>
          <td><code>{{ $t->nomor }}</code></td>
          <td>{{ $t->tanggal->format('d/m/Y') }}</td>
          <td>{{ $t->kasir?->name ?? '-' }}</td>
          <td class="text-end">{{ number_format($t->subtotal,0,',','.') }}</td>
          <td class="text-end text-danger">{{ $t->diskon > 0 ? '-'.number_format($t->diskon,0,',','.') : '-' }}</td>
          <td class="text-end fw-semibold">{{ number_format($t->total,0,',','.') }}</td>
          <td class="text-center">
            <span class="badge {{ $t->metode_bayar=='tunai'?'bg-success-subtle text-success':($t->metode_bayar=='qris'?'bg-primary-subtle text-primary':'bg-info-subtle text-info') }}">
              {{ strtoupper($t->metode_bayar) }}
            </span>
          </td>
          <td class="text-center">
            <a href="{{ route('pos.riwayat.show', $t) }}" class="btn btn-sm btn-outline-primary py-0 px-2"><i class="bi bi-eye"></i></a>
            <a href="{{ route('pos.struk', $t) }}" target="_blank" class="btn btn-sm btn-outline-secondary py-0 px-2"><i class="bi bi-printer"></i></a>
          </td>
        </tr>
        @empty
        <tr><td colspan="8" class="text-center text-muted py-4">Tidak ada transaksi pada periode ini</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($transaksi->hasPages())
  <div class="p-3 border-top">{{ $transaksi->links() }}</div>
  @endif
</div>
@endsection
