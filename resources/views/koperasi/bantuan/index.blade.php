@extends('layouts.aplikasi')

@section('judul', 'Panduan Penggunaan')

@section('breadcrumb')
  <li class="breadcrumb-item active">Panduan</li>
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('css/pages/bantuan.css') }}">
@endpush

@section('konten')

{{-- Banner --}}
<div class="dashboard-welcome mb-4">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
      <div class="welcome-label mb-1">Dokumentasi</div>
      <h4 class="mb-1 fw-700">Panduan Penggunaan Aplikasi</h4>
      <p class="mb-0 welcome-date">
        <i class="bi bi-info-circle me-1"></i>
        Penjelasan lengkap seluruh fitur sistem koperasi
      </p>
    </div>
    <div class="welcome-icon-wrap"><i class="bi bi-journal-text"></i></div>
  </div>
</div>

<div class="row g-4">

{{-- ═══════════════════════════════════════
     KOLOM KIRI — Panduan per modul
════════════════════════════════════════ --}}
<div class="col-lg-8">

  {{-- NAVIGASI CEPAT --}}
  <div class="form-card mb-4 p-3">
    <div class="help-section-title">Navigasi Cepat</div>
    <div class="row g-1">
      @foreach([
        ['#setup-awal',   'bi-list-check',             'Checklist Setup Awal'],
        ['#alur-utama',    'bi-arrow-repeat',          'Alur Simpan Pinjam'],
        ['#modul-anggota', 'bi-people-fill',            'Anggota'],
        ['#modul-simpanan','bi-piggy-bank-fill',        'Simpanan & Penarikan'],
        ['#modul-pinjaman','bi-cash-stack',             'Pinjaman'],
        ['#modul-angsuran','bi-calendar-check-fill',    'Angsuran'],
        ['#modul-keuangan','bi-graph-up-arrow',         'Pendapatan & Pengeluaran'],
        ['#modul-laporan', 'bi-file-earmark-bar-graph', 'Laporan Keuangan'],
        ['#modul-shu',     'bi-pie-chart-fill',         'SHU'],
        ['#modul-user',    'bi-shield-lock-fill',       'Pengguna & Role'],
        ['#tips',          'bi-lightbulb-fill',         'Tips'],
      ] as [$href, $icon, $label])
      <div class="col-sm-6 col-lg-4">
        <a href="{{ $href }}" class="help-nav-link">
          <i class="bi {{ $icon }}"></i> {{ $label }}
        </a>
      </div>
      @endforeach
    </div>
  </div>

  {{-- ══ CHECKLIST SETUP AWAL ══ --}}
  <div class="form-card mb-4" id="setup-awal">
    <div class="help-section-title">Checklist Setup Awal sampai LPJ</div>
    <p class="mb-3 text-muted is-0de4e77b">
      Urutan ini direkomendasikan untuk mulai dari data nol sampai siap cetak LPJ akhir tahun.
    </p>

    <div class="help-step-wrap">
      <div class="help-step-line"></div>
      <div class="help-step-num is-537baf89">1</div>
      <div class="help-step-card">
        <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
          <h5 class="mb-0">Isi Profil Koperasi</h5>
          <span class="help-badge bg-primary text-white">Wajib</span>
        </div>
        <ul class="help-sub-list">
          <li><i class="bi bi-arrow-right-circle-fill"></i> Menu <strong>Pengaturan Aplikasi</strong></li>
          <li><i class="bi bi-dot"></i> Nama koperasi, sub nama, alamat, telepon, email</li>
          <li><i class="bi bi-dot"></i> Data pengurus utama (minimal ketua) untuk kebutuhan dokumen LPJ</li>
        </ul>
      </div>
    </div>

    <div class="help-step-wrap">
      <div class="help-step-line"></div>
      <div class="help-step-num is-d92dada5">2</div>
      <div class="help-step-card">
        <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
          <h5 class="mb-0">Set Pengaturan Simpan Pinjam</h5>
          <span class="help-badge bg-warning text-dark">Wajib</span>
        </div>
        <ul class="help-sub-list">
          <li><i class="bi bi-arrow-right-circle-fill"></i> Menu <strong>Pengaturan → Simpan Pinjam</strong></li>
          <li><i class="bi bi-dot"></i> Jenis anggota dan simpanan wajib per jenis</li>
          <li><i class="bi bi-dot"></i> Bunga pinjaman, tenor, denda, dan batas pinjaman</li>
        </ul>
      </div>
    </div>

    <div class="help-step-wrap">
      <div class="help-step-line"></div>
      <div class="help-step-num is-cc9199f8">3</div>
      <div class="help-step-card">
        <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
          <h5 class="mb-0">Buat Role & Pengguna Internal</h5>
          <span class="help-badge bg-info text-dark">Wajib</span>
        </div>
        <ul class="help-sub-list">
          <li><i class="bi bi-arrow-right-circle-fill"></i> Buat akun <strong>Admin / Ketua / Petugas</strong></li>
          <li><i class="bi bi-dot"></i> Pastikan permission modul sudah sesuai tugas masing-masing</li>
        </ul>
      </div>
    </div>

    <div class="help-step-wrap">
      <div class="help-step-line"></div>
      <div class="help-step-num is-16bb0564">4</div>
      <div class="help-step-card">
        <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
          <h5 class="mb-0">Input Data Anggota</h5>
          <span class="help-badge bg-success text-white">Wajib</span>
        </div>
        <ul class="help-sub-list">
          <li><i class="bi bi-dot"></i> Isi NIK, nama, tanggal lahir, alamat, telepon, unit kerja, tanggal bergabung</li>
          <li><i class="bi bi-dot"></i> Jika perlu, buat akun login anggota sekaligus</li>
        </ul>
      </div>
    </div>

    <div class="help-step-wrap">
      <div class="help-step-line"></div>
      <div class="help-step-num is-4b626ec7">5</div>
      <div class="help-step-card">
        <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
          <h5 class="mb-0">Mulai Input Transaksi Rutin</h5>
          <span class="help-badge is-43d991b1">Operasional</span>
        </div>
        <ul class="help-sub-list">
          <li><i class="bi bi-dot"></i> Simpanan pokok/wajib/sukarela</li>
          <li><i class="bi bi-dot"></i> Pinjaman diajukan → diverifikasi → disetujui</li>
          <li><i class="bi bi-dot"></i> Angsuran dibayar sesuai jadwal</li>
          <li><i class="bi bi-dot"></i> Pendapatan dan pengeluaran operasional dicatat rutin</li>
        </ul>
      </div>
    </div>

    <div class="help-step-wrap">
      <div class="help-step-num is-d010e822">6</div>
      <div class="help-step-card">
        <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
          <h5 class="mb-0">Tutup Tahun: SHU lalu LPJ</h5>
          <span class="help-badge is-42eb32be">Akhir Tahun</span>
        </div>
        <ul class="help-sub-list">
          <li><i class="bi bi-dot"></i> Cek kelengkapan data di Laporan Keuangan, Neraca, dan Laba Rugi</li>
          <li><i class="bi bi-dot"></i> Hitung SHU dan finalisasi jika sudah valid</li>
          <li><i class="bi bi-dot"></i> Buka <strong>Laporan → LPJ</strong>, pilih tahun, lalu cetak LPJ formal</li>
        </ul>
        <div class="help-tip"><i class="bi bi-lightbulb-fill me-1"></i>
          Rumus praktis: <strong>Isi master dulu</strong> → <strong>catat transaksi rutin</strong> → <strong>rekap keuangan</strong> → <strong>final SHU</strong> → <strong>cetak LPJ</strong>.
        </div>
      </div>
    </div>
  </div>

  {{-- ══ ALUR UTAMA ══ --}}
  <div class="form-card mb-4" id="alur-utama">
    <div class="help-section-title">Alur Utama Simpan Pinjam</div>

    <div class="help-step-wrap">
      <div class="help-step-line"></div>
      <div class="help-step-num is-537baf89">1</div>
      <div class="help-step-card">
        <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
          <h5 class="mb-0">Pendaftaran Anggota</h5>
          <span class="help-badge bg-primary text-white">Admin</span>
        </div>
        <p>Calon anggota didaftarkan oleh admin dengan mengisi data identitas, unit kerja, dan jenis keanggotaan.</p>
        <ul class="help-sub-list">
          <li><i class="bi bi-arrow-right-circle-fill"></i> Menu <strong>Anggota → Tambah Anggota</strong></li>
          <li><i class="bi bi-arrow-right-circle-fill"></i> Isi NIK (16 digit), nama, tempat/tgl lahir, jenis kelamin, alamat, telepon, unit kerja</li>
          <li><i class="bi bi-arrow-right-circle-fill"></i> Nomor anggota dibuat otomatis oleh sistem</li>
          <li><i class="bi bi-arrow-right-circle-fill"></i> Bisa sekaligus dibuat akun login untuk anggota tersebut</li>
        </ul>
        <div class="help-tip"><i class="bi bi-lightbulb-fill me-1"></i>
          Untuk import banyak anggota sekaligus, gunakan <strong>Import CSV</strong> di halaman daftar anggota.
          Download template CSV terlebih dahulu agar format sesuai.
        </div>
      </div>
    </div>

    <div class="help-step-wrap">
      <div class="help-step-line"></div>
      <div class="help-step-num is-d92dada5">2</div>
      <div class="help-step-card">
        <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
          <h5 class="mb-0">Setoran Simpanan</h5>
          <span class="help-badge bg-success text-white">Admin / Petugas</span>
        </div>
        <p>Setelah anggota terdaftar, catat setoran simpanan. Tiga jenis simpanan:</p>
        <ul class="help-sub-list">
          <li><i class="bi bi-piggy-bank-fill is-deccf9e7"></i> <strong>Pokok</strong> — sekali bayar saat pertama bergabung</li>
          <li><i class="bi bi-piggy-bank-fill is-deccf9e7"></i> <strong>Wajib</strong> — rutin setiap bulan, nominal sesuai pengaturan per jenis anggota</li>
          <li><i class="bi bi-piggy-bank-fill is-deccf9e7"></i> <strong>Sukarela</strong> — bebas nominal dan waktu; bisa ditarik kembali</li>
        </ul>
        <div class="help-tip"><i class="bi bi-lightbulb-fill me-1"></i>
          Simpanan wajib yang belum lunas bulan ini terlihat di <strong>Monitoring → Simpanan Wajib</strong>.
        </div>
      </div>
    </div>

    <div class="help-step-wrap">
      <div class="help-step-line"></div>
      <div class="help-step-num is-cc9199f8">3</div>
      <div class="help-step-card">
        <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
          <h5 class="mb-0">Pengajuan Pinjaman</h5>
          <span class="help-badge bg-warning text-dark">Admin / Anggota</span>
        </div>
        <p>Alur persetujuan pinjaman:</p>
        <ul class="help-sub-list">
          <li><i class="bi bi-send-fill is-f571fe9b"></i> <strong>Diajukan</strong> — anggota/admin mengisi form pengajuan</li>
          <li><i class="bi bi-search is-b1feed2b"></i> <strong>Diverifikasi</strong> — petugas cek kelengkapan &amp; kelayakan</li>
          <li><i class="bi bi-check-circle-fill is-deccf9e7"></i> <strong>Disetujui</strong> — ketua/admin memberi persetujuan akhir</li>
          <li><i class="bi bi-x-circle-fill is-bc8099b4"></i> <strong>Ditolak</strong> — bisa di tahap mana pun, disertai keterangan alasan</li>
        </ul>
        <div class="help-tip"><i class="bi bi-lightbulb-fill me-1"></i>
          Setelah <strong>Disetujui</strong>, jadwal angsuran dibuat otomatis berdasarkan tenor dan bunga dalam pengaturan Simpan-Pinjam.
        </div>
      </div>
    </div>

    <div class="help-step-wrap">
      <div class="help-step-line"></div>
      <div class="help-step-num is-16bb0564">4</div>
      <div class="help-step-card">
        <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
          <h5 class="mb-0">Pembayaran Angsuran</h5>
          <span class="help-badge is-502caab7">Admin / Petugas</span>
        </div>
        <p>Catat pembayaran cicilan pinjaman setiap bulan.</p>
        <ul class="help-sub-list">
          <li><i class="bi bi-arrow-right-circle-fill"></i> Menu <strong>Angsuran</strong> → filter anggota atau nomor pinjaman</li>
          <li><i class="bi bi-arrow-right-circle-fill"></i> Klik <span class="badge bg-success">Bayar</span> pada baris yang akan dibayar</li>
          <li><i class="bi bi-alarm-fill is-bc8099b4"></i> Lewat jatuh tempo → denda dihitung otomatis berdasarkan pengaturan</li>
          <li><i class="bi bi-arrow-right-circle-fill"></i> Setelah semua angsuran lunas, status pinjaman berubah menjadi <span class="badge bg-secondary">Lunas</span></li>
        </ul>
        <div class="help-tip"><i class="bi bi-lightbulb-fill me-1"></i>
          Gunakan filter <strong>Terlambat</strong> di halaman Monitoring Angsuran untuk melihat semua yang melewati jatuh tempo.
        </div>
      </div>
    </div>

    <div class="help-step-wrap">
      <div class="help-step-line"></div>
      <div class="help-step-num is-4b626ec7">5</div>
      <div class="help-step-card">
        <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
          <h5 class="mb-0">Laporan Keuangan</h5>
          <span class="help-badge is-43d991b1">Admin / Ketua</span>
        </div>
        <ul class="help-sub-list">
          <li><i class="bi bi-arrow-right-circle-fill"></i> <strong>Laporan Keuangan</strong> — arus kas simpanan, pinjaman, angsuran per periode</li>
          <li><i class="bi bi-arrow-right-circle-fill"></i> <strong>Neraca</strong> — posisi Aset, Kewajiban, dan Ekuitas koperasi per akhir tahun</li>
          <li><i class="bi bi-arrow-right-circle-fill"></i> <strong>Laba Rugi</strong> — total pendapatan vs pengeluaran operasional → SHU</li>
          <li><i class="bi bi-printer-fill"></i> Semua laporan bisa dicetak langsung dari browser</li>
        </ul>
      </div>
    </div>

    <div class="help-step-wrap">
      <div class="help-step-num is-d010e822">6</div>
      <div class="help-step-card">
        <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
          <h5 class="mb-0">Perhitungan SHU</h5>
          <span class="help-badge is-42eb32be">Admin / Ketua</span>
        </div>
        <p>Sisa Hasil Usaha dibagikan di akhir tahun berdasarkan partisipasi anggota.</p>
        <ul class="help-sub-list">
          <li><i class="bi bi-arrow-right-circle-fill"></i> Menu <strong>SHU → Hitung SHU Baru</strong></li>
          <li><i class="bi bi-arrow-right-circle-fill"></i> Isi total SHU, persentase jasa modal dan jasa usaha</li>
          <li><i class="bi bi-calculator-fill"></i> Sistem hitung porsi per anggota otomatis</li>
          <li><i class="bi bi-lock-fill is-85f75481"></i> Klik <strong>Finalisasi</strong> setelah dikonfirmasi — tidak bisa diubah lagi</li>
        </ul>
        <div class="help-tip"><i class="bi bi-lightbulb-fill me-1"></i>
          <strong>Jasa Modal</strong> = (Simpanan Anggota ÷ Total Simpanan) × % Jasa Modal × SHU &nbsp;|&nbsp;
          <strong>Jasa Usaha</strong> = (Pinjaman Anggota ÷ Total Pinjaman) × % Jasa Usaha × SHU
        </div>
      </div>
    </div>

  </div>

  {{-- ══ MODUL ANGGOTA ══ --}}
  <div class="form-card mb-4" id="modul-anggota">
    <div class="help-section-title">Modul Anggota</div>
    <div class="row g-3">
      <div class="col-sm-6">
        <div class="help-shortcut">
          <i class="bi bi-person-plus-fill"></i>
          <div><strong>Tambah Anggota</strong><br><span class="text-muted is-0de4e77b">Isi form manual satu per satu</span></div>
        </div>
      </div>
      <div class="col-sm-6">
        <div class="help-shortcut">
          <i class="bi bi-box-arrow-in-down"></i>
          <div><strong>Import CSV</strong><br><span class="text-muted is-0de4e77b">Banyak anggota sekaligus dari file CSV</span></div>
        </div>
      </div>
      <div class="col-sm-6">
        <div class="help-shortcut">
          <i class="bi bi-card-text"></i>
          <div><strong>Rekap Anggota</strong><br><span class="text-muted is-0de4e77b">Detail simpanan &amp; pinjaman per anggota</span></div>
        </div>
      </div>
      <div class="col-sm-6">
        <div class="help-shortcut">
          <i class="bi bi-credit-card-fill"></i>
          <div><strong>Kartu Anggota</strong><br><span class="text-muted is-0de4e77b">Cetak kartu identitas (print-ready)</span></div>
        </div>
      </div>
    </div>
    <div class="mt-3">
      <p class="mb-2 fw-600 is-58180d3d">Panduan Import CSV:</p>
      <ul class="help-sub-list">
        <li><i class="bi bi-1-circle-fill"></i> Klik <strong>Download Template</strong> di halaman Anggota untuk mendapat format yang benar</li>
        <li><i class="bi bi-2-circle-fill"></i> Isi data di spreadsheet. Pisah kolom dengan <strong>titik koma ( ; )</strong> bukan koma</li>
        <li><i class="bi bi-3-circle-fill"></i> Format tanggal: <code>YYYY-MM-DD</code> contoh: <code>1990-05-17</code></li>
        <li><i class="bi bi-4-circle-fill"></i> Simpan sebagai <code>.csv</code> lalu upload melalui form Import</li>
        <li><i class="bi bi-5-circle-fill"></i> Sistem menampilkan laporan baris yang berhasil, dilewati (NIK duplikat), dan error</li>
      </ul>
    </div>
  </div>

  {{-- ══ MODUL SIMPANAN ══ --}}
  <div class="form-card mb-4" id="modul-simpanan">
    <div class="help-section-title">Modul Simpanan &amp; Penarikan</div>
    <div class="row g-3 mb-3">
      <div class="col-sm-4">
        <div class="p-3 rounded-3 text-center is-7359c1bc">
          <i class="bi bi-piggy-bank-fill text-success fs-4"></i>
          <div class="fw-700 mt-1 is-1e28117f">Pokok</div>
          <div class="text-muted is-bb0425a5">Sekali saat bergabung</div>
        </div>
      </div>
      <div class="col-sm-4">
        <div class="p-3 rounded-3 text-center is-77a5cf4b">
          <i class="bi bi-piggy-bank-fill text-primary fs-4"></i>
          <div class="fw-700 mt-1 is-1e28117f">Wajib</div>
          <div class="text-muted is-bb0425a5">Rutin setiap bulan</div>
        </div>
      </div>
      <div class="col-sm-4">
        <div class="p-3 rounded-3 text-center is-28f4201f">
          <i class="bi bi-piggy-bank-fill text-warning fs-4"></i>
          <div class="fw-700 mt-1 is-1e28117f">Sukarela</div>
          <div class="text-muted is-bb0425a5">Bebas, bisa ditarik</div>
        </div>
      </div>
    </div>
    <ul class="help-sub-list">
      <li><i class="bi bi-arrow-down-circle-fill is-2a4cda41"></i>
        <span><strong>Penarikan Simpanan Sukarela:</strong> Menu Simpanan → cari anggota → klik <strong>Tarik</strong>. Masukkan jumlah yang tidak melebihi saldo sukarela.</span>
      </li>
      <li><i class="bi bi-x-circle-fill is-bc8099b4"></i>
        Simpanan <strong>Pokok</strong> dan <strong>Wajib</strong> tidak bisa ditarik — hanya dikembalikan saat anggota keluar/mengundurkan diri.
      </li>
    </ul>
    <div class="help-warn"><i class="bi bi-exclamation-triangle-fill me-1"></i>
      Saldo simpanan sukarela tidak bisa negatif. Sistem menolak penarikan jika jumlah melebihi saldo.
    </div>
  </div>

  {{-- ══ MODUL PINJAMAN ══ --}}
  <div class="form-card mb-4" id="modul-pinjaman">
    <div class="help-section-title">Modul Pinjaman</div>
    <ul class="help-sub-list">
      <li><i class="bi bi-arrow-right-circle-fill"></i> Pengajuan bisa oleh <strong>admin</strong> atas nama anggota, atau oleh <strong>anggota</strong> sendiri jika punya akun login</li>
      <li><i class="bi bi-arrow-right-circle-fill"></i> Bunga, tenor maksimal, dan pinjaman maksimal diatur di <strong>Pengaturan → Simpan Pinjam</strong></li>
      <li><i class="bi bi-arrow-right-circle-fill"></i> Setiap perubahan status pinjaman tercatat di <strong>History Log</strong></li>
      <li><i class="bi bi-arrow-right-circle-fill"></i> Pinjaman yang sudah <span class="badge bg-success">Disetujui</span> tidak bisa dihapus — hanya bisa lunas melalui angsuran</li>
    </ul>
    <div class="help-tip"><i class="bi bi-lightbulb-fill me-1"></i>
      Jadwal angsuran dibuat otomatis setelah pinjaman disetujui. Bisa dilihat di halaman detail pinjaman.
    </div>
  </div>

  {{-- ══ MODUL ANGSURAN ══ --}}
  <div class="form-card mb-4" id="modul-angsuran">
    <div class="help-section-title">Modul Angsuran</div>
    <ul class="help-sub-list">
      <li><i class="bi bi-calendar-check-fill"></i> Jadwal angsuran dibuat otomatis saat pinjaman disetujui</li>
      <li><i class="bi bi-arrow-right-circle-fill"></i> Setiap baris berisi: ke-berapa, tanggal jatuh tempo, pokok, bunga, total bayar</li>
      <li><i class="bi bi-alarm-fill is-bc8099b4"></i> Jika melewati jatuh tempo, klik Bayar → sistem menambahkan denda otomatis</li>
      <li><i class="bi bi-speedometer2"></i> Menu <strong>Monitoring → Angsuran</strong> menampilkan cicilan yang hampir/sudah jatuh tempo di semua anggota</li>
    </ul>
  </div>

  {{-- ══ MODUL PENDAPATAN & PENGELUARAN ══ --}}
  <div class="form-card mb-4" id="modul-keuangan">
    <div class="help-section-title">Pendapatan &amp; Pengeluaran Operasional</div>
    <div class="row g-3">
      <div class="col-sm-6">
        <div class="help-shortcut is-9f4de805">
          <i class="bi bi-graph-up-arrow text-success"></i>
          <div>
            <strong>Kategori Pendapatan</strong>
            <ul class="help-sub-list mt-1 is-0de4e77b">
              <li class="is-6d9a1d17"><i class="bi bi-dot is-deccf9e7"></i>Jasa Administrasi</li>
              <li class="is-6d9a1d17"><i class="bi bi-dot is-deccf9e7"></i>Provisi Pinjaman</li>
              <li class="is-6d9a1d17"><i class="bi bi-dot is-deccf9e7"></i>Bunga Pinjaman / Simpanan</li>
              <li class="is-6d9a1d17"><i class="bi bi-dot is-deccf9e7"></i>Denda, Hibah, Iuran, Lain-lain</li>
            </ul>
          </div>
        </div>
      </div>
      <div class="col-sm-6">
        <div class="help-shortcut is-81b29be3">
          <i class="bi bi-graph-down-arrow text-danger"></i>
          <div>
            <strong>Kategori Pengeluaran</strong>
            <ul class="help-sub-list mt-1 is-0de4e77b">
              <li class="is-6d9a1d17"><i class="bi bi-dot is-bc8099b4"></i>Gaji Pengurus &amp; Karyawan</li>
              <li class="is-6d9a1d17"><i class="bi bi-dot is-bc8099b4"></i>ATK, Listrik, Sewa, Transportasi</li>
              <li class="is-6d9a1d17"><i class="bi bi-dot is-bc8099b4"></i>Pelatihan, Rapat, Sosial, Lain-lain</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
    <div class="help-tip mt-3"><i class="bi bi-lightbulb-fill me-1"></i>
      Data Pendapatan dan Pengeluaran menjadi dasar perhitungan <strong>Laporan Laba Rugi</strong> dan <strong>SHU</strong> tahunan.
    </div>
  </div>

  {{-- ══ MODUL LAPORAN ══ --}}
  <div class="form-card mb-4" id="modul-laporan">
    <div class="help-section-title">Laporan, Neraca &amp; Laba Rugi</div>
    <div class="row g-3">
      <div class="col-sm-4">
        <div class="help-shortcut">
          <i class="bi bi-file-earmark-bar-graph-fill"></i>
          <div>
            <strong>Laporan Keuangan</strong>
            <div class="text-muted is-6c660c40">Arus kas simpanan, pinjaman, angsuran</div>
          </div>
        </div>
      </div>
      <div class="col-sm-4">
        <div class="help-shortcut">
          <i class="bi bi-balance-scale-fill"></i>
          <div>
            <strong>Neraca</strong>
            <div class="text-muted is-6c660c40">Aset vs Kewajiban + Ekuitas per tahun</div>
          </div>
        </div>
      </div>
      <div class="col-sm-4">
        <div class="help-shortcut">
          <i class="bi bi-graph-up"></i>
          <div>
            <strong>Laba Rugi</strong>
            <div class="text-muted is-6c660c40">Pendapatan − Pengeluaran = SHU</div>
          </div>
        </div>
      </div>
    </div>
    <ul class="help-sub-list mt-3">
      <li><i class="bi bi-arrow-right-circle-fill"></i> Semua laporan bisa difilter per <strong>tahun</strong> dan/atau <strong>bulan</strong></li>
      <li><i class="bi bi-printer-fill"></i> Klik <strong>Cetak</strong> → browser membuka tampilan cetak (sidebar &amp; tombol tersembunyi otomatis)</li>
      <li><i class="bi bi-download"></i> Laporan Keuangan memiliki halaman <strong>Cetak Laporan</strong> berformat surat resmi berkop koperasi</li>
    </ul>
  </div>

  {{-- ══ MODUL SHU ══ --}}
  <div class="form-card mb-4" id="modul-shu">
    <div class="help-section-title">Modul SHU (Sisa Hasil Usaha)</div>
    <ul class="help-sub-list">
      <li><i class="bi bi-1-circle-fill"></i> Buka <strong>SHU → Hitung SHU Baru</strong>, pilih tahun</li>
      <li><i class="bi bi-2-circle-fill"></i> Isi total SHU yang akan dibagi, % jasa modal, % jasa usaha, % dana cadangan</li>
      <li><i class="bi bi-3-circle-fill"></i> Klik <strong>Simpan sebagai Draft</strong> — bisa diubah selama masih draft</li>
      <li><i class="bi bi-4-circle-fill"></i> Setelah diverifikasi, klik <strong>Finalisasi</strong> — status menjadi <span class="badge bg-success">Final</span></li>
      <li><i class="bi bi-5-circle-fill"></i> Porsi SHU per anggota bisa dilihat di halaman detail SHU</li>
    </ul>
    <div class="help-warn"><i class="bi bi-exclamation-triangle-fill me-1"></i>
      SHU yang sudah <span class="badge bg-success">Final</span> <strong>tidak bisa diedit atau dihapus</strong>.
      Pastikan semua data benar sebelum finalisasi.
    </div>
  </div>

  {{-- ══ MODUL USER ══ --}}
  <div class="form-card mb-4" id="modul-user">
    <div class="help-section-title">Manajemen Pengguna &amp; Role</div>
    <div class="row g-3">
      <div class="col-sm-6">
        <div class="help-shortcut">
          <i class="bi bi-people-fill"></i>
          <div>
            <strong>Pengguna</strong>
            <div class="text-muted is-6c660c40">Buat/edit akun login untuk admin, petugas, atau anggota</div>
          </div>
        </div>
      </div>
      <div class="col-sm-6">
        <div class="help-shortcut">
          <i class="bi bi-shield-lock-fill"></i>
          <div>
            <strong>Role &amp; Permission</strong>
            <div class="text-muted is-6c660c40">Atur hak akses per role (Admin, Ketua, Petugas, Anggota)</div>
          </div>
        </div>
      </div>
    </div>
    <ul class="help-sub-list mt-3">
      <li><i class="bi bi-arrow-right-circle-fill"></i> Akun anggota bisa dibuat langsung saat input data anggota baru</li>
      <li><i class="bi bi-arrow-right-circle-fill"></i> Setiap pengguna dikaitkan ke satu role; role menentukan menu dan aksi yang bisa diakses</li>
      <li><i class="bi bi-person-circle"></i> Pengguna dapat mengubah nama, email, nomor WA, dan password melalui menu <strong>Profil Saya</strong></li>
    </ul>
  </div>

  {{-- ══ TIPS ══ --}}
  <div class="form-card" id="tips">
    <div class="help-section-title">Tips</div>
    <div class="row g-3">
      <div class="col-sm-6">
        <div class="help-shortcut">
          <i class="bi bi-funnel-fill"></i>
          <div><strong>Filter Aktif = Export Sesuai</strong><br><span class="text-muted is-0de4e77b">Filter yang Anda terapkan ikut terbawa saat export CSV</span></div>
        </div>
      </div>
      <div class="col-sm-6">
        <div class="help-shortcut">
          <i class="bi bi-clock-history"></i>
          <div><strong>History Log</strong><br><span class="text-muted is-0de4e77b">Semua perubahan data tercatat lengkap dengan waktu dan pelaku</span></div>
        </div>
      </div>
      <div class="col-sm-6">
        <div class="help-shortcut">
          <i class="bi bi-search"></i>
          <div><strong>Pencarian</strong><br><span class="text-muted is-0de4e77b">Cari nama atau nomor anggota di semua halaman daftar</span></div>
        </div>
      </div>
      <div class="col-sm-6">
        <div class="help-shortcut">
          <i class="bi bi-gear-fill"></i>
          <div><strong>Pengaturan Koperasi</strong><br><span class="text-muted is-0de4e77b">Nama, logo, bunga, tenor, dan nominal simpanan wajib bisa diubah kapan saja</span></div>
        </div>
      </div>
    </div>
  </div>

</div>{{-- end col kiri --}}

{{-- ═══════════════════════════════════════
     KOLOM KANAN — Info Cepat
════════════════════════════════════════ --}}
<div class="col-lg-4">

  {{-- Hak Akses --}}
  <div class="form-card mb-4">
    <div class="help-section-title">Hak Akses per Role</div>
    <div class="d-flex flex-column gap-3">

      <div class="help-role-card">
        <h6><i class="bi bi-shield-fill-check me-2 text-danger"></i>Admin</h6>
        <ul>
          <li>Akses penuh ke seluruh menu</li>
          <li>Verifikasi &amp; setujui pinjaman</li>
          <li>Setujui pendaftaran anggota</li>
          <li>Kelola pengaturan, role &amp; pengguna</li>
          <li>Input pendapatan &amp; pengeluaran</li>
          <li>Hitung dan finalisasi SHU</li>
        </ul>
      </div>

      <div class="help-role-card">
        <h6><i class="bi bi-person-badge-fill me-2 text-primary"></i>Ketua</h6>
        <ul>
          <li>Persetujuan akhir pinjaman</li>
          <li>Lihat laporan, neraca &amp; SHU</li>
          <li>Lihat pendapatan koperasi</li>
          <li>Lihat monitoring simpanan &amp; angsuran</li>
        </ul>
      </div>

      <div class="help-role-card">
        <h6><i class="bi bi-person-fill me-2 text-success"></i>Petugas</h6>
        <ul>
          <li>Input simpanan &amp; angsuran</li>
          <li>Verifikasi pinjaman (tahap 1)</li>
          <li>Lihat data anggota &amp; laporan</li>
        </ul>
      </div>

      <div class="help-role-card">
        <h6><i class="bi bi-person-circle me-2 text-warning"></i>Anggota</h6>
        <ul>
          <li>Lihat simpanan &amp; pinjaman pribadi</li>
          <li>Lihat jadwal angsuran sendiri</li>
          <li>Dashboard rekap pribadi</li>
        </ul>
      </div>

    </div>
  </div>

  {{-- Status Badge --}}
  <div class="form-card mb-4">
    <div class="help-section-title">Arti Status</div>

    <div class="mb-3">
      <p class="fw-600 mb-2 is-d6d66658">Anggota</p>
      <div class="d-flex flex-wrap gap-2">
        <span class="badge bg-success">Aktif</span>
        <span class="badge bg-danger">Non-Aktif</span>
      </div>
    </div>

    <div class="mb-3">
      <p class="fw-600 mb-2 is-d6d66658">Pinjaman</p>
      <div class="d-flex flex-wrap gap-2">
        <span class="badge bg-secondary">Diajukan</span>
        <span class="badge bg-info text-dark">Diverifikasi</span>
        <span class="badge bg-success">Disetujui</span>
        <span class="badge bg-danger">Ditolak</span>
        <span class="badge bg-dark">Lunas</span>
      </div>
    </div>

    <div class="mb-3">
      <p class="fw-600 mb-2 is-d6d66658">Angsuran</p>
      <div class="d-flex flex-wrap gap-2">
        <span class="badge bg-warning text-dark">Belum</span>
        <span class="badge bg-success">Sudah</span>
        <span class="badge bg-danger">Terlambat</span>
      </div>
    </div>

    <div class="mb-3">
      <p class="fw-600 mb-2 is-d6d66658">SHU</p>
      <div class="d-flex flex-wrap gap-2">
        <span class="badge bg-warning text-dark">Draft</span>
        <span class="badge bg-success">Final</span>
      </div>
    </div>

    <div>
      <p class="fw-600 mb-2 is-d6d66658">Simpanan Transaksi</p>
      <div class="d-flex flex-wrap gap-2">
        <span class="badge bg-success">Setor</span>
        <span class="badge bg-warning text-dark">Tarik</span>
      </div>
    </div>
  </div>

  {{-- Export & Cetak --}}
  <div class="form-card mb-4">
    <div class="help-section-title">Export &amp; Cetak</div>
    <ul class="help-sub-list">
      <li>
        <i class="bi bi-file-earmark-spreadsheet"></i>
        <span><strong>Export CSV</strong> tersedia di: Anggota, Simpanan, Pinjaman, Angsuran, Pengeluaran. Filter aktif ikut terbawa.</span>
      </li>
      <li>
        <i class="bi bi-download"></i>
        <span><strong>Template Anggota</strong> bisa diunduh di halaman Import — sudah berformat titik koma (;).</span>
      </li>
      <li>
        <i class="bi bi-printer-fill"></i>
        <span><strong>Cetak Laporan</strong> menghasilkan surat resmi berkop koperasi.</span>
      </li>
      <li>
        <i class="bi bi-credit-card-fill"></i>
        <span><strong>Kartu Anggota</strong> bisa dicetak dari halaman Rekap → tombol Kartu.</span>
      </li>
    </ul>
  </div>

  {{-- Catatan Keamanan --}}
  <div class="form-card">
    <div class="help-section-title">Catatan Penting</div>
    <ul class="help-sub-list">
      <li><i class="bi bi-clock-history"></i> Semua perubahan data tercatat di menu <strong>History Log</strong></li>
      <li><i class="bi bi-arrow-counterclockwise"></i> <strong>Data yang dihapus tidak dapat dipulihkan</strong> — pastikan sudah yakin sebelum menghapus</li>
      <li><i class="bi bi-shield-check"></i> Ubah kata sandi melalui menu <strong>Profil Saya</strong></li>
      <li><i class="bi bi-lock-fill"></i> SHU yang sudah berstatus <strong>Final</strong> tidak dapat diubah kembali</li>
      <li><i class="bi bi-exclamation-triangle-fill is-2a4cda41"></i> Pinjaman yang sudah <strong>Disetujui</strong> tidak dapat dihapus</li>
    </ul>
  </div>

</div>{{-- end col kanan --}}

</div>{{-- end row --}}

@endsection
