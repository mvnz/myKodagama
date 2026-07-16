@extends('layouts.aplikasi')

@section('judul', 'Tambah User')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('user.index') }}">Manajemen User</a></li>
  <li class="breadcrumb-item active">Tambah User</li>
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
</style>
@endpush

@section('konten')
<div class="row g-4">
  <div class="col-xl-8">
    <div class="form-card user-panel">
      <h6 class="section-title mb-4">
        <i class="bi bi-person-plus me-2 is-bc519fd0"></i>Tambah User Baru
      </h6>

      <form method="POST" action="{{ route('user.store') }}">
        @csrf

        @include('koperasi.user._form', ['isEdit' => false])

        <div class="d-flex gap-2 justify-content-end mt-4 pt-3 border-top">
          <a href="{{ route('user.index') }}" class="btn btn-outline-secondary">Batal</a>
          <button type="submit" class="btn btn-primary px-4">
            <i class="bi bi-check-lg me-1"></i> Simpan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
