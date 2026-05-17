@extends('layouts.aplikasi')

@section('judul', 'Manajemen User')

@section('breadcrumb')
  <li class="breadcrumb-item active">Manajemen User</li>
@endsection

@section('konten')

@if(session('sukses'))
  <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
    <i class="bi bi-check-circle-fill me-2"></i>{{ session('sukses') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
@endif

{{-- Page Banner --}}
<div class="dashboard-welcome mb-4">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
      <div class="welcome-label mb-1">Manajemen Pengguna</div>
      <h4 class="mb-1 fw-700">Data User Sistem</h4>
      <p class="mb-0 welcome-date">
        <i class="bi bi-calendar3 me-1"></i>
        {{ now()->translatedFormat('l, d F Y') }}
      </p>
    </div>
    <div class="d-flex align-items-center gap-3">
      <a href="{{ route('user.create') }}" class="btn btn-light fw-600">
        <i class="bi bi-person-plus me-1"></i> Tambah User
      </a>
      <div class="welcome-icon-wrap">
        <i class="bi bi-people-fill"></i>
      </div>
    </div>
  </div>
</div>

{{-- Stat Cards --}}
<div class="row g-4 mb-4">
  <div class="col-xl-3 col-sm-6">
    <div class="kstat-card kstat-blue">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-people-fill"></i></div>
      <div class="kstat-value">{{ number_format($statsUser['total']) }}</div>
      <div class="kstat-label">Total Pengguna</div>
      <div class="kstat-trend"><i class="bi bi-person-fill me-1"></i> Semua user terdaftar</div>
    </div>
  </div>
  <div class="col-xl-3 col-sm-6">
    <div class="kstat-card kstat-green">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-person-badge-fill"></i></div>
      <div class="kstat-value">{{ number_format($statsUser['admin']) }}</div>
      <div class="kstat-label">Administrator</div>
      <div class="kstat-trend"><i class="bi bi-shield-fill me-1"></i> Akses penuh</div>
    </div>
  </div>
  <div class="col-xl-3 col-sm-6">
    <div class="kstat-card kstat-amber">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-person-workspace"></i></div>
      <div class="kstat-value">{{ number_format($statsUser['petugas']) }}</div>
      <div class="kstat-label">Petugas</div>
      <div class="kstat-trend"><i class="bi bi-briefcase-fill me-1"></i> Staf koperasi</div>
    </div>
  </div>
  <div class="col-xl-3 col-sm-6">
    <div class="kstat-card kstat-red">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-person-fill"></i></div>
      <div class="kstat-value">{{ number_format($statsUser['anggota']) }}</div>
      <div class="kstat-label">Anggota</div>
      <div class="kstat-trend"><i class="bi bi-person-check-fill me-1"></i> User anggota</div>
    </div>
  </div>
</div>

<div class="form-card p-0 overflow-hidden">
  {{-- Header --}}
  <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom">
    <h6 class="mb-0 fw-700" style="color:#112344">
      <i class="bi bi-people-fill me-2" style="color:#175cdd"></i>Daftar User
    </h6>
  </div>

  {{-- Search --}}
  <div class="px-4 py-3 border-bottom" style="background:#f8faff">
    <form method="GET" action="{{ route('user.index') }}">
      <div class="input-group" style="max-width:360px">
        <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
        <input type="text" name="q" value="{{ $q }}" class="form-control"
          placeholder="Cari nama, email, username…">
        @if($q)
          <a href="{{ route('user.index') }}" class="btn btn-outline-secondary">Reset</a>
        @endif
      </div>
    </form>
  </div>

  {{-- Table --}}
  <div class="table-responsive">
    <table class="koperasi-table mb-0">
      <thead>
        <tr>
          <th>#</th>
          <th>Nama / Username</th>
          <th>Email</th>
          <th>No. WA</th>
          <th>Role</th>
          <th>Terhubung ke Anggota</th>
          <th>Dibuat</th>
          <th class="text-center">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($users as $user)
          <tr>
            <td class="text-muted" style="font-size:.78rem">{{ $users->firstItem() + $loop->index }}</td>
            <td>
              <div class="fw-600" style="color:#112344">{{ $user->name }}</div>
              <div class="text-muted" style="font-size:.78rem">@{{ $user->username ?? '—' }}</div>
            </td>
            <td style="font-size:.85rem">{{ $user->email }}</td>
            <td style="font-size:.85rem">{{ $user->no_wa ?? '—' }}</td>
            <td>
              @if($user->role === 'admin')
                <span class="badge bg-danger">Admin</span>
              @else
                <span class="badge bg-primary">{{ $user->roleModel->nama ?? ucfirst($user->role) }}</span>
              @endif
            </td>
            <td>
              @if($user->anggota)
                <div style="font-size:.83rem;font-weight:600;color:#112344">{{ $user->anggota->nama }}</div>
                <div class="text-muted" style="font-size:.73rem">{{ $user->anggota->nomor_anggota }}</div>
              @else
                <span class="text-muted" style="font-size:.8rem">— tidak terhubung —</span>
              @endif
            </td>
            <td class="text-muted" style="font-size:.78rem">{{ $user->created_at->format('d M Y') }}</td>
            <td class="text-center">
              <div class="d-flex gap-1 justify-content-center">
                <a href="{{ route('user.edit', $user) }}" class="btn btn-sm btn-outline-primary px-2" title="Edit">
                  <i class="bi bi-pencil"></i>
                </a>
                @if($user->id !== auth()->id())
                  <form method="POST" action="{{ route('user.destroy', $user) }}"
                    onsubmit="return confirm('Hapus user {{ addslashes($user->name) }}?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger px-2" title="Hapus">
                      <i class="bi bi-trash"></i>
                    </button>
                  </form>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" class="text-center text-muted py-5">
              <i class="bi bi-people" style="font-size:2rem;opacity:.3"></i>
              <p class="mt-2 mb-0">Tidak ada user ditemukan.</p>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if($users->hasPages())
    <div class="px-4 py-3 border-top">
      {{ $users->links() }}
    </div>
  @endif
</div>

@endsection
