@extends('layouts.pos')
@section('judul', $transaksi->nomor)
@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('pos.riwayat.index') }}">Riwayat Transaksi</a></li>
  <li class="breadcrumb-item active">{{ $transaksi->nomor }}</li>
@endsection

@section('konten')

<div class="d-flex justify-content-between align-items-center mb-4">
  <h5 class="fw-bold mb-0 is-feb6723d">
    <i class="bi bi-receipt me-2 is-f571fe9b"></i>Detail Transaksi
  </h5>
  <div class="d-flex gap-2">
    <a href="{{ route('pos.struk', $transaksi) }}" target="_blank" class="btn btn-sm btn-warning text-white fw-semibold">
      <i class="bi bi-printer me-1"></i>Cetak Struk
    </a>
    <a href="{{ route('pos.riwayat.index') }}" class="btn btn-sm btn-outline-secondary">
      <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
  </div>
</div>

{{-- Stat cards --}}
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="kstat-card kstat-amber">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <i class="kstat-icon bi bi-cash-coin"></i>
      <div class="kstat-value kstat-value-sm">Rp {{ number_format($transaksi->total, 0, ',', '.') }}</div>
      <div class="kstat-label">Total Bayar</div>
      <div class="kstat-trend"><i class="bi bi-receipt me-1"></i> {{ $transaksi->nomor }}</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="kstat-card kstat-green">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <i class="kstat-icon bi bi-arrow-return-left"></i>
      <div class="kstat-value kstat-value-sm">Rp {{ number_format($transaksi->kembalian, 0, ',', '.') }}</div>
      <div class="kstat-label">Kembalian</div>
      <div class="kstat-trend"><i class="bi bi-wallet me-1"></i> Bayar Rp {{ number_format($transaksi->bayar, 0, ',', '.') }}</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="kstat-card kstat-teal">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <i class="kstat-icon bi bi-box-seam"></i>
      <div class="kstat-value kstat-value-sm">{{ $transaksi->items->count() }}</div>
      <div class="kstat-label">Jenis Produk</div>
      <div class="kstat-trend"><i class="bi bi-list-ul me-1"></i> Item berbeda</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="kstat-card kstat-purple">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <i class="kstat-icon bi bi-calendar-event"></i>
      <div class="kstat-value kstat-value-sm">{{ $transaksi->tanggal->format('d M Y') }}</div>
      <div class="kstat-label">Tanggal</div>
      <div class="kstat-trend"><i class="bi bi-clock me-1"></i> {{ $transaksi->created_at->format('H:i') }} WIB</div>
    </div>
  </div>
</div>

<div class="row g-4">

  {{-- Info Transaksi --}}
  <div class="col-lg-4">
    <div class="form-card h-auto">
      <div class="text-center mb-4">
        <div class="mx-auto d-flex align-items-center justify-content-center mb-3 is-03278c73"
            >
          <i class="bi bi-receipt-cutoff"></i>
        </div>
        <div class="fw-700 fs-6 is-f571fe9b">{{ $transaksi->nomor }}</div>
        <div class="text-muted small mt-1">{{ $transaksi->tanggal->format('d M Y') }} • {{ $transaksi->created_at->format('H:i') }}</div>
      </div>

      <div class="d-flex flex-column gap-3 is-58180d3d">
        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
          <span class="text-muted"><i class="bi bi-person me-2"></i>Kasir</span>
          <span class="fw-600">{{ $transaksi->kasir?->name ?? '-' }}</span>
        </div>
        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
          <span class="text-muted"><i class="bi bi-credit-card me-2"></i>Metode</span>
          <span class="badge bg-primary-subtle text-primary fw-600">{{ strtoupper($transaksi->metode_bayar) }}</span>
        </div>
        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
          <span class="text-muted"><i class="bi bi-wallet2 me-2"></i>Bayar</span>
          <span class="fw-600">Rp {{ number_format($transaksi->bayar,0,',','.') }}</span>
        </div>
        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
          <span class="text-muted"><i class="bi bi-arrow-return-left me-2"></i>Kembalian</span>
          <span class="fw-600 text-success">Rp {{ number_format($transaksi->kembalian,0,',','.') }}</span>
        </div>
        @if($transaksi->diskon > 0)
        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
          <span class="text-muted"><i class="bi bi-tag me-2"></i>Diskon</span>
          <span class="fw-600 text-danger">- Rp {{ number_format($transaksi->diskon,0,',','.') }}</span>
        </div>
        @endif
        @if($transaksi->catatan)
        <div class="d-flex justify-content-between align-items-center py-2">
          <span class="text-muted"><i class="bi bi-chat-text me-2"></i>Catatan</span>
          <span class="fw-600 text-end is-f66b1f3f">{{ $transaksi->catatan }}</span>
        </div>
        @endif
      </div>

      <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
        <span class="text-muted small">Total Transaksi</span>
        <span class="fw-700 fs-5 is-feb6723d">Rp {{ number_format($transaksi->total,0,',','.') }}</span>
      </div>
    </div>
  </div>

  {{-- Rincian Item --}}
  <div class="col-lg-8">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white py-3 px-4 border-bottom">
        <h6 class="mb-0 fw-700 is-feb6723d">
          <i class="bi bi-list-ul me-2 is-f571fe9b"></i>Rincian Item
        </h6>
      </div>
      <div class="table-responsive">
        <table class="table tabel-koperasi mb-0">
          <thead class="is-98f358ac">
            <tr>
              <th>Produk</th>
              <th class="text-center">Qty</th>
              <th class="text-end">Harga</th>
              <th class="text-end">Subtotal</th>
            </tr>
          </thead>
          <tbody>
            @foreach($transaksi->items as $item)
            <tr>
              <td>
                <div class="fw-600">{{ $item->nama_produk }}</div>
                <div class="text-muted is-640fb1f6"><code>{{ $item->kode_produk }}</code></div>
              </td>
              <td class="text-center">
                <span class="badge bg-secondary-subtle text-secondary">{{ $item->qty }}</span>
              </td>
              <td class="text-end text-muted small">Rp {{ number_format($item->harga,0,',','.') }}</td>
              <td class="text-end fw-600 is-feb6723d">Rp {{ number_format($item->subtotal,0,',','.') }}</td>
            </tr>
            @endforeach
          </tbody>
          <tfoot class="is-a6836366">
            <tr>
              <td colspan="3" class="text-end text-muted small py-2">Subtotal</td>
              <td class="text-end small py-2">Rp {{ number_format($transaksi->subtotal,0,',','.') }}</td>
            </tr>
            @if($transaksi->diskon > 0)
            <tr>
              <td colspan="3" class="text-end text-danger small py-1">Diskon</td>
              <td class="text-end text-danger small py-1">- Rp {{ number_format($transaksi->diskon,0,',','.') }}</td>
            </tr>
            @endif
            <tr>
              <td colspan="3" class="text-end fw-700 py-3 is-fd91441e">TOTAL</td>
              <td class="text-end fw-700 py-3 is-365d82ed">Rp {{ number_format($transaksi->total,0,',','.') }}</td>
            </tr>
            <tr>
              <td colspan="3" class="text-end text-muted small py-1">Dibayar</td>
              <td class="text-end small py-1">Rp {{ number_format($transaksi->bayar,0,',','.') }}</td>
            </tr>
            <tr>
              <td colspan="3" class="text-end text-muted small py-1">Kembalian</td>
              <td class="text-end small py-1 text-success fw-600">Rp {{ number_format($transaksi->kembalian,0,',','.') }}</td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>

</div>

@endsection