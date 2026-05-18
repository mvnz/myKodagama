@extends('layouts.aplikasi')

@section('judul', 'SHU - Sisa Hasil Usaha')

@section('breadcrumb')
  <li class="breadcrumb-item active">SHU</li>
@endsection

@section('konten')

{{-- Page Banner --}}
<div class="dashboard-welcome mb-4">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
      <div class="welcome-label mb-1">Laporan</div>
      <h4 class="mb-1 fw-700">Sisa Hasil Usaha (SHU)</h4>
      <p class="mb-0 welcome-date">
        <i class="bi bi-calendar3 me-1"></i>
        {{ now()->translatedFormat('l, d F Y') }}
      </p>
    </div>
    <div class="welcome-icon-wrap"><i class="bi bi-pie-chart-fill"></i></div>
  </div>
</div>

@if(session('sukses'))
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle me-2"></i>{{ session('sukses') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
@endif
@if(session('error'))
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
@endif

<!-- Table Card -->
<div class="form-card p-0 overflow-hidden">
  <div class="p-4 border-bottom d-flex align-items-center justify-content-between">
    <h6 class="mb-0 fw-700" style="color:#112344">
      <i class="bi bi-pie-chart-fill me-2" style="color:#175cdd"></i>Daftar SHU
      <span class="badge bg-primary ms-2">{{ $daftarShu->total() }}</span>
    </h6>
    @can('shu.kelola')
      <a href="{{ route('shu.hitung') }}" class="btn btn-success btn-sm">
        <i class="bi bi-calculator me-1"></i> Hitung SHU Baru
      </a>
    @endcan
  </div>

  <div class="table-responsive">
    <table class="table koperasi-table mb-0">
      <thead>
        <tr>
          <th>Tahun</th>
          <th class="text-end">Total Pendapatan</th>
          <th class="text-end">Total Biaya</th>
          <th class="text-end">Total SHU</th>
          <th class="text-center">Anggota</th>
          <th class="text-center">Status</th>
          <th class="text-center">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($daftarShu as $shu)
          <tr>
            <td class="fw-700">{{ $shu->tahun }}</td>
            <td class="text-end">Rp {{ number_format($shu->total_pendapatan, 0, ',', '.') }}</td>
            <td class="text-end">Rp {{ number_format($shu->total_biaya, 0, ',', '.') }}</td>
            <td class="text-end fw-700 text-success">Rp {{ number_format($shu->total_shu, 0, ',', '.') }}</td>
            <td class="text-center">{{ $shu->anggota_count }}</td>
            <td class="text-center">
              @if($shu->status === 'final')
                <span class="badge bg-success">Final</span>
              @else
                <span class="badge bg-warning text-dark">Draft</span>
              @endif
            </td>
            <td class="text-center">
              <a href="{{ route('shu.show', $shu) }}" class="btn btn-sm btn-outline-primary" title="Detail">
                <i class="bi bi-eye"></i>
              </a>
              <a href="{{ route('shu.cetak', $shu) }}" class="btn btn-sm btn-outline-success" title="Cetak" target="_blank">
                <i class="bi bi-printer"></i>
              </a>
              @can('shu.kelola')
                @if($shu->status === 'draft')
                  <form action="{{ route('shu.destroy', $shu) }}" method="POST" class="d-inline"
                        onsubmit="return confirm('Hapus data SHU tahun {{ $shu->tahun }}?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger" title="Hapus">
                      <i class="bi bi-trash"></i>
                    </button>
                  </form>
                @endif
              @endcan
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="text-center text-muted py-4">
              <i class="bi bi-inbox fs-4 d-block mb-2"></i>Belum ada data SHU
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if($daftarShu->hasPages())
    <div class="p-3 border-top">
      {{ $daftarShu->links('vendor.pagination.koperasi') }}
    </div>
  @endif
</div>

@endsection
