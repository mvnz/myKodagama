@if($errors->any())
  <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ $errors->first() }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
@endif

<div class="row g-3">

  {{-- Nama Lengkap --}}
  <div class="col-md-6">
    <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
    <input type="text" name="name" id="nameField"
      value="{{ old('name', $user->name ?? '') }}"
      class="form-control @error('name') is-invalid @enderror"
      placeholder="Nama lengkap">
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>

  {{-- Username --}}
  <div class="col-md-6">
    <label class="form-label">Username <span class="text-danger">*</span></label>
    <div class="input-group">
      <span class="input-group-text"><i class="bi bi-at"></i></span>
      <input type="text" name="username" id="usernameField"
        value="{{ old('username', $user->username ?? '') }}"
        class="form-control @error('username') is-invalid @enderror"
        placeholder="username_unik">
      @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="form-text">Hanya huruf, angka, - dan _</div>
  </div>

  {{-- Email --}}
  <div class="col-md-6">
    <label class="form-label">Email <span class="text-danger">*</span></label>
    <div class="input-group">
      <span class="input-group-text"><i class="bi bi-envelope"></i></span>
      <input type="email" name="email"
        value="{{ old('email', $user->email ?? '') }}"
        class="form-control @error('email') is-invalid @enderror"
        placeholder="email@contoh.com">
      @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
  </div>

  {{-- No WA --}}
  <div class="col-md-6">
    <label class="form-label">No. WhatsApp</label>
    <div class="input-group">
      <span class="input-group-text"><i class="bi bi-whatsapp"></i></span>
      <input type="text" name="no_wa"
        value="{{ old('no_wa', $user->no_wa ?? '') }}"
        class="form-control @error('no_wa') is-invalid @enderror"
        placeholder="cth: 08123456789" inputmode="tel">
      @error('no_wa')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
  </div>

  {{-- Role --}}
  <div class="col-md-6">
    <label class="form-label">Role <span class="text-danger">*</span></label>
    <select name="role_id" class="form-select @error('role_id') is-invalid @enderror">
      <option value="" disabled {{ old('role_id', $user->role_id ?? '') ? '' : 'selected' }}>— Pilih role —</option>
      @foreach($roles as $role)
        <option value="{{ $role->id }}"
          {{ old('role_id', $user->role_id ?? '') == $role->id ? 'selected' : '' }}>
          {{ $role->nama }}
        </option>
      @endforeach
    </select>
    @error('role_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>

  {{-- Terhubung ke Anggota --}}
  <div class="col-md-6">
    <label class="form-label">Terhubung ke Anggota</label>
    <select name="anggota_id" class="form-select @error('anggota_id') is-invalid @enderror">
      <option value="">— Tidak terhubung —</option>
      @foreach($anggota as $a)
        <option value="{{ $a->id }}"
          {{ old('anggota_id', $user->anggota_id ?? '') == $a->id ? 'selected' : '' }}>
          {{ $a->nomor_anggota }} – {{ $a->nama }}
        </option>
      @endforeach
    </select>
    <div class="form-text">Hanya anggota yang belum punya akun yang ditampilkan.</div>
    @error('anggota_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>

  {{-- Password --}}
  <div class="col-md-6">
    <label class="form-label">
      Kata Sandi {{ $isEdit ? '' : '<span class="text-danger">*</span>' }}
      {!! $isEdit ? '<span class="text-muted is-bb0425a5">(kosongkan jika tidak diubah)</span>' : '' !!}
    </label>
    <div class="input-group">
      <span class="input-group-text"><i class="bi bi-lock"></i></span>
      <input type="password" name="password" id="passwordField"
        class="form-control @error('password') is-invalid @enderror"
        placeholder="Minimal 8 karakter">
      <button class="btn btn-outline-secondary" type="button" id="togglePassword" tabindex="-1">
        <i class="bi bi-eye"></i>
      </button>
      @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
  </div>

  {{-- Konfirmasi Password --}}
  <div class="col-md-6">
    <label class="form-label">Konfirmasi Kata Sandi</label>
    <div class="input-group">
      <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
      <input type="password" name="password_confirmation" id="passwordConfirmField"
        class="form-control"
        placeholder="Ulangi kata sandi">
      <button class="btn btn-outline-secondary" type="button" id="toggleConfirm" tabindex="-1">
        <i class="bi bi-eye"></i>
      </button>
    </div>
  </div>

</div>

@push('js')
<script>var _isEdit = {{ $isEdit ? 'true' : 'false' }};</script>
<script src="{{ asset('js/pages/user-form.js') }}"></script>
@endpush