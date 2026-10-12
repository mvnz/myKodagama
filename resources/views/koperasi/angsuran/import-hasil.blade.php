@extends('layouts.aplikasi')

@section('judul', 'Hasil Import Angsuran')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('angsuran.index') }}">Angsuran</a></li>
  <li class="breadcrumb-item"><a href="{{ route('import.angsuran.form') }}">Import CSV</a></li>
  <li class="breadcrumb-item active">Hasil</li>
@endsection

@section('konten')
<div class="row justify-content-center">
  <div class="col-xl-9">

    {{-- Ringkasan --}}
    <div class="row g-3 mb-4">
      <div class="col-sm-4">
        <div class="form-card text-center py-3">
          <div class="fw-700 is-7f063ba0">{{ count($berhasil) }}</div>
          <div class="text-muted small mt-1"><i class="bi bi-check-circle-fill text-success me-1"></i>Berhasil diimport</div>
        </div>
      </div>
      <div class="col-sm-4">
        <div class="form-card text-center py-3">
          <div class="fw-700 is-40843df9">{{ count($dilewati) }}</div>
          <div class="text-muted small mt-1"><i class="bi bi-skip-forward-fill text-warning me-1"></i>Dilewati</div>
        </div>
      </div>
      <div class="col-sm-4">
        <div class="form-card text-center py-3">
          <div class="fw-700 is-8cbedfe4">{{ count($error) }}</div>
          <div class="text-muted small mt-1"><i class="bi bi-x-circle-fill text-danger me-1"></i>Gagal / Error</div>
        </div>
      </div>
    </div>

    {{-- Berhasil --}}
    @if(count($berhasil) > 0)
    <div class="form-card mb-4">
      <h6 class="fw-700 mb-3 is-deccf9e7">
        <i class="bi bi-check-circle-fill me-2"></i>{{ count($berhasil) }} Data Berhasil Diimport
      </h6>
      <div class="table-responsive">
        <table class="koperasi-table mb-0">
          <thead>
            <tr>
              <th>Baris</th>
              <th>Nama Anggota</th>
              <th>No. Pinjaman</th>
              <th>Ke</th>
              <th>Tgl Bayar</th>
              <th class="text-end">Jumlah</th>
            </tr>
          </thead>
          <tbody>
            @foreach($berhasil as $b)
            <tr>
              <td class="text-muted small">{{ $b['baris'] }}</td>
              <td>{{ $b['nama'] }}</td>
              <td><code>{{ $b['nomor'] }}</code></td>
              <td>{{ $b['ke'] }}</td>
              <td>{{ $b['tanggal'] }}</td>
              <td class="text-end fw-600 is-deccf9e7">Rp {{ number_format($b['jumlah'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    @endif

    {{-- Dilewati --}}
    @if(count($dilewati) > 0)
    <div class="form-card mb-4">
      <h6 class="fw-700 mb-3 text-warning">
        <i class="bi bi-skip-forward-fill me-2"></i>{{ count($dilewati) }} Baris Dilewati
      </h6>
      <div class="table-responsive">
        <table class="koperasi-table mb-0">
          <thead>
            <tr><th>Baris</th><th>Nomor Pinjaman</th><th>Ke</th><th>Alasan</th></tr>
          </thead>
          <tbody>
            @foreach($dilewati as $d)
            <tr>
              <td class="text-muted small">{{ $d['baris'] }}</td>
              <td>{{ $d['nomor'] }}</td>
              <td>{{ $d['ke'] }}</td>
              <td class="text-warning">{{ $d['alasan'] }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    @endif

    {{-- Error --}}
    @if(count($error) > 0)
    <div class="form-card mb-4">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h6 class="fw-700 mb-0 text-danger">
          <i class="bi bi-x-circle-fill me-2"></i>{{ count($error) }} Baris Gagal
        </h6>
        <a href="{{ route('import.errors.csv', ['jenis' => 'angsuran']) }}" class="btn btn-sm btn-outline-danger">
          <i class="bi bi-download me-1"></i> Export Error CSV
        </a>
      </div>
      <div class="table-responsive">
        <table class="koperasi-table mb-0">
          <thead>
            <tr><th>Baris</th><th>Nomor Pinjaman</th><th>Ke</th><th>Pesan Error</th></tr>
          </thead>
          <tbody>
            @foreach($error as $e)
            <tr>
              <td class="text-muted small">{{ $e['baris'] }}</td>
              <td>{{ $e['nomor'] }}</td>
              <td>{{ $e['ke'] }}</td>
              <td class="text-danger small">{{ $e['pesan'] }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    @endif

    <div class="d-flex gap-2">
      <a href="{{ route('angsuran.index') }}" class="btn btn-primary px-4">
        <i class="bi bi-list-ul me-1"></i> Lihat Data Angsuran
      </a>
      <a href="{{ route('import.angsuran.form') }}" class="btn btn-outline-secondary px-4">
        <i class="bi bi-box-arrow-in-down me-1"></i> Import Lagi
      </a>
    </div>

  </div>
</div>
@endsection
