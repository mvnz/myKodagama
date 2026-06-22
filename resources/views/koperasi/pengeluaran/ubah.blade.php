@extends('layouts.aplikasi')

@section('judul', 'Edit Pengeluaran')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('pengeluaran.index') }}">Pengeluaran</a></li>
  <li class="breadcrumb-item"><a href="{{ route('pengeluaran.show', $pengeluaran) }}">Detail</a></li>
  <li class="breadcrumb-item active">Edit</li>
@endsection

@section('konten')

<div class="row justify-content-center">
  <div class="col-lg-7">
    <div class="form-card">
      <h5 class="fw-700 mb-4">Edit Pengeluaran</h5>

      @if($errors->any())
        <div class="alert alert-danger py-2 mb-3">
          <ul class="mb-0 ps-3">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
          </ul>
        </div>
      @endif

      <form action="{{ route('pengeluaran.update', $pengeluaran) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row g-3">
          <div class="col-sm-6">
            <label class="form-label fw-600">Tanggal <span class="text-danger">*</span></label>
            <input type="date" name="tanggal"
                   value="{{ old('tanggal', $pengeluaran->tanggal->format('Y-m-d')) }}"
                   class="form-control @error('tanggal') is-invalid @enderror">
            @error('tanggal')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-sm-6">
            <label class="form-label fw-600">Kategori <span class="text-danger">*</span></label>
            <select name="kategori" class="form-select @error('kategori') is-invalid @enderror">
              <option value="">-- Pilih Kategori --</option>
              @foreach($labelKategori as $key => $label)
                <option value="{{ $key }}" @selected(old('kategori', $pengeluaran->kategori) === $key)>{{ $label }}</option>
              @endforeach
            </select>
            @error('kategori')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-12">
            <label class="form-label fw-600">Judul / Uraian <span class="text-danger">*</span></label>
            <input type="text" name="judul"
                   value="{{ old('judul', $pengeluaran->judul) }}"
                   class="form-control @error('judul') is-invalid @enderror">
            @error('judul')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-sm-6">
            <label class="form-label fw-600">Jumlah (Rp) <span class="text-danger">*</span></label>
            <input type="number" name="jumlah" min="1"
                   value="{{ old('jumlah', (int) $pengeluaran->jumlah) }}"
                   class="form-control @error('jumlah') is-invalid @enderror">
            @error('jumlah')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-sm-6">
            <label class="form-label fw-600">Bukti / Lampiran</label>
            @if($pengeluaran->bukti)
              <div class="mb-2">
                <a href="{{ Storage::url($pengeluaran->bukti) }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                  <i class="bi bi-paperclip me-1"></i> Lihat Lampiran Saat Ini
                </a>
              </div>
            @endif
            <input type="file" name="bukti" accept=".jpg,.jpeg,.png,.pdf"
                   class="form-control @error('bukti') is-invalid @enderror">
            <div class="form-text">Kosongkan jika tidak ingin mengubah lampiran. JPG, PNG, atau PDF, maks 2MB.</div>
            @error('bukti')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-12">
            <label class="form-label fw-600">Keterangan</label>
            <textarea name="keterangan" rows="3"
                      class="form-control @error('keterangan') is-invalid @enderror">{{ old('keterangan', $pengeluaran->keterangan) }}</textarea>
            @error('keterangan')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
        </div>

        <div class="d-flex gap-2 mt-4 pt-4 border-top">
          <button type="submit" class="btn btn-primary px-4">
            <i class="bi bi-check-lg me-1"></i> Simpan
          </button>
          <a href="{{ route('pengeluaran.show', $pengeluaran) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
          </a>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection
