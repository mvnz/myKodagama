@extends('layouts.pos')
@section('judul','Tambah Produk')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('pos.produk.index') }}">Produk</a></li>
<li class="breadcrumb-item active">Tambah</li>
@endsection

@section('konten')
<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="form-card">
      <h5 class="fw-bold mb-4 is-feb6723d">
        <i class="bi bi-box-seam-fill me-2 is-f571fe9b"></i>Tambah Produk Baru
      </h5>

      <form method="POST" action="{{ route('pos.produk.store') }}">
        @csrf
        @include('pos.produk._form', ['produk' => null, 'kode' => $kode])
        <div class="d-flex gap-2 mt-4">
          <button class="btn-simpan">Simpan</button>
          <a href="{{ route('pos.produk.index') }}" class="btn btn-outline-secondary">Batal</a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
