@extends('layouts.aplikasi')

@section('judul', 'Tambah Pendapatan')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('pendapatan.index') }}">Pendapatan</a></li>
  <li class="breadcrumb-item active">Tambah</li>
@endsection

@section('konten')
<div class="row justify-content-center">
  <div class="col-lg-7">
    <div class="form-card">
      <h6 class="fw-700 mb-4 is-5ca88772"><i class="bi bi-plus-circle me-2"></i>Tambah Pendapatan</h6>

      @if($errors->any())
        <div class="alert alert-danger py-2">
          <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
      @endif

      <form action="{{ route('pendapatan.store') }}" method="POST">
        @csrf

        <div class="row g-3">
          <div class="col-sm-6">
            <label class="form-label fw-600">Tanggal <span class="text-danger">*</span></label>
            <input type="date" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}"
                   class="form-control @error('tanggal') is-invalid @enderror">
            @error('tanggal')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-sm-6">
            <label class="form-label fw-600">Kategori <span class="text-danger">*</span></label>
            <select name="kategori" class="form-select @error('kategori') is-invalid @enderror">
              <option value="">-- Pilih Kategori --</option>
              @foreach($labelKategori as $k => $l)
                <option value="{{ $k }}" @selected(old('kategori') == $k)>{{ $l }}</option>
              @endforeach
            </select>
            @error('kategori')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-12">
            <label class="form-label fw-600">Judul / Deskripsi Singkat <span class="text-danger">*</span></label>
            <input type="text" name="judul" value="{{ old('judul') }}" placeholder="Misal: Jasa admin pinjaman bulan Mei"
                   class="form-control @error('judul') is-invalid @enderror">
            @error('judul')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-sm-6">
            <label class="form-label fw-600">Jumlah (Rp) <span class="text-danger">*</span></label>
            <input type="number" name="jumlah" value="{{ old('jumlah') }}" placeholder="0" min="1"
                   class="form-control @error('jumlah') is-invalid @enderror">
            @error('jumlah')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-12">
            <label class="form-label fw-600">Keterangan</label>
            <textarea name="keterangan" rows="3" placeholder="Keterangan tambahan (opsional)"
                      class="form-control @error('keterangan') is-invalid @enderror">{{ old('keterangan') }}</textarea>
            @error('keterangan')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
        </div>

        <div class="d-flex gap-2 mt-4 pt-4 border-top">
          <button type="submit" class="btn btn-primary px-4">
            <i class="bi bi-check-lg me-1"></i> Simpan
          </button>
          <a href="{{ route('pendapatan.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Batal
          </a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
