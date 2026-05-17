@extends('layouts.aplikasi')

@section('judul', 'Akun Belum Terdaftar')

@section('konten')
<div class="text-center py-5">
  <div class="mb-4">
    <i class="bi bi-person-x" style="font-size:4rem;color:#9ca3af"></i>
  </div>
  <h4 class="fw-700" style="color:#112344">Akun Belum Dikaitkan</h4>
  <p class="text-muted mb-4">Akun Anda belum dikaitkan dengan data anggota koperasi.<br>Hubungi pengurus untuk mengaitkan akun Anda.</p>
  <form method="POST" action="{{ route('keluar') }}">
    @csrf
    <button type="submit" class="btn btn-outline-secondary">
      <i class="bi bi-box-arrow-left me-1"></i>Keluar
    </button>
  </form>
</div>
@endsection
