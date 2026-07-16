@extends('layouts.aplikasi')

@section('judul', 'Manajemen User')

@section('breadcrumb')
  <li class="breadcrumb-item active">Manajemen User</li>
@endsection

@section('konten')
@php
  $sortBy = $sortBy ?? request('sort', 'created_at');
  $sortDir = $sortDir ?? request('dir', 'desc');
@endphp
<div class="row justify-content-center">
  <div class="col-xl-10">

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
      <div class="welcome-icon-wrap">
        <i class="bi bi-people-fill"></i>
      </div>
    </div>
  </div>
</div>

<div class="form-card p-0 overflow-hidden">
  {{-- Header --}}
  <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom">
    <h6 class="mb-0 fw-700 is-5ca88772">
      <i class="bi bi-people-fill me-2 is-bc519fd0"></i>Daftar User
    </h6>
  </div>

  {{-- Search --}}
  <div class="px-4 py-3 border-bottom is-bf063d1c">
    <form method="GET" action="{{ route('user.index') }}">
      <div class="input-group is-0894cd2e">
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
    @php
      $sUrl = fn($k) => request()->fullUrlWithQuery(['sort' => $k, 'dir' => ($sortBy===$k && $sortDir==='asc') ? 'desc' : 'asc']);
      $sIco = fn($k) => $sortBy===$k
        ? ($sortDir==='asc'
            ? '<i class="bi bi-sort-up ms-1 is-0a789cad"></i>'
            : '<i class="bi bi-sort-down ms-1 is-0a789cad"></i>')
        : '<i class="bi bi-arrow-down-up text-muted opacity-50 ms-1 is-bc9efe64"></i>';
    @endphp
    <table class="koperasi-table mb-0">
      <thead>
        <tr>
          <th>#</th>
          <th><a href="{{ $sUrl('name') }}" class="text-decoration-none text-reset d-inline-flex align-items-center">Nama / Username {!! $sIco('name') !!}</a></th>
          <th><a href="{{ $sUrl('email') }}" class="text-decoration-none text-reset d-inline-flex align-items-center">Email {!! $sIco('email') !!}</a></th>
          <th>No. WA</th>
          <th>Role</th>
          <th>Terhubung ke Anggota</th>
          <th><a href="{{ $sUrl('created_at') }}" class="text-decoration-none text-reset d-inline-flex align-items-center">Dibuat {!! $sIco('created_at') !!}</a></th>
          <th class="text-center">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($users as $user)
          <tr>
            <td class="text-muted is-bb0425a5">{{ $users->firstItem() + $loop->index }}</td>
            <td>
              <div class="fw-600 is-5ca88772">{{ $user->name }}</div>
              <div class="text-muted is-bb0425a5">{{ $user->username ?? '—' }}</div>
            </td>
            <td class="is-1e28117f">{{ $user->email }}</td>
            <td class="is-1e28117f">
              @if($user->no_wa)
                <a href="tel:{{ $user->no_wa }}" class="text-decoration-none is-text-374151">
                  <i class="bi bi-telephone me-1 is-81eeaec5"></i>{{ $user->no_wa }}
                </a>
              @else
                <span class="text-muted">—</span>
              @endif
            </td>
            <td>
              @if($user->role === 'admin')
                <span class="badge bg-danger">Admin</span>
              @else
                <span class="badge bg-primary">{{ $user->roleModel->nama ?? ucfirst($user->role) }}</span>
              @endif
            </td>
            <td>
              @if($user->anggota)
                <div class="is-099dcab2">{{ $user->anggota->nama }}</div>
                <div class="text-muted is-f4b9997e"><code>{{ $user->anggota->nomor_anggota }}</code></div>
              @else
                <span class="text-muted is-6c660c40">— tidak terhubung —</span>
              @endif
            </td>
            <td>
              <span class="is-7e7d71c3">
                <i class="bi bi-calendar3 is-3b8e2be5"></i>
                {{ $user->created_at->format('d M Y') }}
              </span>
            </td>
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
              <i class="bi bi-people is-a4ea9cc4"></i>
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

  </div>
</div>
@endsection
