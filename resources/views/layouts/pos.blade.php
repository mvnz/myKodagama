<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('judul','POS') – {{ config('app.name') }}</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Montserrat:wght@300;400;500;600;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  {{-- Pakai koperasi.css agar komponen tabel, badge, stat-card konsisten --}}
  <link rel="stylesheet" href="{{ asset('css/koperasi.css') }}">
  <link rel="stylesheet" href="{{ asset('css/pages/inline-extracted.css') }}">
  
  @stack('css')
</head>
<body>

  <!-- Sidebar (struktur identik dengan koperasi) -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
      <h2><i class="bi bi-shop me-2"></i>Point of Sale</h2>
      <p class="mb-0 mt-1 is-9f375a76">{{ config('app.name') }}</p>
    </div>

    <nav class="nav-menu">
      @php
        $u = auth()->user();
        $bisaKasir = $u->hasPermission('pos.kasir');
        $bisaRiwayat = $u->hasPermission('pos.riwayat');
        $bisaProduk = $u->hasPermission('pos.produk');
        $bisaToko = $u->hasPermission('pos.toko');
      @endphp

      <div class="nav-section-title">Kasir</div>

      @if($bisaKasir)
      <div class="nav-item">
        <a href="{{ route('pos.kasir') }}"
           class="nav-link {{ request()->routeIs('pos.kasir') || request()->routeIs('pos.checkout') ? 'active' : '' }}">
          <i class="bi bi-cart3"></i> Kasir
        </a>
      </div>
      @endif

      @if($bisaRiwayat)
      <div class="nav-section-title">Laporan</div>
      <div class="nav-item">
        <a href="{{ route('pos.riwayat.index') }}"
           class="nav-link {{ request()->routeIs('pos.riwayat.*') ? 'active' : '' }}">
          <i class="bi bi-receipt"></i> Riwayat Transaksi
        </a>
      </div>
      <div class="nav-item">
        <a href="{{ route('pos.laporan') }}"
           class="nav-link {{ request()->routeIs('pos.laporan') ? 'active' : '' }}">
          <i class="bi bi-bar-chart-fill"></i> Laporan Penjualan
        </a>
      </div>
      @endif

      @if($bisaProduk)
      <div class="nav-section-title">Data Master</div>
      <div class="nav-item">
        <a href="{{ route('pos.produk.index') }}"
           class="nav-link {{ request()->routeIs('pos.produk.*') ? 'active' : '' }}">
          <i class="bi bi-box-seam-fill"></i> Produk
        </a>
      </div>
      <div class="nav-item">
        <a href="{{ route('pos.kategori.index') }}"
           class="nav-link {{ request()->routeIs('pos.kategori.*') ? 'active' : '' }}">
          <i class="bi bi-tags-fill"></i> Kategori
        </a>
      </div>
      @endif

      @if($bisaToko)
      <div class="nav-section-title">Toko</div>

      <div class="nav-item">
        <a href="{{ route('pos.toko.index') }}"
           class="nav-link {{ request()->routeIs('pos.toko.*') ? 'active' : '' }}">
          <i class="bi bi-truck"></i> Supplier
        </a>
      </div>
      @endif

      <div class="nav-section-title">Akun</div>

      <div class="nav-item">
        <a href="{{ route('home') }}" class="nav-link">
          <i class="bi bi-arrow-left-circle"></i> Menu Utama
        </a>
      </div>

      <div class="nav-item">
        <form action="{{ route('keluar') }}" method="POST">
          @csrf
          <button type="submit" class="nav-link w-100 text-start border-0 bg-transparent"
            onclick="return confirm('Yakin ingin keluar?')">
            <i class="bi bi-box-arrow-left"></i> Keluar
          </button>
        </form>
      </div>
    </nav>
  </aside>
  <!-- /Sidebar -->

  <!-- Main Content -->
  <div class="main-content">

    <!-- Top Nav -->
    <div class="topnav-app">
      <div class="d-flex align-items-center gap-3">
        <button class="btn btn-sm border-0 d-lg-none" id="sidebarToggle">
          <i class="bi bi-list fs-5"></i>
        </button>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route('pos.kasir') }}">POS</a></li>
            @yield('breadcrumb')
          </ol>
        </nav>
      </div>

      <div class="d-flex align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
          <div class="pos-avatar">
            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
          </div>
          <div class="d-none d-md-block">
            <div class="pos-user-name">{{ auth()->user()->name }}</div>
            <div class="is-a7e9f0b8">{{ auth()->user()->roleModel?->nama ?? auth()->user()->role }}</div>
          </div>
        </div>
      </div>
    </div>
    <!-- /Top Nav -->

    <!-- Alerts -->
    <div class="px-4 pt-3">
      @if(session('sukses'))
        <div class="alert-sukses mb-3">
          <i class="bi bi-check-circle-fill me-2"></i> {{ session('sukses') }}
        </div>
      @endif
      @if(session('galat'))
        <div class="alert-galat mb-3">
          <i class="bi bi-x-circle-fill me-2"></i> {{ session('galat') }}
        </div>
      @endif
      @if($errors->any())
        <div class="alert-galat mb-3">
          <i class="bi bi-exclamation-triangle-fill me-2"></i>
          @foreach($errors->all() as $err)<div>{{ $err }}</div>@endforeach
        </div>
      @endif
    </div>

    <!-- Content Area -->
    <div class="content-area">
      @yield('konten')
    </div>
    <!-- /Content Area -->

    <!-- Footer -->
    <div class="px-4 pb-3 mt-2 is-dbe41d39">
      {{ config('app.name') }} &mdash; Point of Sale
    </div>

  </div>
  <!-- /Main Content -->

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Sidebar toggle mobile
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    if (sidebarToggle && sidebar) {
      sidebarToggle.addEventListener('click', () => sidebar.classList.toggle('sidebar-open'));
    }
    // Auto-dismiss alerts
    document.querySelectorAll('.alert-sukses, .alert-galat').forEach(el => {
      setTimeout(() => {
        el.style.transition = 'opacity .5s';
        el.style.opacity = '0';
        setTimeout(() => el.remove(), 500);
      }, 4000);
    });
  </script>
  @stack('js')
</body>
</html>