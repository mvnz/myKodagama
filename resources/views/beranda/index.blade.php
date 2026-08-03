<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $pengaturan['nama_koperasi'] ?? 'Koda+' }} &ndash; {{ $pengaturan['sub_nama'] ?? 'Simpan Pinjam Terpercaya' }}</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Sora:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="{{ asset('css/koperasi.css') }}">
  <link rel="stylesheet" href="{{ asset('css/pages/inline-extracted.css') }}">
  <link rel="stylesheet" href="{{ asset('css/pages/beranda.css') }}">
</head>
<body>

<!-- TOPBAR -->
<div class="topbar">
  <div class="tb-left">
    <span><i class="bi bi-telephone-fill"></i> {{ $pengaturan['telepon'] ?? '(0274) 123-4567' }}</span>
    <span><i class="bi bi-envelope-fill"></i> {{ $pengaturan['email'] ?? 'info@koperasi.id' }}</span>
    <span><i class="bi bi-clock-fill"></i> {{ $pengaturan['jam_operasional'] ?? 'Senin–Jumat, 08.00–16.00' }}</span>
  </div>
  <div class="tb-right">{{ $pengaturan['slogan_resmi'] ?? '' }}</div>
</div>

<!-- HEADER -->
<header class="site-header">
  <a href="{{ route('beranda') }}" class="site-logo">
    <div class="logo-icon"><i class="bi bi-bank2"></i></div>
    <div>
      <div class="logo-name">{{ $pengaturan['nama_koperasi'] ?? 'Koda+' }}</div>
      <div class="logo-sub">{{ $pengaturan['sub_nama'] ?? 'Koperasi Simpan Pinjam' }}</div>
    </div>
  </a>
</header>

<!-- HERO -->
<section class="hero-wrap">

  <!-- LEFT: branding -->
  <div class="hero-left">
    <div class="trust-badges">
      <span class="badge-item"><i class="bi bi-patch-check-fill"></i> Terpercaya sejak {{ $pengaturan['tahun_berdiri'] ?? '2005' }}</span>
      <span class="badge-item"><i class="bi bi-shield-fill-check"></i> Terdaftar Resmi</span>
      <span class="badge-item"><i class="bi bi-people-fill"></i> {{ $pengaturan['jumlah_anggota'] ?? '2.500+' }} Anggota</span>
    </div>

    <h1 class="hero-h1">
      {{ $pengaturan['tagline'] ?? 'Keuangan Lebih Baik Bersama Koperasi Terpercaya' }}
    </h1>

    <p class="hero-desc">
      {{ $pengaturan['deskripsi'] ?? 'Layanan simpan pinjam yang mudah, transparan, dan berbunga rendah untuk seluruh anggota.' }}
    </p>

    <div class="hero-stats">
      <div class="s-item"><h4>{{ $pengaturan['jumlah_anggota'] ?? '2.500+' }}</h4><p>Anggota Aktif</p></div>
      <div class="s-item"><h4>{{ $pengaturan['bunga_pinjaman'] ?? '1,5%' }}</h4><p>Bunga/Bulan</p></div>
      <div class="s-item"><h4>{{ $pengaturan['tahun_operasi'] ?? '20+' }}</h4><p>Tahun Berdiri</p></div>
    </div>

    <div class="hero-contact-box">
      <div class="contact-ico"><i class="bi bi-headset"></i></div>
      <div class="contact-text">
        <small>Hubungi Kami</small>
        <strong>{{ $pengaturan['telepon'] ?? '(0274) 123-4567' }}</strong>
      </div>
    </div>
  </div>

  <!-- RIGHT: login form -->
  <div class="hero-right">

    @auth
    {{-- Already logged in: show module shortcuts --}}
    <div class="login-card is-13076d95">
      <div class="lc-header">
        <div class="lc-icon"><i class="bi bi-person-check-fill"></i></div>
        <h2>Selamat Datang, {{ Auth::user()->name }}!</h2>
        <p>Anda sudah masuk ke sistem</p>
      </div>
      <a href="{{ route('dashboard') }}" class="btn-login btn-login-koperasi is-4646c9d8">
        <i class="bi bi-bank2"></i> Koperasi
      </a>
      @php
        $bisaAksesPos = Auth::user()->hasPermission('pos.kasir')
          || Auth::user()->hasPermission('pos.riwayat')
          || Auth::user()->hasPermission('pos.produk')
          || Auth::user()->hasPermission('pos.toko');
      @endphp
      @if($bisaAksesPos)
      <a href="{{ route('pos.kasir') }}" class="btn-login btn-login-pos is-116e33d4">
        <i class="bi bi-shop"></i> Point of Sale
      </a>
      @endif
      <div class="lc-divider"><span>atau</span></div>
      <form method="POST" action="{{ route('keluar') }}">
        @csrf
        <button type="submit" class="is-1cfe83af">
          <i class="bi bi-box-arrow-left"></i> Keluar
        </button>
      </form>
    </div>

    @else
    {{-- Not logged in: show login form --}}
    <div class="login-card">
      <div class="lc-header">
        <h2>Login</h2>
      </div>

      @if ($errors->any())
        <div class="lc-alert lc-alert-err">
          <i class="bi bi-exclamation-circle-fill"></i>
          {{ $errors->first() }}
        </div>
      @endif

      @if (session('status'))
        <div class="lc-alert lc-alert-ok">
          <i class="bi bi-check-circle-fill"></i>
          {{ session('status') }}
        </div>
      @endif

      <form method="POST" action="{{ route('masuk.proses') }}">
        @csrf

        <div class="lc-form-group">
          <label for="email">Alamat Email</label>
          <div class="lc-input-wrap">
            <i class="bi bi-envelope-fill icon-left"></i>
            <input
              type="email"
              id="email"
              name="email"
              class="lc-input {{ $errors->has('email') ? 'is-invalid' : '' }}"
              value="{{ old('email') }}"
              placeholder="contoh@email.com"
              autocomplete="email"
              required
            >
          </div>
        </div>

        <div class="lc-form-group">
          <label for="password">Kata Sandi</label>
          <div class="lc-input-wrap">
            <i class="bi bi-lock-fill icon-left"></i>
            <input
              type="password"
              id="password"
              name="password"
              class="lc-input {{ $errors->has('password') ? 'is-invalid' : '' }}"
              placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;"
              autocomplete="current-password"
              required
            >
            <button type="button" class="lc-pw-toggle" onclick="togglePw()" id="pwToggle">
              <i class="bi bi-eye-fill" id="pwIcon"></i>
            </button>
          </div>
        </div>

        <label class="lc-remember">
          <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
          Ingat saya di perangkat ini
        </label>

        <button type="submit" class="btn-login">
          <i class="bi bi-box-arrow-in-right"></i> Masuk Sekarang
        </button>
      </form>

    </div>
    @endauth

  </div>

</section>

<div class="page-footer">
  <span>&copy; {{ date('Y') }} {{ $pengaturan['nama_koperasi'] ?? 'Koda+' }} &middot; {{ $pengaturan['slogan_resmi'] ?? '' }}</span>
  <span>{{ $pengaturan['alamat'] ?? 'Yogyakarta' }} &middot; {{ $pengaturan['email'] ?? 'info@koperasi.id' }}</span>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/pages/beranda.js') }}"></script>
</body>
</html>
