@extends('layouts.aplikasi')

@section('judul', 'Ubah Unit Kerja')

@section('konten')
<div class="row justify-content-center">
  <div class="col-xl-8">
    <div class="form-card">
      <div class="d-flex align-items-center gap-3 mb-4 pb-4 border-bottom">
        <div class="d-flex align-items-center justify-content-center rounded-3"
          style="width:50px;height:50px;background:rgba(23,92,221,.1)">
          <i class="bi bi-building-fill-gear" style="color:#175cdd;font-size:1.4rem"></i>
        </div>
        <div>
          <h5 class="mb-0 fw-700" style="color:#112344">Ubah Unit Kerja</h5>
          <small class="text-muted"><code>{{ $unitKerja->kode }}</code> — {{ $unitKerja->nama }}</small>
        </div>
      </div>

      @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
          <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ $errors->first() }}
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      @endif

      <form action="{{ route('unit-kerja.update', $unitKerja) }}" method="POST">
        @csrf @method('PUT')
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Kode <span class="text-danger">*</span></label>
            <input type="text" name="kode" class="form-control @error('kode') is-invalid @enderror"
              value="{{ old('kode', $unitKerja->kode) }}" placeholder="cth: ADMIN"
              style="text-transform:uppercase" maxlength="20">
            @error('kode')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-5">
            <label class="form-label">Nama Unit Kerja <span class="text-danger">*</span></label>
            <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror"
              value="{{ old('nama', $unitKerja->nama) }}" placeholder="Nama lengkap unit kerja">
            @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-3">
            <label class="form-label">Singkatan</label>
            <input type="text" name="singkatan" class="form-control @error('singkatan') is-invalid @enderror"
              value="{{ old('singkatan', $unitKerja->singkatan) }}" placeholder="cth: ADM" maxlength="20">
            @error('singkatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-12">
            <label class="form-label">Keterangan</label>
            <textarea name="keterangan" class="form-control @error('keterangan') is-invalid @enderror"
              rows="3" placeholder="Deskripsi singkat unit kerja (opsional)">{{ old('keterangan', $unitKerja->keterangan) }}</textarea>
            @error('keterangan')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-12">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" name="aktif" id="aktif" value="1"
                {{ old('aktif', $unitKerja->aktif) ? 'checked' : '' }}>
              <label class="form-check-label" for="aktif">Unit Kerja Aktif</label>
            </div>
          </div>
        </div>

        <div class="d-flex gap-2 mt-4 pt-4 border-top justify-content-end">
          <a href="{{ route('unit-kerja.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Batal
          </a>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-save me-1"></i> Simpan Perubahan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('js')
<script>
  document.querySelector('[name="kode"]').addEventListener('input', function() {
    this.value = this.value.toUpperCase();
  });
</script>
@endpush
