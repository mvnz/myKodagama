@extends('layouts.pos')
@section('judul','Tambah Supplier')
@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('pos.toko.index') }}">Supplier</a></li>
  <li class="breadcrumb-item active">Tambah</li>
@endsection

@section('konten')
<div class="d-flex justify-content-between align-items-center mb-4">
  <h5 class="fw-bold mb-0 is-feb6723d">
    <i class="bi bi-truck me-2 is-f571fe9b"></i>Tambah Supplier
  </h5>
  <a href="{{ route('pos.toko.index') }}" class="btn btn-sm btn-outline-secondary">
    <i class="bi bi-arrow-left me-1"></i>Kembali
  </a>
</div>

<div class="row justify-content-center">
  <div class="col-lg-7">
    <div class="form-card">
      <form method="POST" action="{{ route('pos.toko.store') }}">
        @csrf
        @include('pos.toko._form')
        <div class="d-flex gap-2 mt-4">
          <a href="{{ route('pos.toko.index') }}" class="btn btn-outline-secondary flex-fill">Batal</a>
          <button type="submit" class="btn-simpan flex-fill">
            <i class="bi bi-plus-lg me-1"></i>Simpan Supplier
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
