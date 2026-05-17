@extends('layouts.aplikasi')

@section('judul', 'Tambah User')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('user.index') }}">Manajemen User</a></li>
  <li class="breadcrumb-item active">Tambah User</li>
@endsection

@section('konten')
<div class="row g-4">
  <div class="col-xl-8">
    <div class="form-card">
      <h6 class="section-title mb-4">
        <i class="bi bi-person-plus me-2" style="color:#175cdd"></i>Tambah User Baru
      </h6>

      <form method="POST" action="{{ route('user.store') }}">
        @csrf

        @include('user._form', ['isEdit' => false])

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
