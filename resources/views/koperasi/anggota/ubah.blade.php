@extends('layouts.aplikasi')

@section('judul', 'Ubah Anggota')

@section('konten')
<div class="row justify-content-center">
  <div class="col-xl-10">
    <div class="form-card">
      <div class="d-flex align-items-center gap-3 mb-4 pb-4 border-bottom">
        <div class="d-flex align-items-center justify-content-center rounded-3 is-1dc83824"
         >
          <i class="bi bi-pencil-square is-deae9fc6"></i>
        </div>
        <div>
          <h5 class="mb-0 fw-700 is-5ca88772">Ubah Data Anggota</h5>
          <small class="text-muted">{{ $anggota->nomor_anggota }} — {{ $anggota->nama }}</small>
        </div>
      </div>

      <form action="{{ route('anggota.update', $anggota) }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PUT')

        @if($errors->any())
          <div class="alert alert-danger mb-4" role="alert">
            <div class="d-flex align-items-center gap-2 mb-2">
              <i class="bi bi-exclamation-triangle-fill"></i>
              <strong>Ada kesalahan pada form. Periksa kembali:</strong>
            </div>
            <ul class="mb-0 ps-3">
              @foreach($errors->all() as $error)
                <li class="is-58180d3d">{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <h6 class="text-muted mb-3 text-uppercase is-0cea37d0">Data Pribadi</h6>
        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <label class="form-label">Nomor Anggota</label>
            <input type="text" name="nomor_anggota"
              class="form-control is-readonly-field @error('nomor_anggota') is-invalid @enderror"
              value="{{ old('nomor_anggota', $anggota->nomor_anggota) }}"
              readonly>
            <div class="form-text">Nomor anggota tidak dapat diubah.</div>
            @error('nomor_anggota')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-6">
            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
            <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror"
              value="{{ old('nama', $anggota->nama) }}">
            @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-6">
            <label class="form-label">NIK <span class="text-danger">*</span></label>
            <input type="text" name="nik" class="form-control @error('nik') is-invalid @enderror"
              value="{{ old('nik', $anggota->nik) }}" maxlength="16">
            @error('nik')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-6">
            <label class="form-label">Tempat Lahir <span class="text-danger">*</span></label>
            <input type="text" name="tempat_lahir" class="form-control @error('tempat_lahir') is-invalid @enderror"
              value="{{ old('tempat_lahir', $anggota->tempat_lahir) }}">
            @error('tempat_lahir')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-3">
            <label class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
            <input type="date" name="tanggal_lahir" class="form-control @error('tanggal_lahir') is-invalid @enderror"
              value="{{ old('tanggal_lahir', $anggota->tanggal_lahir?->format('Y-m-d')) }}">
            @error('tanggal_lahir')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-3">
            <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
            <select name="jenis_kelamin" class="form-select @error('jenis_kelamin') is-invalid @enderror">
              <option value="Laki-laki" {{ old('jenis_kelamin', $anggota->jenis_kelamin) === 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
              <option value="Perempuan" {{ old('jenis_kelamin', $anggota->jenis_kelamin) === 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
            </select>
            @error('jenis_kelamin')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-12">
            <label class="form-label">Alamat Lengkap <span class="text-danger">*</span></label>
            <textarea name="alamat" class="form-control @error('alamat') is-invalid @enderror"
              rows="2">{{ old('alamat', $anggota->alamat) }}</textarea>
            @error('alamat')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
        </div>

        <h6 class="text-muted mb-3 text-uppercase is-0cea37d0">Kontak & Unit Kerja</h6>
        <div class="row g-3 mb-4">
          <div class="col-md-4">
            <label class="form-label">No. Telepon <span class="text-danger">*</span></label>
            <input type="text" name="telepon" class="form-control @error('telepon') is-invalid @enderror"
              value="{{ old('telepon', $anggota->telepon) }}">
            @error('telepon')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-4">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
              value="{{ old('email', $anggota->email) }}">
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-4">
            <label class="form-label">Unit Kerja</label>
            <select name="unit_kerja_id" class="form-select @error('unit_kerja_id') is-invalid @enderror">
              <option value="">— Pilih Unit Kerja —</option>
              @foreach($unitKerja as $uk)
                <option value="{{ $uk->id }}" {{ old('unit_kerja_id', $anggota->unit_kerja_id) == $uk->id ? 'selected' : '' }}>
                  {{ $uk->nama }}{{ $uk->singkatan ? ' ('.$uk->singkatan.')' : '' }}
                </option>
              @endforeach
            </select>
            @error('unit_kerja_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
        </div>

        <h6 class="text-muted mb-3 text-uppercase is-0cea37d0">Data Keanggotaan</h6>
        <div class="row g-3 mb-4">
          <div class="col-md-4">
            <label class="form-label">Jenis Anggota</label>
            <select name="jenis_anggota" class="form-select @error('jenis_anggota') is-invalid @enderror">
              <option value="">— Pilih Jenis —</option>
              @foreach($jenisAnggota as $jenis)
                <option value="{{ $jenis }}" {{ old('jenis_anggota', $anggota->jenis_anggota) === $jenis ? 'selected' : '' }}>{{ $jenis }}</option>
              @endforeach
            </select>
            <div class="form-text">Menentukan nominal simpanan wajib bulanan.</div>
            @error('jenis_anggota')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-4">
            <label class="form-label">Tanggal Bergabung <span class="text-danger">*</span></label>
            <input type="date" name="tanggal_bergabung" class="form-control @error('tanggal_bergabung') is-invalid @enderror"
              value="{{ old('tanggal_bergabung', $anggota->tanggal_bergabung?->format('Y-m-d')) }}">
            @error('tanggal_bergabung')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-4">
            <label class="form-label">Status <span class="text-danger">*</span></label>
            <select name="status" class="form-select @error('status') is-invalid @enderror">
              <option value="aktif" {{ old('status', $anggota->status) === 'aktif' ? 'selected' : '' }}>Aktif</option>
              <option value="tidak_aktif" {{ old('status', $anggota->status) === 'tidak_aktif' ? 'selected' : '' }}>Tidak Aktif</option>
            </select>
            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-4">
            <label class="form-label">Ganti Foto</label>
            <input type="file" name="foto" class="form-control @error('foto') is-invalid @enderror"
              accept="image/*" id="fotoInput">
            <div class="form-text">Kosongkan jika tidak ingin mengganti foto</div>
            @error('foto')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          @if($anggota->foto)
          <div class="col-md-4">
            <label class="form-label">Foto Saat Ini</label><br>
            <img src="{{ Storage::url($anggota->foto) }}" alt="Foto" class="img-thumbnail is-img-max-120" id="fotoPreview">
          </div>
          @else
          <div class="col-md-4 is-cb458930" id="fotoPreviewBox">
            <label class="form-label">Pratinjau</label><br>
            <img id="fotoPreview" src="" alt="Pratinjau" class="img-thumbnail is-4ade2f9e">
          </div>
          @endif
        </div>

        {{-- Akun Login --}}
        <h6 class="text-muted mb-3 text-uppercase is-0cea37d0">Akun Login</h6>
        @php $userTerkait = $anggota->user; @endphp
        <div class="row g-3 mb-4">
          @if($userTerkait)
            {{-- Info akun --}}
            <div class="col-12">
              <div class="d-flex align-items-center gap-3 p-3 rounded-3 border is-b55f8cb2">
                <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0 is-eb7949d6"
                 >
                  <i class="bi bi-person-fill-lock"></i>
                </div>
                <div>
                  <div class="fw-600 is-5ca88772">{{ $userTerkait->name }}</div>
                  <div class="is-571af0c2">Role: {{ $userTerkait->roleModel?->nama ?? $userTerkait->role ?? '—' }}</div>
                </div>
              </div>
            </div>
            {{-- Email --}}
            <div class="col-md-6">
              <label class="form-label">Email Login <span class="text-danger">*</span></label>
              <input type="email" name="login_email"
                class="form-control @error('login_email') is-invalid @enderror"
                value="{{ old('login_email', $userTerkait->email) }}">
              @error('login_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            {{-- Role --}}
            <div class="col-md-6">
              <label class="form-label">Role <span class="text-danger">*</span></label>
              <select name="login_role_id" class="form-select @error('login_role_id') is-invalid @enderror">
                <option value="">-- Pilih Role --</option>
                @foreach($roles as $r)
                  <option value="{{ $r->id }}"
                    {{ old('login_role_id', $userTerkait->role_id) == $r->id ? 'selected' : '' }}>
                    {{ $r->nama }}
                  </option>
                @endforeach
              </select>
              @error('login_role_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            {{-- Password baru --}}
            <div class="col-md-6">
              <label class="form-label">Password Baru</label>
              <div class="input-group">
                <input type="password" name="password_baru" id="passwordBaru"
                  class="form-control @error('password_baru') is-invalid @enderror"
                  placeholder="Kosongkan jika tidak diubah" autocomplete="new-password">
                <button type="button" class="btn btn-outline-secondary" onclick="togglePass('passwordBaru',this)">
                  <i class="bi bi-eye"></i>
                </button>
                @error('password_baru')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="form-text">Minimal 8 karakter. Kosongkan jika tidak ingin mengubah.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Konfirmasi Password Baru</label>
              <div class="input-group">
                <input type="password" name="password_baru_confirmation" id="passwordKonfirmasi"
                  class="form-control"
                  placeholder="Ulangi password baru" autocomplete="new-password">
                <button type="button" class="btn btn-outline-secondary" onclick="togglePass('passwordKonfirmasi',this)">
                  <i class="bi bi-eye"></i>
                </button>
              </div>
            </div>
          @else
            {{-- Buat akun baru --}}
            <div class="col-12">
              <div class="d-flex align-items-center gap-2 p-2 rounded-3 border is-a1f6f938">
                <i class="bi bi-info-circle-fill is-e987c5dc"></i>
                <span class="is-4b3aa9a8">Isi email dan password di bawah untuk membuat akun login anggota ini. Kosongkan semua jika tidak ingin membuat akun.</span>
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Email Login</label>
              <input type="email" name="login_email"
                class="form-control @error('login_email') is-invalid @enderror"
                value="{{ old('login_email', $anggota->email) }}"
                placeholder="email@contoh.com">
              @error('login_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
              <label class="form-label">Role</label>
              <select name="login_role_id" class="form-select @error('login_role_id') is-invalid @enderror">
                <option value="">-- Pilih Role --</option>
                @foreach($roles as $r)
                  <option value="{{ $r->id }}"
                    {{ old('login_role_id') == $r->id ? 'selected' : '' }}
                    {{ $r->slug === 'anggota' ? 'selected' : '' }}>
                    {{ $r->nama }}
                  </option>
                @endforeach
              </select>
              @error('login_role_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
              <label class="form-label">Password</label>
              <div class="input-group">
                <input type="password" name="password_baru" id="passwordBaru"
                  class="form-control @error('password_baru') is-invalid @enderror"
                  placeholder="Minimal 8 karakter" autocomplete="new-password">
                <button type="button" class="btn btn-outline-secondary" onclick="togglePass('passwordBaru',this)">
                  <i class="bi bi-eye"></i>
                </button>
                @error('password_baru')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Konfirmasi Password</label>
              <div class="input-group">
                <input type="password" name="password_baru_confirmation" id="passwordKonfirmasi"
                  class="form-control"
                  placeholder="Ulangi password" autocomplete="new-password">
                <button type="button" class="btn btn-outline-secondary" onclick="togglePass('passwordKonfirmasi',this)">
                  <i class="bi bi-eye"></i>
                </button>
              </div>
            </div>
          @endif
        </div>

        <div class="d-flex gap-2 mt-4 pt-4 border-top">
          <button type="submit" class="btn btn-primary px-4">
            <i class="bi bi-check-lg me-2"></i> Simpan
          </button>
          <a href="{{ route('anggota.show', $anggota) }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-2"></i> Kembali
          </a>
        </div>
      </form>
    </div>
  </div>
</div>

@push('js')
<script src="{{ asset('js/pages/anggota-form.js') }}"></script>
@endpush
@endsection