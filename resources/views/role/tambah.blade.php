@extends('layouts.aplikasi')

@section('judul', 'Tambah Role')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('role.index') }}">Pengaturan Role</a></li>
  <li class="breadcrumb-item active">Tambah Role</li>
@endsection

@section('konten')
<div class="row justify-content-center">
  <div class="col-xl-10">
    <div class="form-card">
      <div class="d-flex align-items-center gap-3 mb-4 pb-4 border-bottom">
        <div class="d-flex align-items-center justify-content-center rounded-3"
          style="width:50px;height:50px;background:rgba(23,92,221,.1)">
          <i class="bi bi-shield-plus" style="color:#175cdd;font-size:1.4rem"></i>
        </div>
        <div>
          <h5 class="mb-0 fw-700" style="color:#112344">Tambah Role Baru</h5>
          <small class="text-muted">Tentukan nama role dan pilih hak akses yang diizinkan</small>
        </div>
      </div>

      <form action="{{ route('role.store') }}" method="POST">
        @csrf

        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <label class="form-label">Nama Role <span class="text-danger">*</span></label>
            <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror"
              value="{{ old('nama') }}" placeholder="cth: Kasir, Pengurus, Bendahara">
            @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-6">
            <label class="form-label">Deskripsi</label>
            <input type="text" name="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror"
              value="{{ old('deskripsi') }}" placeholder="Keterangan singkat tentang role ini">
            @error('deskripsi')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
        </div>

        <h6 class="text-muted mb-3 text-uppercase" style="font-size:.78rem;letter-spacing:1px">
          <i class="bi bi-key me-1"></i> Pilih Hak Akses
        </h6>

        <!-- Tombol Pilih Semua / Hapus Semua -->
        <div class="d-flex gap-2 mb-3">
          <button type="button" class="btn btn-sm btn-outline-primary" onclick="pilihSemua()">
            <i class="bi bi-check-all me-1"></i>Pilih Semua
          </button>
          <button type="button" class="btn btn-sm btn-outline-secondary" onclick="hapusSemua()">
            <i class="bi bi-x-lg me-1"></i>Hapus Semua
          </button>
        </div>

        <div class="row g-3">
          @foreach($permissions as $grup => $items)
          <div class="col-md-6">
            <div class="p-3 rounded-3 border h-100" style="background:#f8faff">
              <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="mb-0 fw-600" style="color:#175cdd;font-size:.88rem">
                  <i class="bi {{ grupIkon($grup) }} me-2"></i>{{ $grup }}
                </h6>
                <button type="button" class="btn btn-xs btn-outline-primary btn-sm py-0 px-2"
                  style="font-size:.72rem" onclick="pilihGrup('{{ Str::slug($grup) }}')">
                  Pilih Semua
                </button>
              </div>
              @foreach($items as $permission)
              <div class="form-check mb-2">
                <input class="form-check-input permission-check grup-{{ Str::slug($grup) }}"
                  type="checkbox" name="permissions[]"
                  value="{{ $permission->id }}"
                  id="perm_{{ $permission->id }}"
                  {{ in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}>
                <label class="form-check-label" for="perm_{{ $permission->id }}" style="font-size:.88rem">
                  {{ $permission->nama }}
                  <code class="ms-1" style="font-size:.72rem;color:#6b7280">{{ $permission->slug }}</code>
                </label>
              </div>
              @endforeach
            </div>
          </div>
          @endforeach
        </div>

        @error('permissions')
          <div class="text-danger small mt-2">{{ $message }}</div>
        @enderror

        <div class="d-flex gap-2 mt-4 pt-4 border-top">
          <button type="submit" class="btn btn-primary px-4">
            <i class="bi bi-check-lg me-1"></i> Simpan Role
          </button>
          <a href="{{ route('role.index') }}" class="btn btn-outline-secondary px-4">
            <i class="bi bi-arrow-left me-1"></i> Kembali
          </a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('js')
<script>
  function pilihSemua() {
    document.querySelectorAll('.permission-check').forEach(cb => cb.checked = true);
  }
  function hapusSemua() {
    document.querySelectorAll('.permission-check').forEach(cb => cb.checked = false);
  }
  function pilihGrup(slug) {
    document.querySelectorAll('.grup-' + slug).forEach(cb => cb.checked = true);
  }
</script>
@endpush

