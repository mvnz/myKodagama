@extends('layouts.aplikasi')

@section('judul', 'Import Unit Kerja')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('unit-kerja.index') }}">Unit Kerja</a></li>
  <li class="breadcrumb-item active">Import CSV</li>
@endsection

@section('konten')
<div class="row justify-content-center">
  <div class="col-xl-8">

    {{-- Page Banner --}}
    <div class="dashboard-welcome mb-4">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div>
          <div class="welcome-label mb-1">Data Unit Kerja</div>
          <h4 class="mb-1 fw-700">Import Unit Kerja dari CSV</h4>
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

      <form action="{{ route('import.unit-kerja') }}" method="POST" enctype="multipart/form-data">
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
          <a href="{{ route('export.template.unit-kerja') }}" class="btn btn-outline-success px-4">
            <i class="bi bi-download me-1"></i> Download Template
          </a>
          <a href="{{ route('unit-kerja.index') }}" class="btn btn-outline-secondary px-4">
            <i class="bi bi-arrow-left me-1"></i> Kembali
          </a>
        </div>
      </form>
    </div>

    {{-- Format Kolom --}}
    <div class="form-card">
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
              <td><code>kode</code></td>
              <td><span class="badge bg-danger">Wajib</span></td>
              <td>Kode unik, maks. 20 karakter</td>
              <td><code>IT</code></td>
            </tr>
            <tr>
              <td><code>nama</code></td>
              <td><span class="badge bg-danger">Wajib</span></td>
              <td>Nama lengkap unit kerja</td>
              <td><code>Teknologi Informasi</code></td>
            </tr>
            <tr>
              <td><code>singkatan</code></td>
              <td><span class="badge bg-secondary">Opsional</span></td>
              <td>Singkatan, maks. 20 karakter</td>
              <td><code>TI</code></td>
            </tr>
            <tr>
              <td><code>keterangan</code></td>
              <td><span class="badge bg-secondary">Opsional</span></td>
              <td>Teks bebas</td>
              <td><code>Divisi IT</code></td>
            </tr>
            <tr>
              <td><code>aktif</code></td>
              <td><span class="badge bg-secondary">Opsional</span></td>
              <td><code>ya</code> / <code>tidak</code> (default: ya)</td>
              <td><code>ya</code></td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="alert alert-info d-flex gap-2 mb-0" role="alert">
        <i class="bi bi-lightbulb-fill flex-shrink-0 mt-1"></i>
        <div class="small">
          Unit kerja dengan <strong>kode yang sudah terdaftar</strong> akan <strong>dilewati</strong>, bukan error.
        </div>
      </div>
    </div>

  </div>
</div>
@endsection
