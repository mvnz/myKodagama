@extends('layouts.aplikasi')

@section('judul', 'Profil Saya')

@section('breadcrumb')
  <li class="breadcrumb-item active">Profil</li>
@endsection

@section('konten')
<div class="row g-4">

  {{-- Kartu info user --}}
  <div class="col-lg-4">
    <div class="form-card text-center">
      <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center mx-auto mb-3"
           style="width:96px;height:96px;">
        <span class="text-white fw-700 fs-3">{{ strtoupper(substr($user->name,0,1)) }}</span>
      </div>
      <div class="fw-700 fs-5 is-5ca88772">{{ $user->name }}</div>
      <div class="text-muted small">{{ $user->email }}</div>
      <div class="mt-2">
        <span class="badge bg-primary rounded-pill d-inline-block">{{ $user->roleModel?->nama ?? $user->role }}</span>
      </div>
      @if($user->anggota)
        <div class="mt-3">
          <a href="{{ route('anggota.kartu', $user->anggota) }}" target="_blank" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-credit-card-2-front me-1"></i>Kartu Anggota
          </a>
        </div>
      @endif
      @if($user->no_wa)
        <div class="mt-2 text-muted small"><i class="bi bi-whatsapp text-success me-1"></i>{{ $user->no_wa }}</div>
      @endif
    </div>
  </div>

  <div class="col-lg-8">

    {{-- Edit profil --}}
    <div class="form-card mb-4">
      <h6 class="fw-700 mb-3 is-5ca88772"><i class="bi bi-person-gear me-2"></i>Edit Profil</h6>

      @if(session('sukses'))
        <div class="alert alert-success py-2">{{ session('sukses') }}</div>
      @endif

      @if($errors->has('name') || $errors->has('email') || $errors->has('no_wa'))
        <div class="alert alert-danger py-2">
          <ul class="mb-0 ps-3">
            @foreach($errors->only(['name','email','no_wa']) as $e)<li>{{ $e }}</li>@endforeach
          </ul>
        </div>
      @endif

      <form action="{{ route('profil.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-3">
          <div class="col-sm-6">
            <label class="form-label fw-600">Nama Lengkap <span class="text-danger">*</span></label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}"
                   class="form-control @error('name') is-invalid @enderror">
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-sm-6">
            <label class="form-label fw-600">Email <span class="text-danger">*</span></label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}"
                   class="form-control @error('email') is-invalid @enderror">
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-sm-6">
            <label class="form-label fw-600">Nomor WhatsApp</label>
            <input type="text" name="no_wa" value="{{ old('no_wa', $user->no_wa) }}"
                   placeholder="08xxxxxxxxxx"
                   class="form-control @error('no_wa') is-invalid @enderror">
            @error('no_wa')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
        </div>

        <div class="mt-3">
          <button type="submit" class="btn btn-primary btn-sm">
            <i class="bi bi-check-lg me-1"></i> Simpan Perubahan
          </button>
        </div>
      </form>
    </div>

    {{-- Ganti password --}}
    <div class="form-card">
      <h6 class="fw-700 mb-3 is-5ca88772"><i class="bi bi-shield-lock me-2"></i>Ganti Password</h6>

      @if(session('sukses_password'))
        <div class="alert alert-success py-2">{{ session('sukses_password') }}</div>
      @endif

      @if($errors->has('password_lama') || $errors->has('password_baru'))
        <div class="alert alert-danger py-2">
          <ul class="mb-0 ps-3">
            @foreach($errors->only(['password_lama','password_baru']) as $e)<li>{{ $e }}</li>@endforeach
          </ul>
        </div>
      @endif

      <form action="{{ route('profil.password') }}" method="POST" autocomplete="off">
        @csrf
        @method('PUT')

        <div class="row g-3">
          <div class="col-sm-6">
            <label class="form-label fw-600">Password Lama <span class="text-danger">*</span></label>
            <input type="password" name="password_lama" autocomplete="current-password"
                   class="form-control @error('password_lama') is-invalid @enderror">
            @error('password_lama')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-sm-6">
            <label class="form-label fw-600">Password Baru <span class="text-danger">*</span></label>
            <input type="password" name="password_baru" autocomplete="new-password"
                   class="form-control @error('password_baru') is-invalid @enderror">
            @error('password_baru')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-sm-6">
            <label class="form-label fw-600">Konfirmasi Password Baru <span class="text-danger">*</span></label>
            <input type="password" name="password_baru_confirmation" autocomplete="new-password"
                   class="form-control">
          </div>
        </div>

        <div class="mt-3">
          <button type="submit" class="btn btn-warning btn-sm">
            <i class="bi bi-lock me-1"></i> Ubah Password
          </button>
        </div>
      </form>
    </div>

  </div>
</div>
@endsection
