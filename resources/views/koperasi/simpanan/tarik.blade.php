@extends('layouts.aplikasi')

@section('judul', 'Tarik Simpanan')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('simpanan.index') }}">Simpanan</a></li>
  <li class="breadcrumb-item active">Penarikan</li>
@endsection

@section('konten')
<div class="row justify-content-center">
  <div class="col-lg-6">

    <div class="form-card mb-3">
      <div class="d-flex align-items-center gap-3 mb-4">
        <div class="kstat-icon kstat-amber is-bdf570a9">
          <i class="bi bi-arrow-down-circle-fill fs-4"></i>
        </div>
        <div>
          <div class="fw-700 is-5ca88772">{{ $anggota->nama }}</div>
          <div class="text-muted small">{{ $anggota->nomor_anggota }}</div>
        </div>
      </div>

      <div class="alert alert-info py-2 mb-4 is-58180d3d">
        <i class="bi bi-piggy-bank-fill me-2"></i>
        Saldo <strong>Simpanan Sukarela</strong>: <strong>Rp {{ number_format($saldo, 0, ',', '.') }}</strong>
        <div class="mt-1 text-muted is-6c660c40">Hanya simpanan sukarela yang dapat ditarik.</div>
      </div>

      @if($errors->any())
        <div class="alert alert-danger py-2 mb-3">
          <ul class="mb-0 ps-3">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
          </ul>
        </div>
      @endif

      @if($saldo <= 0)
        <div class="alert alert-warning">
          <i class="bi bi-exclamation-triangle-fill me-2"></i>
          Tidak ada saldo simpanan sukarela yang dapat ditarik.
        </div>
        <a href="{{ route('simpanan.index') }}" class="btn btn-outline-secondary btn-sm">
          <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
      @else
        <form action="{{ route('simpanan.tarik', $anggota) }}" method="POST">
          @csrf

          <div class="row g-3">
            <div class="col-sm-6">
              <label class="form-label fw-600">Tanggal Penarikan <span class="text-danger">*</span></label>
              <input type="date" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}"
                     class="form-control @error('tanggal') is-invalid @enderror">
              @error('tanggal')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-sm-6">
              <label class="form-label fw-600">Jumlah (Rp) <span class="text-danger">*</span></label>
              <input type="number" name="jumlah" value="{{ old('jumlah') }}"
                     placeholder="0" min="1" max="{{ $saldo }}"
                     class="form-control @error('jumlah') is-invalid @enderror">
              <div class="form-text">Maks: Rp {{ number_format($saldo, 0, ',', '.') }}</div>
              @error('jumlah')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12">
              <label class="form-label fw-600">Keterangan</label>
              <input type="text" name="keterangan" value="{{ old('keterangan') }}"
                     placeholder="Keterangan penarikan (opsional)"
                     class="form-control @error('keterangan') is-invalid @enderror">
              @error('keterangan')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>

          <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-warning"
                    onclick="return confirm('Konfirmasi penarikan simpanan?')">
              <i class="bi bi-arrow-down-circle me-1"></i> Proses Penarikan
            </button>
            <a href="{{ route('simpanan.index') }}" class="btn btn-outline-secondary">
              <i class="bi bi-arrow-left me-1"></i> Batal
            </a>
          </div>
        </form>
      @endif
    </div>
  </div>
</div>
@endsection
