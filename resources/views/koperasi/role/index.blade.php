@extends('layouts.aplikasi')

@section('judul', 'Pengaturan Role')

@section('breadcrumb')
  <li class="breadcrumb-item active">Pengaturan Role</li>
@endsection

@section('konten')
<div class="row justify-content-center">
  <div class="col-xl-10">

{{-- Page Banner --}}
<div class="dashboard-welcome mb-3">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
      <div class="welcome-label mb-1">Manajemen Sistem</div>
      <h4 class="mb-1 fw-700">Role & Hak Akses</h4>
      <p class="mb-0 welcome-date">
        <i class="bi bi-calendar3 me-1"></i>
        {{ now()->translatedFormat('l, d F Y') }}
      </p>
    </div>
    <div class="welcome-icon-wrap">
      <i class="bi bi-shield-lock-fill"></i>
    </div>
  </div>
</div>

<div class="form-card">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <div>
      <h6 class="mb-0 fw-700 is-5ca88772">
        <i class="bi bi-shield-lock-fill me-1 is-bc519fd0"></i>
        Daftar Role
      </h6>
      <small class="text-muted">Kelola role dan hak akses pengguna sistem</small>
    </div>
    <a href="{{ route('role.create') }}" class="btn btn-sm btn-outline-primary">
      <i class="bi bi-plus-lg me-1"></i> Tambah Role
    </a>
  </div>

  @if($roles->isEmpty())
    <div class="text-center py-5">
      <i class="bi bi-shield-x is-1f42df73"></i>
      <p class="mt-3 text-muted">Belum ada role yang dibuat. Klik <strong>Tambah Role</strong> untuk memulai.</p>
    </div>
  @else
    <div class="row g-3">
      @foreach($roles as $role)
      <div class="col-lg-4 col-md-6">
        <div class="h-100 d-flex flex-column rounded-3 p-3 bg-white is-f90a5b32"
            >

          <div class="d-flex align-items-start justify-content-between mb-3">
            <div class="d-flex align-items-center gap-3">
              <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 is-3ca5af72"
                  >
                <i class="bi bi-shield-fill-check is-09cb687d"></i>
              </div>
              <div>
                <div class="fw-700 is-9bd992ef">{{ $role->nama }}</div>
                @if($role->slug === 'admin')
                  <span class="badge mt-1 is-d524294b">
                    <i class="bi bi-star-fill me-1"></i>Super Admin
                  </span>
                @endif
              </div>
            </div>
            <div class="dropdown">
              <button class="btn btn-sm btn-outline-secondary border-0" data-bs-toggle="dropdown">
                <i class="bi bi-three-dots-vertical"></i>
              </button>
              <ul class="dropdown-menu dropdown-menu-end">
                <li>
                  <a class="dropdown-item" href="{{ route('role.edit', $role) }}">
                    <i class="bi bi-pencil me-2 text-primary"></i>Edit Role
                  </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                  <form action="{{ route('role.destroy', $role) }}" method="POST"
                    onsubmit="return confirm('Hapus role \'{{ $role->nama }}\'? Tindakan ini tidak dapat dibatalkan.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="dropdown-item text-danger">
                      <i class="bi bi-trash me-2"></i>Hapus Role
                    </button>
                  </form>
                </li>
              </ul>
            </div>
          </div>

          @if($role->deskripsi)
            <p class="small text-muted mb-3">{{ $role->deskripsi }}</p>
          @else
            <p class="small text-muted fst-italic mb-3">Tidak ada deskripsi</p>
          @endif

          <div class="mt-auto d-flex align-items-center justify-content-between pt-2 is-c1ea3bca"
              >
            <span class="small fw-600 is-bc519fd0">
              <i class="bi bi-key-fill me-1"></i>{{ $role->permissions_count }} hak akses
            </span>
            <a href="{{ route('role.edit', $role) }}" class="btn btn-sm btn-outline-primary">
              <i class="bi bi-pencil me-1"></i>Edit
            </a>
          </div>
        </div>
      </div>
      @endforeach
    </div>
  @endif
</div>
  </div>
</div>
@endsection
