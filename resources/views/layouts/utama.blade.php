<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('judul', 'Koda+')</title>
  <meta name="description" content="@yield('deskripsi', 'Koda+ - Sistem Manajemen Koperasi')">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Sora:wght@400;500;600;700&display=swap" rel="stylesheet">

  <!-- Bootstrap 5 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <!-- CSS Koperasi -->
  <link rel="stylesheet" href="{{ asset('css/koperasi.css') }}">
  <link rel="stylesheet" href="{{ asset('css/pages/inline-extracted.css') }}">

  @stack('css')
</head>
<body>

  <!-- Top Bar -->
  <div class="topbar">
    <div class="container d-flex align-items-center justify-content-between h-100">
      <div class="d-flex align-items-center gap-3">
        <span><i class="bi bi-telephone-fill me-1"></i> (0274) 123-4567</span>
        <span class="d-none d-md-inline"><i class="bi bi-envelope-fill me-1"></i> info@koperasi-kodagama.id</span>
      </div>
      <div class="d-flex align-items-center gap-3">
        @auth
          <a href="{{ route('dashboard') }}" class="d-flex align-items-center gap-1">
            <i class="bi bi-grid-fill"></i> Dashboard
          </a>
        @else
          <a href="{{ route('masuk') }}" class="d-flex align-items-center gap-1">
            <i class="bi bi-box-arrow-in-right"></i> Masuk
          </a>
        @endauth
        <a href="#"><i class="bi bi-facebook"></i></a>
        <a href="#"><i class="bi bi-instagram"></i></a>
      </div>
    </div>
  </div>

  <!-- Header -->
  <header class="header">
    <div class="container d-flex align-items-center justify-content-between py-2">
      <a href="{{ route('beranda') }}" class="logo d-flex align-items-center gap-2 text-decoration-none">
        <i class="bi bi-bank2 fs-2 text-primary"></i>
        <h1 class="mb-0">Koda<span>+</span></h1>
      </a>

      <!-- Desktop Nav -->
      <nav class="navmenu d-none d-xl-block">
        <ul>
          <li><a href="{{ route('beranda') }}" class="{{ request()->routeIs('beranda') ? 'active' : '' }}">Beranda</a></li>
          <li><a href="{{ route('beranda') }}#tentang">Tentang</a></li>
          <li><a href="{{ route('beranda') }}#layanan">Layanan</a></li>
          <li><a href="{{ route('beranda') }}#kontak">Kontak</a></li>
          @auth
            <li><a href="{{ route('dashboard') }}" class="btn-masuk"><i class="bi bi-grid me-1"></i>Dashboard</a></li>
          @else
            <li><a href="{{ route('masuk') }}" class="btn-masuk"><i class="bi bi-box-arrow-in-right me-1"></i>Masuk</a></li>
          @endauth
        </ul>
      </nav>

      <!-- Mobile Toggle -->
      <button class="btn d-xl-none border-0 fs-4" id="mobile-toggle" type="button">
        <i class="bi bi-list"></i>
      </button>
    </div>

    <!-- Mobile Nav -->
    <nav class="d-xl-none border-top is-9703d2e0" id="mobile-nav">
      <ul class="list-unstyled p-3 mb-0 bg-white">
        <li class="mb-2"><a href="{{ route('beranda') }}" class="d-block p-2 rounded {{ request()->routeIs('beranda') ? 'text-primary fw-bold' : 'text-dark' }}">Beranda</a></li>
        <li class="mb-2"><a href="{{ route('beranda') }}#tentang" class="d-block p-2 rounded text-dark">Tentang</a></li>
        <li class="mb-2"><a href="{{ route('beranda') }}#layanan" class="d-block p-2 rounded text-dark">Layanan</a></li>
        <li class="mb-2"><a href="{{ route('beranda') }}#kontak" class="d-block p-2 rounded text-dark">Kontak</a></li>
        @auth
          <li><a href="{{ route('dashboard') }}" class="d-block p-2 rounded bg-primary text-white text-center fw-bold">Dashboard</a></li>
        @else
          <li><a href="{{ route('masuk') }}" class="d-block p-2 rounded bg-primary text-white text-center fw-bold">Masuk</a></li>
        @endauth
      </ul>
    </nav>
  </header>
  <!-- /Header -->

  @if(session('sukses'))
    <div class="container py-2">
      <div class="alert-sukses">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('sukses') }}
      </div>
    </div>
  @endif

  @yield('konten')

  <!-- Footer -->
  <footer class="footer-utama" id="kontak">
    <div class="container">
      <div class="row g-4">
        <div class="col-lg-4">
          <h4 class="text-white mb-3"><i class="bi bi-bank2 me-2"></i>Koda+</h4>
          <p class="is-e95a782a">Koperasi simpan pinjam yang berdedikasi melayani anggota dengan prinsip kejujuran, kemandirian, dan kepedulian sosial sejak 2005.</p>
          <div class="mt-3">
            <div class="d-flex align-items-start gap-2 mb-2 is-727130b2">
              <i class="bi bi-geo-alt-fill text-primary mt-1"></i>
              <span>Jl. Koperasi No. 45, Yogyakarta 55211</span>
            </div>
            <div class="d-flex align-items-center gap-2 mb-2 is-727130b2">
              <i class="bi bi-telephone-fill text-primary"></i>
              <span>(0274) 123-4567</span>
            </div>
            <div class="d-flex align-items-center gap-2 is-727130b2">
              <i class="bi bi-envelope-fill text-primary"></i>
              <span>info@koperasi-kodagama.id</span>
            </div>
          </div>
        </div>
        <div class="col-lg-2 col-md-4">
          <h5>Menu</h5>
          <ul>
            <li><a href="{{ route('beranda') }}">Beranda</a></li>
            <li><a href="{{ route('beranda') }}#tentang">Tentang Kami</a></li>
            <li><a href="{{ route('beranda') }}#layanan">Layanan</a></li>
            <li><a href="{{ route('beranda') }}#kontak">Kontak</a></li>
          </ul>
        </div>
        <div class="col-lg-2 col-md-4">
          <h5>Layanan</h5>
          <ul>
            <li><a href="#">Simpanan Pokok</a></li>
            <li><a href="#">Simpanan Wajib</a></li>
            <li><a href="#">Simpanan Sukarela</a></li>
            <li><a href="#">Pinjaman Umum</a></li>
          </ul>
        </div>
        <div class="col-lg-4 col-md-4">
          <h5>Jam Operasional</h5>
          <ul class="list-unstyled is-727130b2">
            <li class="d-flex justify-content-between mb-1 py-1 border-bottom border-secondary">
              <span>Senin – Jumat</span><span>08:00 – 16:00</span>
            </li>
            <li class="d-flex justify-content-between mb-1 py-1 border-bottom border-secondary">
              <span>Sabtu</span><span>08:00 – 12:00</span>
            </li>
            <li class="d-flex justify-content-between py-1">
              <span>Minggu</span><span>Tutup</span>
            </li>
          </ul>
        </div>
      </div>
    </div>
    <div class="footer-bottom">
      <div class="container">
        &copy; {{ date('Y') }} Koda+. Semua Hak Dilindungi.
        <span class="ms-2">|</span>
        <span class="ms-2">Terdaftar dan Diawasi oleh Kementerian Koperasi RI</span>
      </div>
    </div>
  </footer>

  <!-- Scroll Top -->
  <button class="scroll-top" id="scrollTop" onclick="window.scrollTo({top:0,behavior:'smooth'})">
    <i class="bi bi-arrow-up"></i>
  </button>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script src="{{ asset('js/pages/utama-layout.js') }}"></script>

  @stack('js')
</body>
</html>
