@extends('layouts.aplikasi')

@section('judul', 'Pengaturan Role')

@section('breadcrumb')
  <li class="breadcrumb-item active">Pengaturan Role</li>
@endsection

@section('konten')

{{-- Page Banner --}}
<div class="dashboard-welcome mb-4">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
      <div class="welcome-label mb-1">Manajemen Sistem</div>
      <h4 class="mb-1 fw-700">Role & Hak Akses</h4>
      <p class="mb-0 welcome-date">
        <i class="bi bi-calendar3 me-1"></i>
        {{ now()->translatedFormat('l, d F Y') }}
      </p>
    </div>
    <div class="d-flex align-items-center gap-3">
      <a href="{{ route('role.create') }}" class="btn btn-light fw-600">
        <i class="bi bi-plus-circle me-1"></i> Tambah Role
      </a>
      <div class="welcome-icon-wrap">
        <i class="bi bi-shield-lock-fill"></i>
      </div>
    </div>
  </div>
</div>

@if($roles->isEmpty())
  <div class="form-card text-center py-5">
    <i class="bi bi-shield-x" style="font-size:3rem;color:#c4c9d4"></i>
    <p class="mt-3 text-muted">Belum ada role yang dibuat. Klik <strong>Tambah Role</strong> untuk memulai.</p>
  </div>
@else
  <div class="row g-4">
    @foreach($roles as $role)
    <div class="col-lg-4 col-md-6">
      <div class="form-card h-100 d-flex flex-column" style="border-top:4px solid #175cdd">
        <div class="d-flex align-items-start justify-content-between mb-3">
          <div>
            <h6 class="fw-700 mb-1" style="color:#112344;font-size:1rem">
              <i class="bi bi-shield-fill-check me-2" style="color:#175cdd"></i>{{ $role->nama }}
            </h6>
            @if($role->slug === 'admin')
              <span class="badge" style="background:#fef3c7;color:#92400e;font-size:.72rem">
                <i class="bi bi-star-fill me-1"></i>Super Admin
              </span>
            @endif
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
          <p class="text-muted small mb-3">{{ $role->deskripsi }}</p>
        @endif

        <div class="mt-auto pt-3 border-top d-flex align-items-center justify-content-between">
          <span class="small text-muted">
            <i class="bi bi-key-fill me-1" style="color:#175cdd"></i>
            {{ $role->permissions_count }} hak akses
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
@endsection
