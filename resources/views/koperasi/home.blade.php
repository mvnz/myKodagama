<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Menu Utama — {{ $namaApp }}</title>
  <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  
  <link rel="stylesheet" href="{{ asset('css/pages/inline-extracted.css') }}">
</head>
<body>
  <div class="wrap">
    <div class="brand">
      <h1>{{ $namaApp }}</h1>
      <p>{{ $subNama }}</p>
    </div>

    <div class="user-chip mx-auto d-block w-fit mb-4 is-a192adcf">
      <i class="bi bi-person-circle"></i>
      {{ auth()->user()->name }}
      <span class="is-6aadd840">·</span>
      <span>{{ auth()->user()->roleModel?->nama ?? auth()->user()->role }}</span>
    </div>

    <div class="row g-4">
      {{-- Koperasi --}}
      <div class="col-sm-6">
        <a href="{{ route('dashboard') }}" class="menu-card">
          <div class="menu-icon is-1c7ce959">
            <i class="bi bi-bank2"></i>
          </div>
          <div class="menu-title">Koperasi</div>
          <div class="menu-desc">Kelola simpanan, pinjaman, angsuran, laporan keuangan, dan data anggota.</div>
        </a>
      </div>

      {{-- POS --}}
      @php
        $bisaAksesPos = auth()->user()?->hasPermission('pos.kasir')
          || auth()->user()?->hasPermission('pos.riwayat')
          || auth()->user()?->hasPermission('pos.produk')
          || auth()->user()?->hasPermission('pos.toko');
      @endphp
      @if($bisaAksesPos)
      <div class="col-sm-6">
        <a href="{{ route('pos.kasir') }}" class="menu-card">
          <div class="menu-icon is-314e93f7">
            <i class="bi bi-shop"></i>
          </div>
          <div class="menu-title">Point of Sale</div>
          <div class="menu-desc">Kasir, transaksi penjualan, manajemen produk dan stok toko.</div>
        </a>
      </div>
      @endif
    </div>

    <div class="user-info">
      <form method="POST" action="{{ route('keluar') }}" class="d-inline">
        @csrf
        <button type="submit" class="is-871052bc">
          <i class="bi bi-box-arrow-right me-1"></i>Keluar
        </button>
      </form>
    </div>
  </div>
</body>
</html>
