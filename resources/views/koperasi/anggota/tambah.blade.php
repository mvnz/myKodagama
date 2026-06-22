@extends('layouts.aplikasi')

@section('judul', 'Tambah Anggota')

@section('konten')
<div class="row justify-content-center">
  <div class="col-xl-10">
    <div class="form-card">
      <div class="d-flex align-items-center gap-3 mb-4 pb-4 border-bottom">
        <div class="d-flex align-items-center justify-content-center rounded-3 is-1e545767"
         >
          <i class="bi bi-person-plus-fill is-f1e54591"></i>
        </div>
        <div>
          <h5 class="mb-0 fw-700 is-5ca88772">Tambah Anggota Baru</h5>
          <small class="text-muted">Lengkapi semua informasi yang diperlukan</small>
        </div>
      </div>

      <form action="{{ route('anggota.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- Nomor anggota auto-generate -->
        <input type="hidden" name="nomor_anggota" value="{{ $nomorBaru }}">

        <div class="mb-3 p-3 rounded-3 is-26995383">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-hash text-primary"></i>
            <small class="text-muted">Nomor Anggota otomatis saat simpan (berdasarkan tanggal bergabung).</small>
          </div>
          <div class="small text-muted mt-1">Contoh format: KOP-YYYY-0001</div>
        </div>

        <h6 class="text-muted mb-3 text-uppercase is-0cea37d0">Data Pribadi</h6>
        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
            <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror"
              value="{{ old('nama') }}" placeholder="Nama sesuai KTP">
            @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-6">
            <label class="form-label">NIK <span class="text-danger">*</span></label>
            <input type="text" name="nik" class="form-control @error('nik') is-invalid @enderror"
              value="{{ old('nik') }}" maxlength="16" placeholder="16 digit NIK">
            @error('nik')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-6">
            <label class="form-label">Tempat Lahir <span class="text-danger">*</span></label>
            <input type="text" name="tempat_lahir" class="form-control @error('tempat_lahir') is-invalid @enderror"
              value="{{ old('tempat_lahir') }}" placeholder="Kota kelahiran">
            @error('tempat_lahir')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-3">
            <label class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
            <input type="date" name="tanggal_lahir" class="form-control @error('tanggal_lahir') is-invalid @enderror"
              value="{{ old('tanggal_lahir') }}">
            @error('tanggal_lahir')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-3">
            <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
            <select name="jenis_kelamin" class="form-select @error('jenis_kelamin') is-invalid @enderror">
              <option value="">Pilih...</option>
              <option value="Laki-laki" {{ old('jenis_kelamin') === 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
              <option value="Perempuan" {{ old('jenis_kelamin') === 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
            </select>
            @error('jenis_kelamin')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-12">
            <label class="form-label">Alamat Lengkap <span class="text-danger">*</span></label>
            <textarea name="alamat" class="form-control @error('alamat') is-invalid @enderror"
              rows="2" placeholder="Alamat sesuai KTP">{{ old('alamat') }}</textarea>
            @error('alamat')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
        </div>

        <h6 class="text-muted mb-3 text-uppercase is-0cea37d0">Kontak & Unit Kerja</h6>
        <div class="row g-3 mb-4">
          <div class="col-md-4">
            <label class="form-label">No. Telepon <span class="text-danger">*</span></label>
            <input type="text" name="telepon" class="form-control @error('telepon') is-invalid @enderror"
              value="{{ old('telepon') }}" placeholder="08xxxxxxxxxx">
            @error('telepon')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-4">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
              value="{{ old('email') }}" placeholder="opsional">
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-4">
            <label class="form-label">Unit Kerja</label>
            <select name="unit_kerja_id" class="form-select @error('unit_kerja_id') is-invalid @enderror">
              <option value="">— Pilih Unit Kerja —</option>
              @foreach($unitKerja as $uk)
                <option value="{{ $uk->id }}" {{ old('unit_kerja_id') == $uk->id ? 'selected' : '' }}>
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
                <option value="{{ $jenis }}" {{ old('jenis_anggota') === $jenis ? 'selected' : '' }}>{{ $jenis }}</option>
              @endforeach
            </select>
            <div class="form-text">Menentukan nominal simpanan wajib bulanan.</div>
            @error('jenis_anggota')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
            <label class="form-label">Tanggal Bergabung <span class="text-danger">*</span></label>
            <input type="date" name="tanggal_bergabung" class="form-control @error('tanggal_bergabung') is-invalid @enderror"
              value="{{ old('tanggal_bergabung', date('Y-m-d')) }}">
            @error('tanggal_bergabung')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-4">
            <label class="form-label">Status <span class="text-danger">*</span></label>
            <select name="status" class="form-select @error('status') is-invalid @enderror">
              <option value="aktif" {{ old('status', 'aktif') === 'aktif' ? 'selected' : '' }}>Aktif</option>
              <option value="tidak_aktif" {{ old('status') === 'tidak_aktif' ? 'selected' : '' }}>Tidak Aktif</option>
            </select>
            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-4">
            <label class="form-label">Foto</label>
            <input type="file" name="foto" class="form-control @error('foto') is-invalid @enderror"
              accept="image/*" id="fotoInput">
            <div class="form-text">Maks 2MB. Format: JPG, PNG, GIF</div>
            @error('foto')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-4 is-cb458930" id="fotoPreviewBox">
            <label class="form-label">Pratinjau</label>
            <img id="fotoPreview" src="" alt="Pratinjau" class="img-thumbnail is-4ade2f9e">
          </div>
        </div>

        <div class="d-flex gap-2 mt-4 pt-4 border-top">
          <button type="submit" class="btn btn-primary px-4">
            <i class="bi bi-check-lg me-2"></i> Simpan
          </button>
          <a href="{{ route('anggota.index') }}" class="btn btn-secondary">
            <i class="bi bi-x-lg me-2"></i> Batal
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