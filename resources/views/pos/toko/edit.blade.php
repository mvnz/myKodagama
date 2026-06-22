@extends('layouts.pos')
@section('judul','Edit Supplier')
@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('pos.toko.index') }}">Supplier</a></li>
  <li class="breadcrumb-item"><a href="{{ route('pos.toko.show', $toko) }}">{{ $toko->nama }}</a></li>
  <li class="breadcrumb-item active">Edit</li>
@endsection

@section('konten')
<div class="d-flex justify-content-between align-items-center mb-4">
  <h5 class="fw-bold mb-0 is-feb6723d">
    <i class="bi bi-pencil-square me-2 is-f571fe9b"></i>Edit Supplier
  </h5>
  <a href="{{ route('pos.toko.show', $toko) }}" class="btn btn-sm btn-outline-secondary">
    <i class="bi bi-arrow-left me-1"></i>Kembali
  </a>
</div>

<div class="row justify-content-center">
  <div class="col-lg-7">
    <div class="form-card">
      <form method="POST" action="{{ route('pos.toko.update', $toko) }}">
        @csrf @method('PUT')
        @include('pos.toko._form')
        <div class="d-flex gap-2 mt-4">
          <a href="{{ route('pos.toko.show', $toko) }}" class="btn btn-outline-secondary flex-fill">Batal</a>
          <button type="submit" class="btn-simpan flex-fill">
            <i class="bi bi-check-lg me-1"></i>Simpan Perubahan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
