@extends('layouts.aplikasi')

@section('judul', 'Rekonsiliasi Angsuran')

@push('css')
<link rel="stylesheet" href="{{ asset('css/pages/rekon-angsuran.css') }}">
@endpush

@section('konten')
<div class="row justify-content-center">
  <div class="col-xl-9">

    @if(session('sukses'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
      <i class="bi bi-check-circle-fill me-2"></i>{{ session('sukses') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('gagal'))
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
      <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('gagal') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="form-card">
      <div class="d-flex align-items-center gap-3 mb-4 pb-4 border-bottom">
        <div class="rekon-angsuran-hero-icon d-flex align-items-center justify-content-center rounded-3">
          <i class="bi bi-file-earmark-arrow-up-fill rekon-angsuran-hero-icon-i"></i>
        </div>
        <div>
          <h5 class="mb-0 fw-700 rekon-angsuran-title">Rekonsiliasi Angsuran</h5>
          <small class="text-muted">Import pembayaran angsuran pinjaman dari file mutasi bank (CSV)</small>
        </div>
      </div>

      <form action="{{ route('rekon-angsuran.preview') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- Upload File --}}
        <div class="mb-4">
          <label class="form-label fw-600">File Mutasi Bank (CSV) <span class="text-danger">*</span></label>
          <input type="file" name="file_csv" accept=".csv,.txt"
            class="form-control @error('file_csv') is-invalid @enderror">
          @error('file_csv')<div class="invalid-feedback">{{ $message }}</div>@enderror
          <small class="text-muted">Format: CSV, maks 5 MB. Delimiter koma (,) atau titik koma (;) terdeteksi otomatis.</small>
        </div>

        {{-- Mapping Kolom --}}
        <div class="mb-4 p-3 rounded-3 rekon-angsuran-map-box">
          <p class="fw-600 mb-3 rekon-angsuran-title"><i class="bi bi-table me-1"></i> Pemetaan Kolom CSV</p>
          <small class="text-muted d-block mb-3">
            Tentukan indeks kolom (mulai dari 0). Contoh: kolom pertama = 0, kedua = 1, dst.
          </small>
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Kolom Tanggal</label>
              <input type="number" name="kol_tanggal" value="{{ old('kol_tanggal', 0) }}" min="0"
                class="form-control @error('kol_tanggal') is-invalid @enderror">
              @error('kol_tanggal')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
              <label class="form-label">Kolom Berita / Keterangan</label>
              <input type="number" name="kol_berita" value="{{ old('kol_berita', 1) }}" min="0"
                class="form-control @error('kol_berita') is-invalid @enderror">
              @error('kol_berita')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
              <label class="form-label">Kolom Nominal / Kredit</label>
              <input type="number" name="kol_nominal" value="{{ old('kol_nominal', 2) }}" min="0"
                class="form-control @error('kol_nominal') is-invalid @enderror">
              @error('kol_nominal')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
              <label class="form-label">Jumlah Baris Header</label>
              <input type="number" name="baris_header" value="{{ old('baris_header', 1) }}" min="0"
                class="form-control @error('baris_header') is-invalid @enderror">
              @error('baris_header')<div class="invalid-feedback">{{ $message }}</div>@enderror
              <small class="text-muted">Berapa baris pertama yang dilewati</small>
            </div>
          </div>
        </div>

        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-warning px-4 text-white">
            <i class="bi bi-search me-2"></i>Proses & Pratinjau
          </button>
          <a href="{{ route('rekon-angsuran.template') }}" class="btn btn-outline-success px-4">
            <i class="bi bi-download me-2"></i>Download Template
          </a>
          <a href="{{ route('angsuran.index') }}" class="btn btn-outline-secondary px-4">Batal</a>
        </div>
      </form>
    </div>

    {{-- Format Kolom --}}
    <div class="form-card mt-4">
      <h6 class="fw-700 mb-3 rekon-angsuran-title">
        <i class="bi bi-table me-2 rekon-angsuran-icon-amber"></i>Format Kolom CSV
      </h6>

      <div class="table-responsive mb-4">
        <table class="koperasi-table mb-0">
          <thead>
            <tr>
              <th>Nama Kolom</th>
              <th>Wajib</th>
              <th>Format / Nilai</th>
              <th>Contoh</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><code>Posting Date</code></td>
              <td><span class="badge bg-danger">Wajib</span></td>
              <td>Tanggal transaksi bank (YYYY-MM-DD, DD/MM/YYYY, atau format tanggal umum)</td>
              <td><code>2026-06-19</code></td>
            </tr>
            <tr>
              <td><code>Remark</code></td>
              <td><span class="badge bg-danger">Wajib</span></td>
              <td>Harus berformat <code>angsuran-{ke}-{nomor_anggota}</code></td>
              <td><code>angsuran-3-KOP-2026-0001</code></td>
            </tr>
            <tr>
              <td><code>Credit</code></td>
              <td><span class="badge bg-danger">Wajib</span></td>
              <td>Nominal pembayaran masuk (angka). Simbol seperti <code>Rp</code>, titik, koma akan dibersihkan.</td>
              <td><code>250000</code></td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="alert alert-info d-flex gap-2 mb-0" role="alert">
        <i class="bi bi-lightbulb-fill mt-1"></i>
        <div class="small">
          Jika header file menggunakan <strong>Posting Date</strong>, <strong>Remark</strong>, dan <strong>Credit</strong>,
          sistem akan otomatis membaca kolom tersebut. Jika nama kolom berbeda, gunakan input indeks di bagian
          <strong>Pemetaan Kolom CSV</strong>.
        </div>
      </div>
    </div>

    {{-- Petunjuk --}}
    <div class="form-card mt-4">
      <h6 class="fw-700 mb-3 rekon-angsuran-title"><i class="bi bi-info-circle-fill me-2 text-warning"></i>Petunjuk Format CSV</h6>
      <ul class="mb-0 rekon-angsuran-help-list">
        <li>Jika header CSV berisi <strong>Posting Date</strong>, <strong>Remark</strong>, dan <strong>Credit</strong>, sistem akan otomatis memetakan kolom tersebut.</li>
        <li>Kolom <strong>Berita/Keterangan</strong> harus menggunakan format: <code>angsuran-{ke}-{nomor_anggota}</code></li>
        <li>Contoh: <code>angsuran-3-KOP-2026-0001</code> → pembayaran angsuran ke-3 anggota KOP-2026-0001</li>
        <li>Angsuran yang sudah berstatus <strong>lunas</strong> akan dilewati otomatis.</li>
        <li>Denda keterlambatan dihitung otomatis berdasarkan tanggal jatuh tempo vs tanggal bayar.</li>
        <li>Kolom nominal hanya digunakan sebagai referensi — yang tercatat adalah jumlah tagihan dari sistem.</li>
      </ul>
    </div>

  </div>
</div>
@endsection
