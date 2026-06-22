@extends('layouts.aplikasi')

@section('judul', 'Import Anggota')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('anggota.index') }}">Anggota</a></li>
  <li class="breadcrumb-item active">Import CSV</li>
@endsection

@section('konten')
<div class="row justify-content-center">
  <div class="col-xl-8">

    {{-- Page Banner --}}
    <div class="dashboard-welcome mb-4">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div>
          <div class="welcome-label mb-1">Data Anggota</div>
          <h4 class="mb-1 fw-700">Import Anggota dari CSV</h4>
          <p class="mb-0 welcome-date">
            <i class="bi bi-calendar3 me-1"></i>
            {{ now()->translatedFormat('l, d F Y') }}
          </p>
        </div>
        <div class="welcome-icon-wrap">
          <i class="bi bi-box-arrow-in-down"></i>
        </div>
      </div>
    </div>

    @if($errors->any())
      <div class="alert alert-danger alert-dismissible fade show d-flex gap-2 mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill flex-shrink-0 mt-1"></i>
        <div>
          @foreach($errors->all() as $err)
            <div>{{ $err }}</div>
          @endforeach
        </div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
      </div>
    @endif

    {{-- Upload Form --}}
    <div class="form-card mb-4">
      <h6 class="fw-700 mb-1 is-5ca88772">
        <i class="bi bi-file-earmark-arrow-up me-2 is-bc519fd0"></i>Upload File CSV
      </h6>
      <p class="text-muted small mb-4">Pilih file CSV yang sudah sesuai format. Maksimal ukuran 2 MB.</p>

      <form action="{{ route('import.anggota') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-4">
          <label class="form-label fw-600">File CSV <span class="text-danger">*</span></label>
          <input type="file" name="file" id="fileInput" accept=".csv,text/csv"
                 class="form-control @error('file') is-invalid @enderror">
          @error('file')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
          <div class="form-text">Format yang diterima: .csv. Gunakan <strong>titik koma (;)</strong> sebagai pemisah kolom.</div>
        </div>

        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-primary px-4">
            <i class="bi bi-box-arrow-in-down me-1"></i> Proses Import
          </button>
          <a href="{{ route('export.template.anggota') }}" class="btn btn-outline-success px-4">
            <i class="bi bi-download me-1"></i> Download Template
          </a>
          <a href="{{ route('anggota.index') }}" class="btn btn-outline-secondary px-4">
            <i class="bi bi-arrow-left me-1"></i> Kembali
          </a>
        </div>
      </form>
    </div>

    {{-- Format Kolom --}}
    <div class="form-card mb-4">
      <h6 class="fw-700 mb-3 is-5ca88772">
        <i class="bi bi-table me-2 is-bc519fd0"></i>Format Kolom CSV
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
              <td><code>nomor_anggota</code></td>
              <td><span class="badge bg-secondary">Opsional</span></td>
              <td>Jika diisi: format <code>KOP-YYYY-0001</code> dan harus sesuai <code>tanggal_bergabung</code>. Jika kosong: nomor otomatis lanjut urutan terakhir.</td>
              <td><code>KOP-2026-0001</code></td>
            </tr>
            <tr>
              <td><code>nama</code></td>
              <td><span class="badge bg-danger">Wajib</span></td>
              <td>Nama lengkap anggota</td>
              <td><code>Budi Santoso</code></td>
            </tr>
            <tr>
              <td><code>nik</code></td>
              <td><span class="badge bg-danger">Wajib</span></td>
              <td>16 digit, angka saja</td>
              <td><code>3273010101900001</code></td>
            </tr>
            <tr>
              <td><code>jenis_kelamin</code></td>
              <td><span class="badge bg-danger">Wajib</span></td>
              <td><code>Laki-laki</code> / <code>Perempuan</code></td>
              <td><code>Laki-laki</code></td>
            </tr>
            <tr>
              <td><code>tempat_lahir</code></td>
              <td><span class="badge bg-danger">Wajib</span></td>
              <td>Kota/kabupaten lahir</td>
              <td><code>Yogyakarta</code></td>
            </tr>
            <tr>
              <td><code>tanggal_lahir</code></td>
              <td><span class="badge bg-danger">Wajib</span></td>
              <td>Format YYYY-MM-DD</td>
              <td><code>1990-05-15</code></td>
            </tr>
            <tr>
              <td><code>alamat</code></td>
              <td><span class="badge bg-danger">Wajib</span></td>
              <td>Alamat lengkap</td>
              <td><code>Jl. Malioboro No. 1</code></td>
            </tr>
            <tr>
              <td><code>telepon</code></td>
              <td><span class="badge bg-danger">Wajib</span></td>
              <td>Nomor HP/telp</td>
              <td><code>08123456789</code></td>
            </tr>
            <tr>
              <td><code>tanggal_bergabung</code></td>
              <td><span class="badge bg-danger">Wajib</span></td>
              <td>Format YYYY-MM-DD</td>
              <td><code>{{ date('Y-m-d') }}</code></td>
            </tr>
            <tr>
              <td><code>jenis_anggota</code></td>
              <td><span class="badge bg-secondary">Opsional</span></td>
              <td><code>PNS</code> / <code>Non-PNS</code></td>
              <td><code>PNS</code></td>
            </tr>
            <tr>
              <td><code>email</code></td>
              <td><span class="badge bg-secondary">Opsional</span></td>
              <td>Email unik, kosongkan jika tidak ada</td>
              <td><code>budi@email.com</code></td>
            </tr>
            <tr>
              <td><code>status</code></td>
              <td><span class="badge bg-secondary">Opsional</span></td>
              <td><code>aktif</code> / <code>tidak_aktif</code> (default: aktif)</td>
              <td><code>aktif</code></td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="alert alert-info d-flex gap-2 mb-0" role="alert">
        <i class="bi bi-lightbulb-fill flex-shrink-0 mt-1"></i>
        <div class="small">
          <strong>Nomor anggota</strong> bisa diisi manual dengan format <code>KOP-YYYY-0001</code>.
          Jika kolom <code>nomor_anggota</code> kosong, sistem otomatis melanjutkan urutan nomor terakhir.
          Anggota yang NIK-nya sudah terdaftar akan <strong>dilewati</strong>, bukan error.
        </div>
      </div>
    </div>

    {{-- Cara membuat CSV dari Excel --}}
    <div class="form-card">
      <h6 class="fw-700 mb-3"><i class="bi bi-file-earmark-excel me-2 is-a24f9eb3"></i>Cara Buat CSV dari Excel</h6>
      <ol class="mb-0 ps-3 is-eab7dfc0">
        <li>Download template dengan klik <strong>Download Template</strong> di atas</li>
        <li>Buka file template di <strong>Microsoft Excel</strong> atau <strong>LibreOffice Calc</strong></li>
        <li>Isi data anggota mulai baris ke-2 (jangan ubah nama kolom di baris pertama)</li>
        <li>Pisahkan setiap kolom dengan <strong>titik koma (;)</strong> &mdash; bukan koma</li>
        <li>Klik <strong>File &rarr; Save As</strong>, pilih format <strong>CSV (Comma delimited) (*.csv)</strong></li>
        <li>Upload file .csv hasil simpan di form ini</li>
      </ol>
    </div>

  </div>
</div>
@endsection
