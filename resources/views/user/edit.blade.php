@extends('layouts.aplikasi')

@section('judul', 'Edit User — ' . $user->name)

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('user.index') }}">Manajemen User</a></li>
  <li class="breadcrumb-item active">Edit User</li>
@endsection

@section('konten')
<div class="row g-4">
  <div class="col-xl-8">
    <div class="form-card">
      <h6 class="section-title mb-4">
        <i class="bi bi-pencil-square me-2" style="color:#175cdd"></i>Edit User: {{ $user->name }}
      </h6>

      <form method="POST" action="{{ route('user.update', $user) }}">
        @csrf @method('PUT')

        @include('user._form', ['isEdit' => true])

        <div class="d-flex gap-2 justify-content-end mt-4 pt-3 border-top">
          <a href="{{ route('user.index') }}" class="btn btn-outline-secondary">Batal</a>
          <button type="submit" class="btn btn-primary px-4">
            <i class="bi bi-check-lg me-1"></i> Simpan Perubahan
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- Info anggota terhubung --}}
  @if($user->anggota)
    <div class="col-xl-4">
      <div class="form-card">
        <h6 class="section-title mb-3">
          <i class="bi bi-person-badge-fill me-2" style="color:#10b981"></i>Data Anggota Terhubung
        </h6>
        <div class="d-flex flex-column gap-2" style="font-size:.85rem">
          <div><span class="text-muted">Nomor:</span> <strong>{{ $user->anggota->nomor_anggota }}</strong></div>
          <div><span class="text-muted">Nama:</span> {{ $user->anggota->nama }}</div>
          <div><span class="text-muted">Telepon:</span> {{ $user->anggota->telepon }}</div>
          <div><span class="text-muted">Status:</span>
            <span class="badge {{ $user->anggota->status === 'aktif' ? 'bg-success' : 'bg-secondary' }}">
              {{ ucfirst($user->anggota->status) }}
            </span>
          </div>
        </div>
        <a href="{{ route('anggota.show', $user->anggota) }}" class="btn btn-sm btn-outline-success mt-3 w-100">
          <i class="bi bi-box-arrow-up-right me-1"></i> Lihat Detail Anggota
        </a>
      </div>
    </div>
  @endif
</div>
@endsection
