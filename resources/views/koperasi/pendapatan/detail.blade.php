@extends('layouts.aplikasi')

@section('judul', 'Detail Pendapatan')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('pendapatan.index') }}">Pendapatan</a></li>
  <li class="breadcrumb-item active">Detail</li>
@endsection

@section('konten')
<div class="row justify-content-center">
  <div class="col-lg-6">
    <div class="form-card">
      <div class="d-flex justify-content-between align-items-start mb-4">
        <h6 class="fw-700 mb-0 is-5ca88772">
          <i class="bi bi-graph-up-arrow me-2 text-success"></i>Detail Pendapatan
        </h6>
        <span class="badge bg-success-subtle text-success-emphasis fs-8">{{ $pendapatan->labelKategori }}</span>
      </div>

      <table class="table table-borderless table-sm mb-0 is-727130b2">
        <tbody>
          <tr>
            <th width="140" class="text-muted fw-600">Tanggal</th>
            <td>{{ $pendapatan->tanggal->isoFormat('D MMMM Y') }}</td>
          </tr>
          <tr>
            <th class="text-muted fw-600">Judul</th>
            <td class="fw-600">{{ $pendapatan->judul }}</td>
          </tr>
          <tr>
            <th class="text-muted fw-600">Kategori</th>
            <td>{{ $pendapatan->labelKategori }}</td>
          </tr>
          <tr>
            <th class="text-muted fw-600">Jumlah</th>
            <td class="fw-700 text-success fs-5">Rp {{ number_format($pendapatan->jumlah, 0, ',', '.') }}</td>
          </tr>
          <tr>
            <th class="text-muted fw-600">Keterangan</th>
            <td>{{ $pendapatan->keterangan ?: '-' }}</td>
          </tr>
          <tr>
            <th class="text-muted fw-600">Dicatat oleh</th>
            <td>{{ $pendapatan->pencatat?->name ?? '-' }}</td>
          </tr>
          <tr>
            <th class="text-muted fw-600">Waktu Input</th>
            <td>{{ $pendapatan->created_at->isoFormat('D MMM Y, HH:mm') }}</td>
          </tr>
        </tbody>
      </table>

      <div class="d-flex gap-2 mt-4">
        @can('pendapatan.kelola')
        <a href="{{ route('pendapatan.edit', $pendapatan) }}" class="btn btn-primary btn-sm">
          <i class="bi bi-pencil me-1"></i> Edit
        </a>
        <form action="{{ route('pendapatan.destroy', $pendapatan) }}" method="POST"
              onsubmit="return confirm('Hapus data pendapatan ini?')">
          @csrf @method('DELETE')
          <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash me-1"></i> Hapus</button>
        </form>
        @endcan
        <a href="{{ route('pendapatan.index') }}" class="btn btn-outline-secondary btn-sm">
          <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
      </div>
    </div>
  </div>
</div>
@endsection
