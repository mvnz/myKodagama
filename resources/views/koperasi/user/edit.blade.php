@extends('layouts.aplikasi')

@section('judul', 'Edit User — ' . $user->name)

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('user.index') }}">Manajemen User</a></li>
  <li class="breadcrumb-item active">Edit User</li>
@endsection

@push('css')
<style>
  .user-panel {
    border: 1px solid #dce7f7;
    border-radius: 16px;
    background:
      radial-gradient(circle at 96% 0, rgba(23, 92, 221, .08), transparent 38%),
      #fff;
    box-shadow: 0 14px 30px rgba(17, 35, 68, .08);
  }

  .user-panel .section-title {
    color: #112344;
    font-weight: 800;
    margin-bottom: 1.1rem;
  }

  .user-related {
    border: 1px solid #dce7f7;
    border-radius: 16px;
    box-shadow: 0 12px 26px rgba(17, 35, 68, .08);
    background: #fff;
  }

  .user-related .meta {
    display: flex;
    flex-direction: column;
    gap: .45rem;
    font-size: .9rem;
    color: #2f4468;
  }

  .user-related .meta strong { color: #112344; }
</style>
@endpush

@section('konten')
<div class="row g-4">
  <div class="col-xl-8">
    <div class="form-card user-panel">
      <h6 class="section-title mb-4">
        <i class="bi bi-pencil-square me-2 is-bc519fd0"></i>Edit User: {{ $user->name }}
      </h6>

      <form method="POST" action="{{ route('user.update', $user) }}">
        @csrf @method('PUT')

        @include('koperasi.user._form', ['isEdit' => true])

        <div class="d-flex gap-2 justify-content-end mt-4 pt-3 border-top">
          <a href="{{ route('user.index') }}" class="btn btn-outline-secondary">Batal</a>
          <button type="submit" class="btn btn-primary px-4">
            <i class="bi bi-check-lg me-1"></i> Simpan
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- Info anggota terhubung --}}
  @if($user->anggota)
    <div class="col-xl-4">
      <div class="form-card user-related">
        <h6 class="section-title mb-3">
          <i class="bi bi-person-badge-fill me-2 is-29fb9df2"></i>Data Anggota Terhubung
        </h6>
        <div class="meta">
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
