<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Menu Utama — {{ $namaApp }}</title>
  <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Sora:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="{{ asset('css/pages/inline-extracted.css') }}">
  <style>
    *, *::before, *::after { box-sizing: border-box; }

    html, body {
      min-height: 100%;
      margin: 0;
      font-family: 'Plus Jakarta Sans', sans-serif !important;
      background: linear-gradient(135deg, #0d1b3e 0%, #112344 50%, #1a2f5a 100%);
    }

    .wrap {
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 2.5rem 1rem;
      max-width: 700px;
      margin: 0 auto;
    }

    .brand {
      text-align: center;
      margin-bottom: 2rem;
    }

    .brand h1 {
      font-size: 2.2rem;
      font-weight: 700;
      color: #ffffff;
      margin-bottom: .3rem;
      letter-spacing: -.5px;
    }

    .brand p {
      color: rgba(255,255,255,.6);
      font-size: .95rem;
      margin: 0;
      font-weight: 300;
    }

    .w-fit { width: fit-content; }

    .user-chip {
      display: inline-flex;
      align-items: center;
      gap: .45rem;
      background: rgba(255,255,255,.1);
      border: 1px solid rgba(255,255,255,.2);
      border-radius: 50px;
      padding: .45rem 1.1rem;
      font-size: .88rem;
      color: rgba(255,255,255,.9);
      font-weight: 500;
      backdrop-filter: blur(6px);
    }

    .user-chip i { color: #7eb3ff; font-size: 1.1rem; }
    .is-6aadd840 { color: rgba(255,255,255,.35); }
    .is-a192adcf {}

    .menu-card {
      display: block;
      border-radius: 1rem;
      padding: 2rem 1.75rem;
      text-decoration: none;
      height: 100%;
      transition: transform .2s, box-shadow .2s;
      position: relative;
      overflow: hidden;
    }

    .menu-card:hover { transform: translateY(-4px); }

    .menu-card-koperasi {
      background: linear-gradient(145deg, #175cdd, #1e4fc2);
      box-shadow: 0 8px 32px rgba(23,92,221,.45);
      color: #fff;
    }

    .menu-card-koperasi:hover {
      box-shadow: 0 14px 40px rgba(23,92,221,.6);
      color: #fff;
    }

    .menu-card-pos {
      background: linear-gradient(145deg, #b45309, #92400e);
      box-shadow: 0 8px 32px rgba(180,83,9,.45);
      color: #fff;
    }

    .menu-card-pos:hover {
      box-shadow: 0 14px 40px rgba(180,83,9,.6);
      color: #fff;
    }

    .menu-icon {
      width: 56px;
      height: 56px;
      border-radius: .75rem;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.6rem;
      margin-bottom: 1.1rem;
      background: rgba(255,255,255,.15);
      color: #fff;
    }

    .menu-title {
      font-size: 1.2rem;
      font-weight: 700;
      margin-bottom: .4rem;
      color: #fff;
    }

    .menu-desc {
      font-size: .85rem;
      color: rgba(255,255,255,.75);
      line-height: 1.55;
    }

    .user-info { margin-top: 2.5rem; text-align: center; }

    .is-871052bc {
      background: transparent;
      border: 1px solid rgba(255,255,255,.25);
      color: rgba(255,255,255,.65);
      border-radius: .4rem;
      padding: .45rem 1.4rem;
      font-size: .88rem;
      cursor: pointer;
      font-family: 'Plus Jakarta Sans', sans-serif;
      transition: background .15s, color .15s, border-color .15s;
    }

    .is-871052bc:hover {
      background: rgba(239,68,68,.15);
      border-color: rgba(239,68,68,.5);
      color: #fca5a5;
    }
  </style>
  
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
        <a href="{{ route('dashboard') }}" class="menu-card menu-card-koperasi">
          <div class="menu-icon">
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
        <a href="{{ route('pos.kasir') }}" class="menu-card menu-card-pos">
          <div class="menu-icon">
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
