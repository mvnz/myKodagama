@extends('layouts.pos')
@section('judul','Edit Produk')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('pos.produk.index') }}">Produk</a></li>
<li class="breadcrumb-item active">Edit</li>
@endsection

@section('konten')
<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="form-card">
      <h5 class="fw-bold mb-4 is-feb6723d">
        <i class="bi bi-pencil-fill me-2 is-f571fe9b"></i>Edit Produk
        <small class="text-muted fw-normal is-1e28117f">{{ $produk->nama }}</small>
      </h5>

      <form method="POST" action="{{ route('pos.produk.update', $produk) }}">
        @csrf @method('PUT')
        @include('pos.produk._form', ['kode' => $produk->kode])
        <div class="d-flex gap-2 mt-4">
          <button class="btn-simpan">Perbarui</button>
          <a href="{{ route('pos.produk.index') }}" class="btn btn-outline-secondary">Batal</a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
