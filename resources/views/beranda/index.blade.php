<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $pengaturan['nama_koperasi'] ?? 'Koperasi Kodagama' }} &ndash; {{ $pengaturan['sub_nama'] ?? 'Simpan Pinjam Terpercaya' }}</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500&family=Montserrat:wght@300;400;600;700&family=Lato:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="{{ asset('css/koperasi.css') }}">
  <style>
    :root {
      --accent: #175cdd;
      --heading: #112344;
      --nav-font: 'Lato', sans-serif;
      --heading-font: 'Montserrat', sans-serif;
    }
    *, *::before, *::after { box-sizing: border-box; }
    html, body { height: 100%; overflow: hidden; margin: 0; font-family: 'Roboto', sans-serif; color: #3c4049; }

    /* TOPBAR */
    .topbar {
      background: var(--accent);
      height: 38px;
      display: flex; align-items: center;
      font-size: 13px; color: #fff; padding: 0 2rem;
    }
    .topbar .tb-left { display: flex; gap: 1.5rem; align-items: center; }
    .topbar .tb-left span { display: flex; align-items: center; gap: .4rem; }
    .topbar .tb-right { margin-left: auto; opacity: .8; font-size: 12px; }

    /* HEADER */
    .site-header {
      background: #fff; height: 58px;
      display: flex; align-items: center; padding: 0 2rem;
      box-shadow: 0 2px 12px rgba(0,0,0,.07); position: relative; z-index: 2;
    }
    .site-logo { display: flex; align-items: center; gap: .65rem; text-decoration: none; }
    .site-logo .logo-icon {
      width: 36px; height: 36px; background: var(--accent); border-radius: 8px;
      display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.1rem;
    }
    .site-logo .logo-name { font-family: var(--heading-font); font-weight: 700; font-size: 1.15rem; color: var(--heading); line-height: 1; }
    .site-logo .logo-sub  { font-size: .68rem; color: #6b7280; margin-top: 2px; }
    .site-nav { margin-left: auto; display: flex; align-items: center; gap: .25rem; }
    .site-nav a {
      font-family: var(--nav-font); font-size: .88rem; font-weight: 500;
      color: #3c4049; text-decoration: none; padding: .4rem .85rem;
      border-radius: 6px; transition: color .2s, background .2s;
    }
    .site-nav a:hover { color: var(--accent); background: #eff6ff; }

    /* HERO */
    .hero-wrap {
      height: calc(100vh - 96px); /* 38 topbar + 58 header */
      display: grid;
      grid-template-columns: 1fr 1fr;
      overflow: hidden;
    }

    /* LEFT */
    .hero-left {
      background: linear-gradient(145deg, #0c1d3a 0%, #112344 55%, #1d4ed8 100%);
      color: #fff;
      display: flex; flex-direction: column; justify-content: center;
      padding: 2.5rem 3rem 2.5rem 4rem;
      position: relative; overflow: hidden;
    }
    .hero-left::before {
      content: ''; position: absolute;
      width: 420px; height: 420px; border-radius: 50%;
      background: rgba(255,255,255,.04);
      top: -120px; right: -120px; pointer-events: none;
    }
    .hero-left::after {
      content: ''; position: absolute;
      width: 260px; height: 260px; border-radius: 50%;
      background: rgba(255,255,255,.04);
      bottom: -70px; left: -70px; pointer-events: none;
    }

    .trust-badges { display: flex; gap: .65rem; flex-wrap: wrap; margin-bottom: 1.4rem; position: relative; }
    .badge-item {
      display: flex; align-items: center; gap: .4rem;
      padding: .3rem .85rem;
      background: rgba(255,255,255,.12);
      border-radius: 50px;
      color: rgba(255,255,255,.9);
      font-size: .78rem; font-weight: 500;
    }

    .hero-h1 {
      font-family: var(--heading-font);
      font-size: 2.6rem; font-weight: 700; line-height: 1.25;
      color: #fff; margin-bottom: .9rem; position: relative;
    }
    .hero-h1 .highlight {
      color: #60a5fa;
      position: relative; display: inline-block;
    }
    .hero-h1 .highlight::after {
      content: ''; position: absolute;
      bottom: 1px; left: 0; right: 0;
      height: 3px; background: #60a5fa; border-radius: 2px; opacity: .6;
    }

    .hero-desc { font-size: .95rem; line-height: 1.7; color: rgba(255,255,255,.7); margin-bottom: 1.6rem; max-width: 420px; position: relative; }

    .hero-stats { display: flex; gap: 2rem; margin-bottom: 1.8rem; position: relative; }
    .hero-stats .s-item h4 {
      font-family: var(--heading-font);
      font-size: 1.7rem; font-weight: 700;
      color: #60a5fa; margin: 0; line-height: 1;
    }
    .hero-stats .s-item p { font-size: .75rem; color: rgba(255,255,255,.55); margin: .2rem 0 0; font-weight: 400; }

    .hero-contact-box {
      display: inline-flex; align-items: center; gap: .85rem;
      padding: .7rem 1.2rem;
      background: rgba(255,255,255,.08);
      border: 1px solid rgba(255,255,255,.15);
      border-radius: 12px; max-width: fit-content; position: relative;
    }
    .hero-contact-box .contact-ico {
      width: 40px; height: 40px; background: rgba(255,255,255,.15); color: #fff;
      border-radius: 50%; display: flex; align-items: center; justify-content: center;
      font-size: 1rem; flex-shrink: 0;
    }
    .hero-contact-box .contact-text small { display: block; color: rgba(255,255,255,.5); font-size: .68rem; }
    .hero-contact-box .contact-text strong { color: #fff; font-size: .9rem; }

    /* RIGHT: login panel */
    .hero-right {
      background: #f4f8ff;
      display: flex; align-items: center; justify-content: center;
      padding: 2rem 3rem;
      position: relative; overflow: hidden;
    }
    .hero-right::before {
      content: ''; position: absolute;
      width: 300px; height: 300px; border-radius: 50%;
      background: color-mix(in srgb, #175cdd, transparent 93%);
      top: -80px; right: -80px; pointer-events: none;
    }
    .hero-right::after {
      content: ''; position: absolute;
      width: 200px; height: 200px; border-radius: 50%;
      background: color-mix(in srgb, #175cdd, transparent 93%);
      bottom: -50px; left: -50px; pointer-events: none;
    }

    /* LOGIN CARD */
    .login-card {
      width: 100%; max-width: 420px;
      background: #fff;
      border-radius: 24px;
      padding: 2.4rem 2.2rem;
      box-shadow: 0 20px 60px rgba(17,35,68,.12);
      position: relative; z-index: 1;
    }
    .login-card .lc-header { text-align: center; margin-bottom: 1.8rem; }
    .login-card .lc-header .lc-icon {
      width: 60px; height: 60px;
      background: linear-gradient(135deg, var(--accent), #1e3a8a);
      border-radius: 16px; margin: 0 auto .9rem;
      display: flex; align-items: center; justify-content: center;
      font-size: 1.6rem; color: #fff;
    }
    .login-card .lc-header h2 {
      font-family: var(--heading-font); font-size: 1.4rem; font-weight: 700;
      color: var(--heading); margin-bottom: .25rem;
    }
    .login-card .lc-header p { font-size: .85rem; color: #9ca3af; margin: 0; }

    .lc-alert {
      font-size: .83rem; border-radius: 10px; padding: .65rem .9rem;
      margin-bottom: 1.1rem;
      display: flex; align-items: center; gap: .5rem;
    }
    .lc-alert-err  { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
    .lc-alert-ok   { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }

    .lc-form-group { margin-bottom: 1rem; }
    .lc-form-group label { display: block; font-size: .8rem; font-weight: 600; color: var(--heading); margin-bottom: .4rem; }
    .lc-input-wrap { position: relative; }
    .lc-input-wrap i.icon-left {
      position: absolute; left: .9rem; top: 50%; transform: translateY(-50%);
      color: #9ca3af; font-size: 1rem; pointer-events: none;
    }
    .lc-input {
      width: 100%; padding: .7rem .9rem .7rem 2.5rem;
      border: 1.5px solid #e5e7eb; border-radius: 10px;
      font-size: .9rem; color: var(--heading);
      transition: border-color .2s, box-shadow .2s;
      outline: none; background: #fff;
    }
    .lc-input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(23,92,221,.1); }
    .lc-input.is-invalid { border-color: #dc2626; }

    .lc-pw-toggle {
      position: absolute; right: .9rem; top: 50%; transform: translateY(-50%);
      background: none; border: none; color: #9ca3af; cursor: pointer; padding: 0;
      font-size: 1rem; line-height: 1;
    }
    .lc-pw-toggle:hover { color: var(--accent); }

    .lc-remember {
      display: flex; align-items: center; gap: .5rem;
      font-size: .82rem; color: #6b7280; margin-bottom: 1.3rem;
      cursor: pointer;
    }
    .lc-remember input { accent-color: var(--accent); width: 14px; height: 14px; cursor: pointer; }

    .btn-login {
      width: 100%; padding: .75rem;
      background: linear-gradient(135deg, var(--accent), #1e3a8a);
      color: #fff; border: none; border-radius: 50px;
      font-family: var(--heading-font); font-weight: 700; font-size: .95rem;
      cursor: pointer; transition: opacity .2s, transform .2s;
      display: flex; align-items: center; justify-content: center; gap: .5rem;
    }
    .btn-login:hover { opacity: .9; transform: translateY(-1px); }
    .btn-login:active { transform: translateY(0); }

    .lc-divider { display: flex; align-items: center; gap: .75rem; margin: 1.2rem 0 .8rem; }
    .lc-divider::before,
    .lc-divider::after { content: ''; flex: 1; height: 1px; background: #e5e7eb; }
    .lc-divider span { font-size: .75rem; color: #9ca3af; white-space: nowrap; }

    .lc-info-pills { display: grid; grid-template-columns: 1fr 1fr; gap: .6rem; }
    .lc-pill {
      display: flex; align-items: center; gap: .5rem;
      background: #f4f8ff; border-radius: 10px; padding: .6rem .75rem;
      font-size: .78rem; color: var(--heading); font-weight: 500;
    }
    .lc-pill i { color: var(--accent); font-size: .95rem; }

    /* Page footer */
    .page-footer {
      position: fixed; bottom: 0; left: 0; right: 0;
      background: rgba(255,255,255,.9); backdrop-filter: blur(8px);
      border-top: 1px solid rgba(0,0,0,.06);
      padding: .4rem 2rem; font-size: .72rem; color: #9ca3af;
      display: flex; justify-content: space-between; align-items: center; z-index: 50;
    }

    @media (max-width: 1100px) {
      .hero-left  { padding: 2rem 2rem 2rem 3rem; }
      .hero-h1    { font-size: 2.2rem; }
      .hero-right { padding: 2rem; }
    }
    @media (max-width: 900px) {
      .hero-h1 { font-size: 1.9rem; }
      .login-card { padding: 1.8rem 1.5rem; }
    }
    @media (max-width: 768px) {
      html, body { overflow: auto; }
      .hero-wrap  { grid-template-columns: 1fr; height: auto; }
      .hero-left  { padding: 2.5rem 1.5rem; }
      .hero-right { padding: 2rem 1.5rem; }
      .page-footer { position: static; margin-top: 1rem; }
      .topbar .tb-right { display: none; }
      .topbar .tb-left span:last-child { display: none; }
    }
  </style>
</head>
<body>

<!-- TOPBAR -->
<div class="topbar">
  <div class="tb-left">
    <span><i class="bi bi-telephone-fill"></i> {{ $pengaturan['telepon'] ?? '(0274) 123-4567' }}</span>
    <span><i class="bi bi-envelope-fill"></i> {{ $pengaturan['email'] ?? 'info@koperasi.id' }}</span>
    <span><i class="bi bi-clock-fill"></i> {{ $pengaturan['jam_operasional'] ?? 'Senin–Jumat, 08.00–16.00' }}</span>
  </div>
  <div class="tb-right">{{ $pengaturan['slogan_resmi'] ?? 'Terdaftar Kementerian Koperasi RI' }}</div>
</div>

<!-- HEADER -->
<header class="site-header">
  <a href="{{ route('beranda') }}" class="site-logo">
    <div class="logo-icon"><i class="bi bi-bank2"></i></div>
    <div>
      <div class="logo-name">{{ $pengaturan['nama_koperasi'] ?? 'Kodagama' }}</div>
      <div class="logo-sub">{{ $pengaturan['sub_nama'] ?? 'Koperasi Simpan Pinjam' }}</div>
    </div>
  </a>
  <nav class="site-nav">
    <a href="{{ route('beranda') }}">Beranda</a>
    @if($pengaturan['nav_tentang_url'] ?? '#')
    <a href="{{ $pengaturan['nav_tentang_url'] ?? '#' }}">{{ $pengaturan['nav_tentang_label'] ?? 'Tentang' }}</a>
    @endif
    @if($pengaturan['nav_layanan_url'] ?? '#')
    <a href="{{ $pengaturan['nav_layanan_url'] ?? '#' }}">{{ $pengaturan['nav_layanan_label'] ?? 'Layanan' }}</a>
    @endif
    @if($pengaturan['nav_kontak_url'] ?? '#')
    <a href="{{ $pengaturan['nav_kontak_url'] ?? '#' }}">{{ $pengaturan['nav_kontak_label'] ?? 'Kontak' }}</a>
    @endif
  </nav>
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
    {{-- Already logged in: show dashboard shortcut --}}
    <div class="login-card" style="text-align:center">
      <div class="lc-header">
        <div class="lc-icon"><i class="bi bi-person-check-fill"></i></div>
        <h2>Selamat Datang, {{ Auth::user()->name }}!</h2>
        <p>Anda sudah masuk ke sistem</p>
      </div>
      <a href="{{ route('dashboard') }}" class="btn-login" style="text-decoration:none">
        <i class="bi bi-grid-fill"></i> Buka Dashboard
      </a>
      <div class="lc-divider"><span>atau</span></div>
      <form method="POST" action="{{ route('keluar') }}">
        @csrf
        <button type="submit" style="width:100%;padding:.6rem;background:none;border:1.5px solid #e5e7eb;border-radius:50px;font-size:.88rem;color:#6b7280;cursor:pointer;transition:all .2s">
          <i class="bi bi-box-arrow-left"></i> Keluar
        </button>
      </form>
    </div>

    @else
    {{-- Not logged in: show login form --}}
    <div class="login-card">
      <div class="lc-header">
        <div class="lc-icon"><i class="bi bi-box-arrow-in-right"></i></div>
        <h2>Masuk ke Sistem</h2>
        <p>Gunakan akun petugas atau admin</p>
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

      <div class="mt-3 text-center" style="font-size:.82rem;color:#64748b">
        Belum punya akun?
        <a href="{{ route('daftar') }}" style="font-weight:600;color:#175cdd">Daftar di sini</a>
      </div>

      <div class="lc-divider"><span>informasi layanan</span></div>

      <div class="lc-info-pills">
        <div class="lc-pill"><i class="bi bi-piggy-bank-fill"></i> Simpan Pinjam</div>
        <div class="lc-pill"><i class="bi bi-people-fill"></i> Manajemen Anggota</div>
        <div class="lc-pill"><i class="bi bi-cash-coin"></i> Cicilan &amp; Angsuran</div>
        <div class="lc-pill"><i class="bi bi-graph-up-arrow"></i> Laporan Keuangan</div>
      </div>
    </div>
    @endauth

  </div>

</section>

<div class="page-footer">
  <span>&copy; {{ date('Y') }} {{ $pengaturan['nama_koperasi'] ?? 'Koperasi Kodagama' }} &middot; {{ $pengaturan['slogan_resmi'] ?? 'Terdaftar Kementerian Koperasi RI' }}</span>
  <span>{{ $pengaturan['alamat'] ?? 'Yogyakarta' }} &middot; {{ $pengaturan['email'] ?? 'info@koperasi.id' }}</span>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  function togglePw() {
    const input = document.getElementById('password');
    const icon  = document.getElementById('pwIcon');
    if (input.type === 'password') {
      input.type = 'text';
      icon.className = 'bi bi-eye-slash-fill';
    } else {
      input.type = 'password';
      icon.className = 'bi bi-eye-fill';
    }
  }
</script>
</body>
</html>
