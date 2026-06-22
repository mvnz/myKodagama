@extends('layouts.aplikasi')

@section('judul', 'Pengaturan Aplikasi')

@section('breadcrumb')
  <li class="breadcrumb-item active">Pengaturan Aplikasi</li>
@endsection

@section('konten')

<div class="row justify-content-center">
  <div class="col-xl-10">

    {{-- Page Banner --}}
    <div class="dashboard-welcome mb-4">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div>
          <div class="welcome-label mb-1">Pengaturan Sistem</div>
          <h4 class="mb-1 fw-700">Pengaturan Aplikasi</h4>
          <p class="mb-0 welcome-date">
            <i class="bi bi-calendar3 me-1"></i>
            {{ now()->translatedFormat('l, d F Y') }}
          </p>
        </div>
        <div class="welcome-icon-wrap">
            <i class="bi bi-sliders"></i>
          </div>
      </div>
    </div>

    <form action="{{ route('pengaturan.aplikasi.update') }}" method="POST" id="formPengaturan">
      @csrf
      @method('PUT')

      @if(session('sukses'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
          <i class="bi bi-check-circle-fill"></i>
          <span>{{ session('sukses') }}</span>
          <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
      @endif

      <div class="row g-4">

        {{-- ── Profil Koperasi ─────────────────────────────────────── --}}
        <div class="col-12">
          <div class="form-card">
            <h6 class="fw-700 mb-3 d-flex align-items-center gap-2 is-bc519fd0">
              <i class="bi bi-building"></i> Profil Koperasi
            </h6>
            <div class="row g-3">

              <div class="col-md-6">
                <label class="form-label">Sub Nama / Jenis</label>
                @php $item = $pengaturan->get('sub_nama') @endphp
                <input type="text" class="form-control" name="pengaturan[sub_nama]"
                  value="{{ old('pengaturan.sub_nama', $item?->nilai) }}"
                  placeholder="contoh: Koperasi Simpan Pinjam" maxlength="100">
                <div class="form-text">Ditampilkan di bawah nama koperasi pada logo.</div>
              </div>

              <div class="col-12">
                <label class="form-label">Tagline</label>
                @php $item = $pengaturan->get('tagline') @endphp
                <input type="text" class="form-control" name="pengaturan[tagline]"
                  value="{{ old('pengaturan.tagline', $item?->nilai) }}"
                  placeholder="contoh: Keuangan Lebih Baik Bersama Koperasi Terpercaya" maxlength="200">
                <div class="form-text">Judul besar pada halaman beranda (hero section).</div>
              </div>

              <div class="col-12">
                <label class="form-label">Deskripsi Singkat</label>
                @php $item = $pengaturan->get('deskripsi') @endphp
                <textarea class="form-control" name="pengaturan[deskripsi]" rows="3"
                  placeholder="Deskripsi singkat koperasi..." maxlength="500">{{ old('pengaturan.deskripsi', $item?->nilai) }}</textarea>
                <div class="form-text">Paragraf deskripsi yang muncul di bawah tagline.</div>
              </div>

              <div class="col-md-6">
                <label class="form-label">Tahun Berdiri</label>
                @php $item = $pengaturan->get('tahun_berdiri') @endphp
                <input type="text" class="form-control" name="pengaturan[tahun_berdiri]"
                  value="{{ old('pengaturan.tahun_berdiri', $item?->nilai) }}"
                  placeholder="contoh: 2005" maxlength="20">
              </div>

              <div class="col-md-6">
                <label class="form-label">Slogan Resmi</label>
                @php $item = $pengaturan->get('slogan_resmi') @endphp
                <input type="text" class="form-control" name="pengaturan[slogan_resmi]"
                  value="{{ old('pengaturan.slogan_resmi', $item?->nilai) }}"
                  placeholder="contoh: Terdaftar Kementerian Koperasi RI" maxlength="150">
                <div class="form-text">Ditampilkan di topbar dan footer.</div>
              </div>

            </div>
          </div>
        </div>

        {{-- ── Kontak ─────────────────────────────────────────────── --}}
        <div class="col-12">
          <div class="form-card">
            <h6 class="fw-700 mb-3 d-flex align-items-center gap-2 is-bc519fd0">
              <i class="bi bi-telephone-fill"></i> Informasi Kontak
            </h6>
            <div class="row g-3">

              <div class="col-md-6">
                <label class="form-label">Nomor Telepon</label>
                @php $item = $pengaturan->get('telepon') @endphp
                <div class="input-group">
                  <span class="input-group-text"><i class="bi bi-telephone-fill"></i></span>
                  <input type="text" class="form-control" name="pengaturan[telepon]"
                    value="{{ old('pengaturan.telepon', $item?->nilai) }}"
                    placeholder="contoh: (0274) 123-4567" maxlength="30">
                </div>
              </div>

              <div class="col-md-6">
                <label class="form-label">Email</label>
                @php $item = $pengaturan->get('email') @endphp
                <div class="input-group">
                  <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
                  <input type="email" class="form-control" name="pengaturan[email]"
                    value="{{ old('pengaturan.email', $item?->nilai) }}"
                    placeholder="contoh: info@koperasi.id" maxlength="150">
                </div>
              </div>

              <div class="col-md-6">
                <label class="form-label">Jam Operasional</label>
                @php $item = $pengaturan->get('jam_operasional') @endphp
                <div class="input-group">
                  <span class="input-group-text"><i class="bi bi-clock-fill"></i></span>
                  <input type="text" class="form-control" name="pengaturan[jam_operasional]"
                    value="{{ old('pengaturan.jam_operasional', $item?->nilai) }}"
                    placeholder="contoh: Senin–Jumat, 08.00–16.00" maxlength="60">
                </div>
              </div>

              <div class="col-md-6">
                <label class="form-label">Alamat</label>
                @php $item = $pengaturan->get('alamat') @endphp
                <div class="input-group">
                  <span class="input-group-text"><i class="bi bi-geo-alt-fill"></i></span>
                  <input type="text" class="form-control" name="pengaturan[alamat]"
                    value="{{ old('pengaturan.alamat', $item?->nilai) }}"
                    placeholder="contoh: Jl. Koperasi No. 1, Yogyakarta" maxlength="200">
                </div>
              </div>

            </div>
          </div>
        </div>

        {{-- ── Statistik Beranda ──────────────────────────────────── --}}
        <div class="col-12">
          <div class="form-card">
            <h6 class="fw-700 mb-3 d-flex align-items-center gap-2 is-bc519fd0">
              <i class="bi bi-bar-chart-fill"></i> Statistik Beranda
            </h6>
            <p class="text-muted small mb-3">
              Angka-angka ini ditampilkan di hero section halaman beranda sebagai daya tarik bagi pengunjung.
            </p>
            <div class="row g-3">

              <div class="col-md-4">
                <label class="form-label">Jumlah Anggota Aktif</label>
                @php $item = $pengaturan->get('jumlah_anggota') @endphp
                <div class="input-group">
                  <span class="input-group-text"><i class="bi bi-people-fill"></i></span>
                  <input type="text" class="form-control" name="pengaturan[jumlah_anggota]"
                    value="{{ old('pengaturan.jumlah_anggota', $item?->nilai) }}"
                    placeholder="contoh: 2.500+" maxlength="20">
                </div>
              </div>

              <div class="col-md-4">
                <label class="form-label">Bunga Pinjaman / Bulan</label>
                @php $item = $pengaturan->get('bunga_pinjaman') @endphp
                <div class="input-group">
                  <span class="input-group-text"><i class="bi bi-percent"></i></span>
                  <input type="text" class="form-control" name="pengaturan[bunga_pinjaman]"
                    value="{{ old('pengaturan.bunga_pinjaman', $item?->nilai) }}"
                    placeholder="contoh: 1,5%" maxlength="20">
                </div>
              </div>

              <div class="col-md-4">
                <label class="form-label">Tahun Beroperasi</label>
                @php $item = $pengaturan->get('tahun_operasi') @endphp
                <div class="input-group">
                  <span class="input-group-text"><i class="bi bi-calendar-check-fill"></i></span>
                  <input type="text" class="form-control" name="pengaturan[tahun_operasi]"
                    value="{{ old('pengaturan.tahun_operasi', $item?->nilai) }}"
                    placeholder="contoh: 20+" maxlength="20">
                </div>
              </div>

            </div>
          </div>
        </div>

      </div>{{-- /row --}}

      {{-- Actions --}}
      <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-primary px-4">
          <i class="bi bi-check-lg me-1"></i> Simpan Perubahan
        </button>
        <a href="{{ route('beranda') }}" target="_blank" class="btn btn-outline-secondary px-4">
          <i class="bi bi-eye me-1"></i> Pratinjau Beranda
        </a>
      </div>

    </form>

  </div>
</div>
@endsection
