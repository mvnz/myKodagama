<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('judul', 'Dashboard') – {{ $namaKoperasiLayout }}</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <!-- Favicon -->
  <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
  <link rel="alternate icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Montserrat:wght@300;400;500;600;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">

  <!-- Bootstrap 5 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <!-- CSS Koperasi -->
  <link rel="stylesheet" href="{{ asset('css/koperasi.css') }}">
  <link rel="stylesheet" href="{{ asset('css/pages/inline-extracted.css') }}">

  <!-- Tom Select -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css">

  @stack('css')
</head>
<body class="is-bf063d1c">

  <!-- Sidebar -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
      <h2><i class="bi bi-bank2 me-2"></i>{{ $namaKoperasiLayout }}</h2>
      <p class="mb-0 mt-1 is-9f375a76">{{ $subNamaLayout }}</p>
    </div>

    <nav class="nav-menu">
      @php
        $u = auth()->user();
        $bisaDashboard      = $u->hasPermission('dashboard.lihat');
        $bisaAnggota        = $u->hasPermission('anggota.lihat');
        $bisaUnitKerja      = $u->hasPermission('unit-kerja.lihat');
        $bisaPengurus       = $u->hasPermission('pengaturan.pengurus');
        $adaDataMaster      = $bisaAnggota || $bisaUnitKerja || $bisaPengurus;
        $bisaSimpanan       = $u->hasPermission('simpanan.lihat');
        $bisaPinjaman       = $u->hasPermission('pinjaman.lihat');
        $bisaAngsuran       = $u->hasPermission('angsuran.lihat');
        $bisaPengeluaran    = $u->hasPermission('pengeluaran.lihat');
        $bisaPendapatan     = $u->hasPermission('pendapatan.lihat');
        $bisaRekon          = $u->hasPermission('simpanan.rekon');
        $bisaRekonAngsuran  = $u->hasPermission('angsuran.rekon');
        $bisaKelolaPengguna = $u->hasPermission('user.kelola');
        $adaTransaksi       = $bisaSimpanan || $bisaPinjaman || $bisaAngsuran || $bisaPengeluaran || $bisaPendapatan || $bisaRekon || $bisaRekonAngsuran;
        $bisaLaporan        = $u->hasPermission('laporan.lihat');
        $bisaMonitoring     = $u->hasPermission('monitoring.lihat');
        $bisaShu            = $u->hasPermission('shu.lihat');
        $adaLaporan         = $bisaLaporan || $bisaMonitoring || $bisaShu;
        $bisaAppSetting     = $u->hasPermission('pengaturan.aplikasi');
        $bisaSpSetting      = $u->hasPermission('pengaturan.simpan-pinjam');
        $bisaNotifWa        = $u->hasPermission('pengaturan.aplikasi'); // admin only
        $bisaRole           = $u->hasPermission('role.kelola');
        $bisaLog            = $u->hasPermission('log.lihat');
        $adaPengaturan      = $bisaAppSetting || $bisaSpSetting || $bisaRole || $bisaKelolaPengguna;
      @endphp

      <div class="nav-section-title">Menu Utama</div>

      @if($bisaDashboard)
      <div class="nav-item">
        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
          <i class="bi bi-grid-fill"></i> Dashboard
        </a>
      </div>
      @endif

      <div class="nav-item">
        <a href="{{ route('struktur-organisasi') }}" class="nav-link {{ request()->routeIs('struktur-organisasi') ? 'active' : '' }}">
          <i class="bi bi-diagram-3-fill"></i> Struktur Organisasi
        </a>
      </div>

      @if($adaDataMaster)
      <div class="nav-section-title">Data Master</div>

      @php $dataMasterAktif = request()->routeIs('anggota.*') || request()->routeIs('pengaturan.pengurus') || request()->routeIs('unit-kerja.*'); @endphp
      <div class="nav-item">
        <a href="#submenuDataMaster"
          class="nav-link d-flex align-items-center justify-content-between {{ $dataMasterAktif ? 'active' : '' }}"
          data-bs-toggle="collapse"
          aria-expanded="{{ $dataMasterAktif ? 'true' : 'false' }}"
          aria-controls="submenuDataMaster">
          <span><i class="bi bi-database-fill me-1"></i> Data Master</span>
          <i class="bi bi-chevron-down is-4e89bd68" id="chevronDataMaster"></i>
        </a>
        <div class="collapse {{ $dataMasterAktif ? 'show' : '' }}" id="submenuDataMaster">
          <div class="ps-3 mt-1 d-flex flex-column gap-1">
            @if($bisaAnggota)
            <a href="{{ route('anggota.index') }}"
              class="nav-link py-1 {{ request()->routeIs('anggota.*') ? 'active' : '' }}"
              style="font-size:.85rem">
              <i class="bi bi-people-fill"></i> Anggota
            </a>
            @endif
            @if($bisaUnitKerja)
            <a href="{{ route('unit-kerja.index') }}"
              class="nav-link py-1 {{ request()->routeIs('unit-kerja.*') ? 'active' : '' }}"
              style="font-size:.85rem">
              <i class="bi bi-building-fill"></i> Unit Kerja
            </a>
            @endif
            @if($bisaPengurus)
            <a href="{{ route('pengaturan.pengurus') }}"
              class="nav-link py-1 {{ request()->routeIs('pengaturan.pengurus') ? 'active' : '' }}"
              style="font-size:.85rem">
              <i class="bi bi-person-badge-fill"></i> Pengurus
            </a>
            @endif
          </div>
        </div>
      </div>
      @endif

      @if($adaTransaksi)
      <div class="nav-section-title">Transaksi</div>

      @php $transaksiAktif = request()->routeIs('simpanan.*') || request()->routeIs('pinjaman.*') || request()->routeIs('angsuran.*') || request()->routeIs('pengeluaran.*') || request()->routeIs('pendapatan.*') || request()->routeIs('rekon.*') || request()->routeIs('rekon-angsuran.*'); @endphp
      <div class="nav-item">
        <a href="#submenuTransaksi"
          class="nav-link d-flex align-items-center justify-content-between {{ $transaksiAktif ? 'active' : '' }}"
          data-bs-toggle="collapse"
          aria-expanded="{{ $transaksiAktif ? 'true' : 'false' }}"
          aria-controls="submenuTransaksi">
          <span><i class="bi bi-arrow-left-right me-1"></i> Transaksi</span>
          <i class="bi bi-chevron-down is-4e89bd68" id="chevronTransaksi"></i>
        </a>
        <div class="collapse {{ $transaksiAktif ? 'show' : '' }}" id="submenuTransaksi">
          <div class="ps-3 mt-1 d-flex flex-column gap-1">
            @if($bisaSimpanan)
            <a href="{{ route('simpanan.index') }}"
              class="nav-link py-1 {{ request()->routeIs('simpanan.*') && !request()->routeIs('rekon.*') ? 'active' : '' }}"
              style="font-size:.85rem">
              <i class="bi bi-piggy-bank-fill"></i> Simpanan
            </a>
            @endif
            @if($bisaRekon)
            <a href="{{ route('rekon.index') }}"
              class="nav-link py-1 {{ request()->routeIs('rekon.index') || request()->routeIs('rekon.preview') || request()->routeIs('rekon.import') ? 'active' : '' }}"
              style="font-size:.85rem">
              <i class="bi bi-file-earmark-arrow-up-fill"></i> Rekon Simpanan
            </a>
            @endif
            @if($bisaPinjaman)
            <a href="{{ route('pinjaman.index') }}"
              class="nav-link py-1 {{ request()->routeIs('pinjaman.*') ? 'active' : '' }}"
              style="font-size:.85rem">
              <i class="bi bi-cash-stack"></i> Pinjaman
            </a>
            @endif
            @if($bisaAngsuran)
            <a href="{{ route('angsuran.index') }}"
              class="nav-link py-1 {{ request()->routeIs('angsuran.*') ? 'active' : '' }}"
              style="font-size:.85rem">
              <i class="bi bi-calendar-check-fill"></i> Angsuran
            </a>
            @endif
            @if($bisaRekonAngsuran)
            <a href="{{ route('rekon-angsuran.index') }}"
              class="nav-link py-1 {{ request()->routeIs('rekon-angsuran.*') ? 'active' : '' }}"
              style="font-size:.85rem">
              <i class="bi bi-file-earmark-arrow-up-fill"></i> Rekon Angsuran
            </a>
            @endif
            @if($bisaPengeluaran)
            <a href="{{ route('pengeluaran.index') }}"
              class="nav-link py-1 {{ request()->routeIs('pengeluaran.*') ? 'active' : '' }}"
              style="font-size:.85rem">
              <i class="bi bi-cash-coin"></i> Pengeluaran
            </a>
            @endif
            @if($bisaPendapatan)
            <a href="{{ route('pendapatan.index') }}"
              class="nav-link py-1 {{ request()->routeIs('pendapatan.*') ? 'active' : '' }}"
              style="font-size:.85rem">
              <i class="bi bi-graph-up-arrow"></i> Pendapatan
            </a>
            @endif
          </div>
        </div>
      </div>
      @endif

      @if($adaLaporan)
      <div class="nav-section-title">Laporan</div>

      @if($bisaLaporan)
      <div class="nav-item">
        <a href="{{ route('laporan.keuangan') }}" class="nav-link {{ request()->routeIs('laporan.keuangan') || request()->routeIs('laporan.keuangan.cetak') ? 'active' : '' }}">
          <i class="bi bi-file-earmark-bar-graph-fill"></i> Laporan Keuangan
        </a>
      </div>
      <div class="nav-item">
        <a href="{{ route('laporan.laba-rugi') }}" class="nav-link {{ request()->routeIs('laporan.laba-rugi') ? 'active' : '' }}">
          <i class="bi bi-graph-up"></i> Laba Rugi
        </a>
      </div>
      <div class="nav-item">
        <a href="{{ route('laporan.lpj') }}" class="nav-link {{ request()->routeIs('laporan.lpj') || request()->routeIs('laporan.lpj.cetak') ? 'active' : '' }}">
          <i class="bi bi-journal-richtext"></i> Laporan LPJ
        </a>
      </div>
      @endif

      @can('shu.lihat')
      <div class="nav-item">
        <a href="{{ route('shu.index') }}" class="nav-link {{ request()->routeIs('shu.*') ? 'active' : '' }}">
          <i class="bi bi-pie-chart-fill"></i> SHU
        </a>
      </div>
      @endcan

      @if($bisaMonitoring)
      @php $monitoringAktif = request()->routeIs('monitoring.*'); @endphp
      <div class="nav-item">
        <a href="#submenuMonitoring"
          class="nav-link d-flex align-items-center justify-content-between {{ $monitoringAktif ? 'active' : '' }}"
          data-bs-toggle="collapse"
          aria-expanded="{{ $monitoringAktif ? 'true' : 'false' }}"
          aria-controls="submenuMonitoring">
          <span><i class="bi bi-speedometer2 me-1"></i> Monitoring</span>
          <i class="bi bi-chevron-down is-4e89bd68"></i>
        </a>
        <div class="collapse {{ $monitoringAktif ? 'show' : '' }}" id="submenuMonitoring">
          <div class="ps-3 mt-1 d-flex flex-column gap-1">
            <a href="{{ route('monitoring.simpanan') }}"
              class="nav-link py-1 {{ request()->routeIs('monitoring.simpanan') ? 'active' : '' }}"
              style="font-size:.85rem">
              <i class="bi bi-piggy-bank-fill"></i> Simpanan Wajib
            </a>
            <a href="{{ route('monitoring.pinjaman') }}"
              class="nav-link py-1 {{ request()->routeIs('monitoring.pinjaman') ? 'active' : '' }}"
              style="font-size:.85rem">
              <i class="bi bi-alarm-fill"></i> Angsuran
            </a>
          </div>
        </div>
      </div>
      @endif
      @endif

      @if($adaPengaturan || $bisaLog)
      <div class="nav-section-title">Pengaturan</div>

      @if($adaPengaturan)
      @php $pengaturanAktif = request()->routeIs('pengaturan.simpan-pinjam') || request()->routeIs('pengaturan.alur-pinjaman') || request()->routeIs('pengaturan.notifikasi-wa') || request()->routeIs('role.*') || request()->routeIs('user.*'); @endphp
      <div class="nav-item">
        <a href="#submenuPengaturan"
          class="nav-link d-flex align-items-center justify-content-between {{ $pengaturanAktif ? 'active' : '' }}"
          data-bs-toggle="collapse"
          aria-expanded="{{ $pengaturanAktif ? 'true' : 'false' }}"
          aria-controls="submenuPengaturan">
          <span><i class="bi bi-sliders me-1"></i> Pengaturan</span>
          <i class="bi bi-chevron-down is-4e89bd68" id="chevronPengaturan"></i>
        </a>
        <div class="collapse {{ $pengaturanAktif ? 'show' : '' }}" id="submenuPengaturan">
          <div class="ps-3 mt-1 d-flex flex-column gap-1">
            @if($bisaSpSetting)
            <a href="{{ route('pengaturan.simpan-pinjam') }}"
              class="nav-link py-1 {{ request()->routeIs('pengaturan.simpan-pinjam') ? 'active' : '' }}"
              style="font-size:.85rem">
              <i class="bi bi-bank2"></i> Simpan Pinjam
            </a>
            @endif
            @if($bisaAppSetting)
            <a href="{{ route('pengaturan.aplikasi') }}"
              class="nav-link py-1 {{ request()->routeIs('pengaturan.aplikasi') ? 'active' : '' }}"
              style="font-size:.85rem">
              <i class="bi bi-gear-fill"></i> Aplikasi
            </a>
            @endif
            @if($bisaAppSetting)
            <a href="{{ route('pengaturan.alur-pinjaman') }}"
              class="nav-link py-1 {{ request()->routeIs('pengaturan.alur-pinjaman') ? 'active' : '' }}"
              style="font-size:.85rem">
              <i class="bi bi-diagram-3-fill"></i> Alur Pinjaman
            </a>
            @endif
            @if($bisaNotifWa)
            <a href="{{ route('pengaturan.notifikasi-wa') }}"
              class="nav-link py-1 {{ request()->routeIs('pengaturan.notifikasi-wa') ? 'active' : '' }}"
              style="font-size:.85rem">
              <i class="bi bi-whatsapp"></i> Notifikasi WA
            </a>
            @endif
            @if($bisaRole)
            <a href="{{ route('role.index') }}"
              class="nav-link py-1 {{ request()->routeIs('role.*') ? 'active' : '' }}"
              style="font-size:.85rem">
              <i class="bi bi-shield-lock-fill"></i> Role
            </a>
            @endif
            @if($bisaKelolaPengguna)
            <a href="{{ route('user.index') }}"
              class="nav-link py-1 {{ request()->routeIs('user.*') ? 'active' : '' }}"
              style="font-size:.85rem">
              <i class="bi bi-people-fill"></i> Pengguna
            </a>
            @endif
          </div>
        </div>
      </div>
      @endif

      @if($bisaLog)
      <div class="nav-item">
        <a href="{{ route('log.index') }}" class="nav-link {{ request()->routeIs('log.*') ? 'active' : '' }}">
          <i class="bi bi-clock-history"></i> History Log
        </a>
      </div>
      @endif
      @endif

      <div class="nav-section-title">Akun</div>

      <div class="nav-item">
        <a href="{{ route('profil') }}" class="nav-link {{ request()->routeIs('profil') || request()->routeIs('profil.*') ? 'active' : '' }}">
          <i class="bi bi-person-circle"></i> Profil Saya
        </a>
      </div>

      <div class="nav-item">
        <a href="{{ route('bantuan') }}" class="nav-link {{ request()->routeIs('bantuan') ? 'active' : '' }}">
          <i class="bi bi-question-circle"></i> Panduan
        </a>
      </div>

      <div class="nav-item">
        <a href="{{ route('home') }}" class="nav-link">
          <i class="bi bi-arrow-left-circle"></i> Kembali ke Menu
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
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            @yield('breadcrumb')
          </ol>
        </nav>
      </div>

      <div class="d-flex align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
          <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold is-c99a529f"
           >
            {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
          </div>
          <div class="d-none d-md-block">
            <div class="is-d38ffea9">{{ Auth::user()->name }}</div>
            <div class="is-a7e9f0b8">{{ Auth::user()->roleModel?->nama ?? Auth::user()->role }}</div>
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
    </div>

    <!-- Content Area -->
    <div class="content-area">
      @yield('konten')
    </div>

    <!-- Footer -->
    <div class="text-center py-3 border-top is-bc35d6f5">
      &copy; {{ date('Y') }} {{ $namaKoperasiLayout }}. Sistem Manajemen Koperasi.
    </div>
  </div>
  <!-- /Main Content -->

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Tom Select JS -->
  <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

  <script src="{{ asset('js/pages/app-layout.js') }}"></script>

  @stack('js')

  <script src="{{ asset('js/pages/tom-select-init.js') }}"></script>
</body>
</html>
