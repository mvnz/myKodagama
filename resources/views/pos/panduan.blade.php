@extends('layouts.pos')

@section('judul', 'Panduan POS')

@section('breadcrumb')
  <li class="breadcrumb-item active">Panduan</li>
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('css/pages/bantuan.css') }}">
@endpush

@section('konten')

<div class="dashboard-welcome mb-4 is-5d93e480">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
      <div class="welcome-label mb-1">Dokumentasi</div>
      <h4 class="mb-1 fw-700">Panduan Penggunaan POS</h4>
      <p class="mb-0 welcome-date">
        <i class="bi bi-info-circle me-1"></i>
        Penjelasan fitur kasir, produk, supplier, dan laporan penjualan
      </p>
    </div>
    <div class="welcome-icon-wrap"><i class="bi bi-journal-text"></i></div>
  </div>
</div>

<div class="row g-4">
<div class="col-lg-8">

  <div class="form-card mb-4 p-3">
    <div class="help-section-title">Navigasi Cepat</div>
    <div class="row g-1">
      @foreach([
        ['#setup-awal',   'bi-list-check',    'Checklist Setup Awal'],
        ['#modul-kasir',  'bi-cart3',         'Kasir'],
        ['#modul-produk', 'bi-box-seam-fill', 'Produk & Stok'],
        ['#modul-kategori','bi-tags-fill',    'Kategori'],
        ['#modul-supplier','bi-truck',        'Supplier & Pencairan'],
        ['#modul-laporan','bi-bar-chart-fill','Riwayat & Laporan'],
        ['#tips',         'bi-lightbulb-fill','Tips'],
      ] as [$href, $icon, $label])
      <div class="col-sm-6 col-lg-4">
        <a href="{{ $href }}" class="help-nav-link"><i class="bi {{ $icon }}"></i> {{ $label }}</a>
      </div>
      @endforeach
    </div>
  </div>

  <div class="form-card mb-4" id="setup-awal">
    <div class="help-section-title">Checklist Setup Awal</div>
    <p class="mb-3 text-muted is-0de4e77b">Urutan yang disarankan sebelum mulai berjualan.</p>

    @foreach([
      ['Buat Kategori', 'Wajib', 'bg-primary', [
        'Menu <strong>Kategori</strong>, tambahkan kelompok barang (mis. Makanan, Minuman)',
      ]],
      ['Tambah Supplier', 'Opsional', 'bg-secondary', [
        'Menu <strong>Supplier</strong> &rarr; Tambah. Isi nama, pemilik, dan kota',
        'Hubungkan supplier saat membuat produk titipan agar saldonya tercatat',
      ]],
      ['Tambah Produk', 'Wajib', 'bg-primary', [
        'Menu <strong>Produk</strong> &rarr; Tambah Produk',
        'Isi kode, barcode, nama, kategori, satuan, harga beli, harga jual, dan stok awal',
        'Produk yang dinonaktifkan tidak muncul di kasir',
      ]],
      ['Mulai Berjualan', 'Harian', 'bg-success', [
        'Buka menu <strong>Kasir</strong>, scan atau cari produk, lalu klik <strong>Bayar</strong>',
      ]],
    ] as $i => [$judul, $label, $warna, $poin])
    <div class="help-step-wrap">
      <div class="help-step-line"></div>
      <div class="help-step-num is-537baf89">{{ $i + 1 }}</div>
      <div class="help-step-card">
        <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
          <h5 class="mb-0">{{ $judul }}</h5>
          <span class="help-badge {{ $warna }} text-white">{{ $label }}</span>
        </div>
        <ul class="help-sub-list">
          @foreach($poin as $p)
            <li><i class="bi bi-arrow-right-circle-fill"></i> {!! $p !!}</li>
          @endforeach
        </ul>
      </div>
    </div>
    @endforeach
  </div>

  <div class="form-card mb-4" id="modul-kasir">
    <div class="help-section-title">Kasir</div>
    <div class="row g-3">
      @foreach([
        ['bi-upc-scan', 'Scan Barcode', 'Arahkan scanner ke kolom pencarian; produk langsung masuk keranjang'],
        ['bi-search', 'Cari Produk', 'Ketik nama atau kode produk, lalu pilih dari daftar'],
        ['bi-percent', 'Diskon', 'Isi nominal diskon (Rp) di keranjang; total otomatis berkurang'],
        ['bi-credit-card', 'Metode Bayar', 'Tunai, Transfer, atau QRIS'],
      ] as [$icon, $judul, $ket])
      <div class="col-sm-6">
        <div class="help-shortcut">
          <i class="bi {{ $icon }}"></i>
          <div><strong>{{ $judul }}</strong><br><span class="text-muted is-0de4e77b">{{ $ket }}</span></div>
        </div>
      </div>
      @endforeach
    </div>
    <ul class="help-sub-list mt-3">
      <li><i class="bi bi-1-circle-fill"></i> Tambahkan produk ke keranjang, atur jumlah, isi diskon bila ada</li>
      <li><i class="bi bi-2-circle-fill"></i> Klik <strong>Bayar</strong>, pilih metode, lalu isi jumlah bayar (untuk tunai)</li>
      <li><i class="bi bi-3-circle-fill"></i> Kembalian dihitung otomatis; setelah sukses, struk bisa dicetak</li>
    </ul>
    <div class="help-warn"><i class="bi bi-exclamation-triangle-fill me-1"></i>
      Produk dengan stok habis tidak bisa dijual, dan jumlah yang melebihi stok akan ditolak.</div>
    <div class="help-tip"><i class="bi bi-info-circle-fill me-1"></i>
      Setiap transaksi sukses otomatis mengurangi stok.</div>
  </div>

  <div class="form-card mb-4" id="modul-produk">
    <div class="help-section-title">Produk &amp; Stok</div>
    <ul class="help-sub-list">
      <li><i class="bi bi-plus-circle-fill"></i> <strong>Tambah/Ubah produk:</strong> kode, barcode, nama, kategori, supplier, satuan, harga beli, harga jual, stok, dan status aktif</li>
      <li><i class="bi bi-funnel-fill"></i> <strong>Cari &amp; filter:</strong> cari nama, kode, atau barcode; filter per kategori atau supplier</li>
      <li><i class="bi bi-upc-scan"></i> <strong>Tambah stok dengan scanner:</strong> di halaman Produk, scan barcode, isi jumlah, tekan Enter</li>
    </ul>
    <div class="help-tip"><i class="bi bi-info-circle-fill me-1"></i>
      Jika barcode belum terdaftar, tersedia tombol untuk langsung membuat produk baru dengan barcode tersebut.</div>
  </div>

  <div class="form-card mb-4" id="modul-kategori">
    <div class="help-section-title">Kategori</div>
    <p class="mb-0 is-0de4e77b">Menu <strong>Kategori</strong> dipakai untuk menambah, mengubah nama, atau menghapus kelompok produk. Kategori memudahkan filter di halaman Produk.</p>
  </div>

  <div class="form-card mb-4" id="modul-supplier">
    <div class="help-section-title">Supplier &amp; Pencairan Saldo</div>
    <ul class="help-sub-list">
      <li><i class="bi bi-truck"></i> Setiap penjualan produk milik supplier menambah <strong>saldo</strong> supplier sebesar harga pokok (harga beli) barang yang terjual</li>
      <li><i class="bi bi-eye-fill"></i> Buka detail supplier untuk melihat saldo, total dicairkan, dan mutasi saldo</li>
      <li><i class="bi bi-cash-coin"></i> Klik <strong>Cairkan Saldo</strong> untuk mencatat pembayaran ke supplier</li>
    </ul>
    <div class="help-warn"><i class="bi bi-exclamation-triangle-fill me-1"></i>
      Jumlah pencairan tidak boleh melebihi saldo yang tersedia.</div>
  </div>

  <div class="form-card mb-4" id="modul-laporan">
    <div class="help-section-title">Riwayat &amp; Laporan Penjualan</div>
    <div class="row g-3">
      @foreach([
        ['bi-receipt', 'Riwayat Transaksi', 'Daftar seluruh transaksi beserta detail item'],
        ['bi-printer-fill', 'Cetak Struk', 'Cetak ulang struk dari detail transaksi'],
        ['bi-bar-chart-fill', 'Laporan Penjualan', 'Ringkasan penjualan per periode'],
        ['bi-file-earmark-text', 'Cetak Laporan', 'Versi siap cetak dari laporan penjualan'],
      ] as [$icon, $judul, $ket])
      <div class="col-sm-6">
        <div class="help-shortcut">
          <i class="bi {{ $icon }}"></i>
          <div><strong>{{ $judul }}</strong><br><span class="text-muted is-0de4e77b">{{ $ket }}</span></div>
        </div>
      </div>
      @endforeach
    </div>
  </div>

  <div class="form-card" id="tips">
    <div class="help-section-title">Tips</div>
    <div class="row g-3">
      @foreach([
        ['bi-upc-scan', 'Scanner USB', 'Scanner bekerja seperti keyboard, jadi pastikan kursor ada di kolom scan'],
        ['bi-box-seam', 'Cek Stok Rutin', 'Gunakan scan stok di halaman Produk saat barang baru datang'],
        ['bi-toggle-on', 'Nonaktifkan, Jangan Hapus', 'Produk yang tidak dijual lagi cukup dinonaktifkan agar riwayat tetap utuh'],
        ['bi-arrow-left-circle', 'Menu Utama', 'Gunakan Menu Utama di sidebar untuk kembali ke aplikasi koperasi'],
      ] as [$icon, $judul, $ket])
      <div class="col-sm-6">
        <div class="help-shortcut">
          <i class="bi {{ $icon }}"></i>
          <div><strong>{{ $judul }}</strong><br><span class="text-muted is-0de4e77b">{{ $ket }}</span></div>
        </div>
      </div>
      @endforeach
    </div>
  </div>

</div>

<div class="col-lg-4">
  <div class="form-card mb-4">
    <div class="help-section-title">Hak Akses Menu</div>
    <div class="d-flex flex-column gap-3">
      @foreach([
        ['bi-cart3 text-primary', 'Kasir', 'Izin <code>pos.kasir</code>', ['Transaksi penjualan', 'Scan &amp; checkout']],
        ['bi-receipt text-success', 'Riwayat', 'Izin <code>pos.riwayat</code>', ['Riwayat transaksi &amp; struk', 'Laporan penjualan']],
        ['bi-box-seam-fill text-warning', 'Produk', 'Izin <code>pos.produk</code>', ['Kelola produk &amp; kategori', 'Tambah stok via scanner']],
        ['bi-truck text-danger', 'Supplier', 'Izin <code>pos.toko</code>', ['Kelola supplier', 'Cairkan saldo']],
      ] as [$icon, $judul, $izin, $poin])
      <div class="help-role-card">
        <h6><i class="bi {{ $icon }} me-2"></i>{{ $judul }}</h6>
        <div class="small text-muted mb-1">{!! $izin !!}</div>
        <ul>
          @foreach($poin as $p)<li>{!! $p !!}</li>@endforeach
        </ul>
      </div>
      @endforeach
    </div>
    <p class="small text-muted mt-3 mb-0">Menu yang tidak muncul di sidebar berarti akun Anda belum punya izin tersebut. Hubungi admin.</p>
  </div>
</div>

</div>
@endsection