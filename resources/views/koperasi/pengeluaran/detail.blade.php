@extends('layouts.aplikasi')

@section('judul', 'Detail Pengeluaran')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('pengeluaran.index') }}">Pengeluaran</a></li>
  <li class="breadcrumb-item active">Detail</li>
@endsection

@section('konten')

<div class="row justify-content-center">
  <div class="col-lg-7">

    @if(session('sukses'))
      <div class="alert alert-success py-2 mb-3">{{ session('sukses') }}</div>
    @endif

    <div class="form-card">
      <div class="d-flex align-items-start justify-content-between mb-4">
        <div>
          <h5 class="fw-700 mb-1">{{ $pengeluaran->judul }}</h5>
          <span class="badge bg-secondary bg-opacity-75 text-dark">{{ $pengeluaran->label_kategori }}</span>
        </div>
        <div class="text-end">
          <div class="fw-800 text-danger is-ab66dd84">
            Rp {{ number_format($pengeluaran->jumlah, 0, ',', '.') }}
          </div>
          <div class="text-muted is-9ee0bb07">{{ $pengeluaran->tanggal->translatedFormat('d F Y') }}</div>
        </div>
      </div>

      <table class="table table-sm table-borderless mb-0">
        <tbody>
          <tr>
            <td class="text-muted is-58180d3d" width="140">Tanggal</td>
            <td class="is-58180d3d">{{ $pengeluaran->tanggal->translatedFormat('d F Y') }}</td>
          </tr>
          <tr>
            <td class="text-muted is-58180d3d">Kategori</td>
            <td class="is-58180d3d">{{ $pengeluaran->label_kategori }}</td>
          </tr>
          <tr>
            <td class="text-muted is-58180d3d">Jumlah</td>
            <td class="fw-700 text-danger is-58180d3d">Rp {{ number_format($pengeluaran->jumlah, 0, ',', '.') }}</td>
          </tr>
          <tr>
            <td class="text-muted is-58180d3d">Keterangan</td>
            <td class="is-58180d3d">{{ $pengeluaran->keterangan ?: '-' }}</td>
          </tr>
          <tr>
            <td class="text-muted is-58180d3d">Dicatat oleh</td>
            <td class="is-58180d3d">{{ $pengeluaran->pencatat?->name ?? '-' }}</td>
          </tr>
          <tr>
            <td class="text-muted is-58180d3d">Waktu Input</td>
            <td class="is-58180d3d">{{ $pengeluaran->created_at->translatedFormat('d F Y H:i') }}</td>
          </tr>
          @if($pengeluaran->bukti)
          <tr>
            <td class="text-muted is-58180d3d">Bukti</td>
            <td>
              <a href="{{ Storage::url($pengeluaran->bukti) }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-paperclip me-1"></i> Lihat Lampiran
              </a>
            </td>
          </tr>
          @endif
        </tbody>
      </table>

      <div class="d-flex gap-2 mt-4 pt-3 border-top">
        @can('pengeluaran.kelola')
        <a href="{{ route('pengeluaran.edit', $pengeluaran) }}" class="btn btn-primary btn-sm">
          <i class="bi bi-pencil me-1"></i> Edit
        </a>
        <form action="{{ route('pengeluaran.destroy', $pengeluaran) }}" method="POST"
              onsubmit="return confirm('Hapus pengeluaran ini?')">
          @csrf @method('DELETE')
          <button type="submit" class="btn btn-outline-danger btn-sm">
            <i class="bi bi-trash me-1"></i> Hapus
          </button>
        </form>
        @endcan
        <a href="{{ route('pengeluaran.index') }}" class="btn btn-outline-secondary btn-sm ms-auto">
          <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
      </div>
    </div>

  </div>
</div>

@endsection
