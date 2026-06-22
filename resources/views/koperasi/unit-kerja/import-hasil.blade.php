@extends('layouts.aplikasi')

@section('judul', 'Hasil Import Unit Kerja')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('unit-kerja.index') }}">Unit Kerja</a></li>
  <li class="breadcrumb-item"><a href="{{ route('import.unit-kerja.form') }}">Import CSV</a></li>
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
    <a href="{{ route('unit-kerja.index') }}" class="btn btn-primary">
      <i class="bi bi-building-fill me-1"></i> Lihat Daftar Unit Kerja
    </a>
  @endif
  <a href="{{ route('import.unit-kerja.form') }}" class="btn btn-outline-secondary">
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
          <th>Kode</th>
          <th>Nama Unit Kerja</th>
        </tr>
      </thead>
      <tbody>
        @foreach($berhasil as $row)
        <tr>
          <td class="text-muted">{{ $row['baris'] }}</td>
          <td><code>{{ $row['kode'] }}</code></td>
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
          <th>Kode</th>
          <th>Nama</th>
          <th>Alasan</th>
        </tr>
      </thead>
      <tbody>
        @foreach($dilewati as $row)
        <tr>
          <td class="text-muted">{{ $row['baris'] }}</td>
          <td><code>{{ $row['kode'] }}</code></td>
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
  <h6 class="fw-700 mb-3 text-danger">
    <i class="bi bi-x-circle-fill me-2"></i>{{ count($error) }} Baris Gagal Diproses
  </h6>
  <div class="table-responsive">
    <table class="koperasi-table table">
      <thead>
        <tr>
          <th width="60">Baris</th>
          <th>Kode</th>
          <th>Pesan Error</th>
        </tr>
      </thead>
      <tbody>
        @foreach($error as $row)
        <tr>
          <td class="text-muted">{{ $row['baris'] }}</td>
          <td><code>{{ $row['kode'] }}</code></td>
          <td><span class="text-danger">{{ $row['pesan'] }}</span></td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endif

@endsection
