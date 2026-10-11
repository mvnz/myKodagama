@extends('layouts.aplikasi')

@section('judul', 'Hasil Import Anggota')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('anggota.index') }}">Anggota</a></li>
  <li class="breadcrumb-item"><a href="{{ route('import.anggota.form') }}">Import CSV</a></li>
  <li class="breadcrumb-item active">Hasil</li>
@endsection

@section('konten')

{{-- Ringkasan --}}
<div class="row g-3 mb-4">
  <div class="col-sm-4">
    <div class="kstat-card kstat-green">
      <div class="kstat-deco"></div>
      <div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-check-circle-fill"></i></div>
      <div class="kstat-value">{{ count($berhasil) }}</div>
      <div class="kstat-label">Berhasil Diimpor</div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="kstat-card kstat-amber">
      <div class="kstat-deco"></div>
      <div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-skip-forward-fill"></i></div>
      <div class="kstat-value">{{ count($dilewati) }}</div>
      <div class="kstat-label">Dilewati (Duplikat)</div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="kstat-card kstat-red">
      <div class="kstat-deco"></div>
      <div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-x-circle-fill"></i></div>
      <div class="kstat-value">{{ count($error) }}</div>
      <div class="kstat-label">Error</div>
    </div>
  </div>
</div>

<div class="d-flex gap-2 mb-4">
  @if(count($berhasil))
    <a href="{{ route('anggota.index') }}" class="btn btn-primary">
      <i class="bi bi-people-fill me-1"></i> Lihat Daftar Anggota
    </a>
  @endif
  <a href="{{ route('import.anggota.form') }}" class="btn btn-outline-secondary">
    <i class="bi bi-box-arrow-in-down me-1"></i> Import Lagi
  </a>
</div>

{{-- Berhasil --}}
@if(count($berhasil))
<div class="form-card mb-4">
  <h6 class="fw-700 mb-3 text-success">
    <i class="bi bi-check-circle-fill me-2"></i>{{ count($berhasil) }} Data Berhasil Diimpor
  </h6>
  <div class="table-responsive">
    <table class="koperasi-table table">
      <thead>
        <tr>
          <th width="60">Baris</th>
          <th>Nomor Anggota</th>
          <th>Nama</th>
        </tr>
      </thead>
      <tbody>
        @foreach($berhasil as $row)
        <tr>
          <td class="text-muted">{{ $row['baris'] }}</td>
          <td><code>{{ $row['nomor'] }}</code></td>
          <td>{{ $row['nama'] }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endif

{{-- Dilewati --}}
@if(count($dilewati))
<div class="form-card mb-4">
  <h6 class="fw-700 mb-3 is-f571fe9b">
    <i class="bi bi-skip-forward-fill me-2"></i>{{ count($dilewati) }} Baris Dilewati (Duplikat)
  </h6>
  <div class="table-responsive">
    <table class="koperasi-table table">
      <thead>
        <tr>
          <th width="60">Baris</th>
          <th>Nama</th>
          <th>Alasan</th>
        </tr>
      </thead>
      <tbody>
        @foreach($dilewati as $row)
        <tr>
          <td class="text-muted">{{ $row['baris'] }}</td>
          <td>{{ $row['nama'] }}</td>
          <td><span class="badge bg-warning text-dark">{{ $row['alasan'] }}</span></td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endif

{{-- Error --}}
@if(count($error))
<div class="form-card mb-4">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <h6 class="fw-700 mb-0 text-danger">
      <i class="bi bi-x-circle-fill me-2"></i>{{ count($error) }} Baris Gagal Diproses
    </h6>
    <a href="{{ route('import.errors.csv', ['jenis' => 'anggota']) }}" class="btn btn-sm btn-outline-danger">
      <i class="bi bi-download me-1"></i> Export Error CSV
    </a>
  </div>
  <div class="table-responsive">
    <table class="koperasi-table table">
      <thead>
        <tr>
          <th width="60">Baris</th>
          <th>Nomor Anggota</th>
          <th>Nama</th>
          <th>Pesan Error</th>
        </tr>
      </thead>
      <tbody>
        @foreach($error as $row)
        <tr>
          <td class="text-muted">{{ $row['baris'] }}</td>
          <td><code>{{ $row['nomor'] ?? '-' }}</code></td>
          <td>{{ $row['nama'] }}</td>
          <td><span class="text-danger small">{{ $row['pesan'] }}</span></td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  <div class="alert alert-warning py-2 mt-3 mb-0 is-a7560b14">
    <i class="bi bi-exclamation-triangle-fill me-1"></i>
    Perbaiki data di file CSV sesuai pesan di atas, lalu import kembali baris yang gagal saja.
  </div>
</div>
@endif

@if(! count($berhasil) && ! count($dilewati) && ! count($error))
  <div class="form-card text-center py-5">
    <i class="bi bi-file-earmark-x text-muted is-b178ddac"></i>
    <p class="mt-3 text-muted">File CSV tidak memiliki data selain header.</p>
    <a href="{{ route('import.anggota.form') }}" class="btn btn-primary mt-2">
      <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
  </div>
@endif

@endsection
