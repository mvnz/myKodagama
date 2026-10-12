@extends('layouts.aplikasi')

@section('judul', 'Import Angsuran')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('angsuran.index') }}">Angsuran</a></li>
  <li class="breadcrumb-item active">Import CSV</li>
@endsection

@section('konten')
<div class="row justify-content-center">
  <div class="col-xl-8">

    {{-- Page Banner --}}
    <div class="dashboard-welcome mb-4">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div>
          <div class="welcome-label mb-1">Data Angsuran</div>
          <h4 class="mb-1 fw-700">Import Pembayaran Angsuran dari CSV</h4>
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
      <p class="text-muted small mb-4">Pilih file CSV yang sudah sesuai format. Maksimal ukuran 5 MB.</p>

      <form action="{{ route('import.angsuran') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-4">
          <label class="form-label fw-600">File CSV <span class="text-danger">*</span></label>
          <input type="file" name="file" class="form-control" accept=".csv,.txt" required>
          <div class="form-text">Format yang diterima: .csv (delimiter koma atau titik koma)</div>
        </div>

        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-primary px-4">
            <i class="bi bi-box-arrow-in-down me-1"></i> Proses Import
          </button>
          <a href="{{ route('export.template.angsuran') }}" class="btn btn-outline-success px-4">
            <i class="bi bi-download me-1"></i> Download Template
          </a>
          <a href="{{ route('angsuran.index') }}" class="btn btn-outline-secondary px-4">
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
              <td><code>nomor_pinjaman</code></td>
              <td><span class="badge bg-danger">Wajib</span></td>
              <td>Nomor pinjaman yang sudah terdaftar dan punya jadwal angsuran</td>
              <td><code>PIN-202401-0001</code></td>
            </tr>
            <tr>
              <td><code>ke</code></td>
              <td><span class="badge bg-danger">Wajib</span></td>
              <td>Angsuran ke- (sesuai jadwal pinjaman)</td>
              <td><code>1</code></td>
            </tr>
            <tr>
              <td><code>tanggal_bayar</code></td>
              <td><span class="badge bg-danger">Wajib</span></td>
              <td>Format YYYY-MM-DD</td>
              <td><code>2024-02-12</code></td>
            </tr>
            <tr>
              <td><code>denda</code></td>
              <td><span class="badge bg-secondary">Opsional</span></td>
              <td>Angka. Jika kosong dihitung otomatis dari keterlambatan</td>
              <td><code>5000</code></td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="alert alert-info d-flex gap-2 mb-3" role="alert">
        <i class="bi bi-info-circle-fill flex-shrink-0 mt-1"></i>
        <div>
          <strong>Tips membuat CSV dari Excel:</strong>
          <ol class="mb-0 mt-1 ps-3 small">
            <li>Buat kolom sesuai format di atas (baris pertama = nama kolom).</li>
            <li>Isi data mulai baris kedua.</li>
            <li>Simpan sebagai <em>CSV UTF-8 (dengan pemisah koma)</em> atau gunakan titik koma sebagai delimiter.</li>
            <li>Pastikan <code>nomor_pinjaman</code> dan <code>ke</code> sama dengan jadwal angsuran di sistem.</li>
            <li>Angsuran yang sudah dibayar dilewati. Pinjaman otomatis menjadi lunas setelah semua angsuran dibayar.</li>
          </ol>
        </div>
      </div>

      <div class="alert alert-warning d-flex gap-2 mb-0" role="alert">
        <i class="bi bi-exclamation-triangle-fill flex-shrink-0 mt-1"></i>
        <div class="small">
          Data yang berhasil diimport <strong>tidak dapat dibatalkan secara massal</strong>. Periksa kembali file sebelum diproses.
        </div>
      </div>
    </div>

  </div>
</div>
@endsection
