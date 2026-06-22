@extends('layouts.aplikasi')

@section('judul', 'Edit Role – ' . $role->nama)

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('role.index') }}">Pengaturan Role</a></li>
  <li class="breadcrumb-item active">Edit: {{ $role->nama }}</li>
@endsection

@section('konten')
<div class="row justify-content-center">
  <div class="col-xl-10">
    <div class="form-card">
      <div class="d-flex align-items-center gap-3 mb-4 pb-4 border-bottom">
        <div class="d-flex align-items-center justify-content-center rounded-3 is-1e545767"
         >
          <i class="bi bi-shield-lock-fill is-f1e54591"></i>
        </div>
        <div>
          <h5 class="mb-0 fw-700 is-5ca88772">Edit Role: {{ $role->nama }}</h5>
          <small class="text-muted">Ubah nama, deskripsi, dan hak akses role ini</small>
        </div>
      </div>

      <form action="{{ route('role.update', $role) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <label class="form-label">Nama Role <span class="text-danger">*</span></label>
            <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror"
              value="{{ old('nama', $role->nama) }}" placeholder="cth: Kasir, Pengurus, Bendahara">
            @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-6">
            <label class="form-label">Deskripsi</label>
            <input type="text" name="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror"
              value="{{ old('deskripsi', $role->deskripsi) }}" placeholder="Keterangan singkat tentang role ini">
            @error('deskripsi')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
        </div>

        <h6 class="text-muted mb-3 text-uppercase is-0cea37d0">
          <i class="bi bi-key me-1"></i> Hak Akses
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
            <div class="p-3 rounded-3 border h-100 is-bf063d1c">
              <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="mb-0 fw-600 is-6b7d1c61">
                  <i class="bi {{ grupIkon($grup) }} me-2"></i>{{ $grup }}
                </h6>
                <button type="button" class="btn btn-xs btn-outline-primary btn-sm py-0 px-2 is-cc73531a"
                  onclick="pilihGrup('{{ Str::slug($grup) }}')">
                  Pilih Semua
                </button>
              </div>
              @foreach($items as $permission)
              <div class="form-check mb-2">
                <input class="form-check-input permission-check grup-{{ Str::slug($grup) }}"
                  type="checkbox" name="permissions[]"
                  value="{{ $permission->id }}"
                  id="perm_{{ $permission->id }}"
                  {{ in_array($permission->id, old('permissions', $selectedPermissions)) ? 'checked' : '' }}>
                <label class="form-check-label is-fs-088" for="perm_{{ $permission->id }}">
                  {{ $permission->nama }}
                  <code class="ms-1 is-55f680bc">{{ $permission->slug }}</code>
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
            <i class="bi bi-check-lg me-1"></i> Simpan
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
<script src="{{ asset('js/pages/role-form.js') }}"></script>
@endpush
