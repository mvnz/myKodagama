@extends('layouts.aplikasi')

@section('judul', 'Rekonsiliasi Simpanan')

@push('css')
<link rel="stylesheet" href="{{ asset('css/pages/rekon-simpanan.css') }}">
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
        <div class="rekon-simpanan-hero-icon d-flex align-items-center justify-content-center rounded-3">
          <i class="bi bi-file-earmark-arrow-up-fill rekon-simpanan-hero-icon-i"></i>
        </div>
        <div>
          <h5 class="mb-0 fw-700 rekon-title">Rekonsiliasi Simpanan</h5>
          <small class="text-muted">Import simpanan dari file mutasi bank (CSV)</small>
        </div>
      </div>

      <form action="{{ route('rekon.preview') }}" method="POST" enctype="multipart/form-data">
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
        <div class="mb-4 p-3 rounded-3 rekon-map-box">
          <p class="fw-600 mb-3 rekon-title"><i class="bi bi-table me-1"></i> Pemetaan Kolom CSV</p>
          <small class="text-muted d-block mb-3">
            Tentukan indeks kolom (mulai dari 0). Contoh: jika kolom pertama = 0, kedua = 1, dst.
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
              <small class="text-muted">Berapa baris pertama yang dilewati (baris judul kolom)</small>
            </div>
          </div>
        </div>

        {{-- Kata Kunci Jenis Simpanan --}}
        <div class="mb-4 p-3 rounded-3 rekon-map-box">
          <p class="fw-600 mb-3 rekon-title"><i class="bi bi-key-fill me-1"></i> Kata Kunci Jenis Simpanan</p>
          <small class="text-muted d-block mb-3">
            Kata kunci yang dicari di kolom berita untuk menentukan jenis simpanan (tidak peka huruf besar/kecil).
          </small>
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Kata Kunci Simpanan Wajib</label>
              <input type="text" name="kata_wajib" value="{{ old('kata_wajib', 'wajib') }}"
                class="form-control @error('kata_wajib') is-invalid @enderror" placeholder="wajib">
              @error('kata_wajib')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
              <label class="form-label">Kata Kunci Simpanan Pokok</label>
              <input type="text" name="kata_pokok" value="{{ old('kata_pokok', 'pokok') }}"
                class="form-control @error('kata_pokok') is-invalid @enderror" placeholder="pokok">
              @error('kata_pokok')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
              <label class="form-label">Kata Kunci Simpanan Sukarela</label>
              <input type="text" name="kata_sukarela" value="{{ old('kata_sukarela', 'sukarela') }}"
                class="form-control @error('kata_sukarela') is-invalid @enderror" placeholder="sukarela">
              @error('kata_sukarela')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>
        </div>

        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-primary px-4">
            <i class="bi bi-search me-2"></i>Proses & Pratinjau
          </button>
          <a href="{{ route('rekon.template') }}" class="btn btn-outline-success px-4">
            <i class="bi bi-download me-2"></i>Download Template
          </a>
          <a href="{{ route('simpanan.index') }}" class="btn btn-outline-secondary px-4">Batal</a>
        </div>
      </form>
    </div>

    {{-- Format Kolom --}}
    <div class="form-card mt-4">
      <h6 class="fw-700 mb-3 rekon-title">
        <i class="bi bi-table me-2 rekon-icon-indigo"></i>Format Kolom CSV
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
              <td>Format <code>{kata_kunci}-{nomor_anggota}</code> dengan kata kunci simpanan (wajib/pokok/sukarela)</td>
              <td><code>WAJIB-KOP-2026-0001</code></td>
            </tr>
            <tr>
              <td><code>Credit</code></td>
              <td><span class="badge bg-danger">Wajib</span></td>
              <td>Nominal setoran masuk (angka). Simbol seperti <code>Rp</code>, titik, koma akan dibersihkan.</td>
              <td><code>50000</code></td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="alert alert-info d-flex gap-2 mb-0" role="alert">
        <i class="bi bi-lightbulb-fill mt-1"></i>
        <div class="small">
          Gunakan tombol <strong>Download Template</strong> agar format kolom sesuai sistem.
          Jika posisi kolom berbeda, sesuaikan indeks pada bagian <strong>Pemetaan Kolom CSV</strong>.
        </div>
      </div>
    </div>

    {{-- Petunjuk --}}
    <div class="form-card mt-4">
      <h6 class="fw-700 mb-3 rekon-title"><i class="bi bi-info-circle-fill me-2 text-primary"></i>Petunjuk Format CSV</h6>
      <ul class="mb-0 rekon-help-list">
        <li>File CSV dari mutasi bank biasanya memiliki baris header di atas. Isi <strong>Jumlah Baris Header</strong> sesuai dengan baris yang ingin dilewati.</li>
        <li>Kolom <strong>Berita/Keterangan</strong> harus menggunakan format: <code>{kata_kunci}-{nomor_anggota}</code></li>
        <li>Contoh: <code>WAJIB-KOP-2026-0001</code> atau <code>simpanan wajib-KOP-2026-0001</code></li>
        <li>Setelah kata kunci jenis simpanan, dipisahkan dengan tanda <strong>"-"</strong>, diikuti langsung nomor anggota.</li>
        <li>Kolom nominal hanya diambil angkanya — karakter seperti titik, koma, atau "Rp" diabaikan.</li>
      </ul>
    </div>

  </div>
</div>
@endsection
