@extends('layouts.aplikasi')

@section('judul', 'Tambah Simpanan')

@section('konten')
<div class="row justify-content-center">
  <div class="col-xl-8">
    <div class="form-card">
      <div class="d-flex align-items-center gap-3 mb-4 pb-4 border-bottom">
        <div class="d-flex align-items-center justify-content-center rounded-3 is-98f48ffa"
         >
          <i class="bi bi-piggy-bank-fill is-f25f18e3"></i>
        </div>
        <div>
          <h5 class="mb-0 fw-700 is-5ca88772">Tambah Simpanan</h5>
          <small class="text-muted">Catat transaksi simpanan anggota</small>
        </div>
      </div>

      <form action="{{ route('simpanan.store') }}" method="POST">
        @csrf

        <div class="row g-3">
          <div class="col-12">
            <label class="form-label">Anggota <span class="text-danger">*</span></label>
            <select name="anggota_id" class="form-select @error('anggota_id') is-invalid @enderror">
              <option value="">-- Pilih Anggota --</option>
              @foreach($anggota as $a)
                <option value="{{ $a->id }}"
                  {{ old('anggota_id', request('anggota_id')) == $a->id ? 'selected' : '' }}>
                  {{ $a->nomor_anggota }} — {{ $a->nama }}
                </option>
              @endforeach
            </select>
            @error('anggota_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-md-6">
            <label class="form-label">Jenis Simpanan <span class="text-danger">*</span></label>
            <select name="jenis_simpanan" class="form-select @error('jenis_simpanan') is-invalid @enderror">
              <option value="">Pilih jenis...</option>
              <option value="pokok" {{ old('jenis_simpanan') === 'pokok' ? 'selected' : '' }}>Simpanan Pokok</option>
              <option value="wajib" {{ old('jenis_simpanan') === 'wajib' ? 'selected' : '' }}>Simpanan Wajib</option>
              <option value="sukarela" {{ old('jenis_simpanan') === 'sukarela' ? 'selected' : '' }}>Simpanan Sukarela</option>
            </select>
            @error('jenis_simpanan')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-md-6">
            <label class="form-label">Tanggal <span class="text-danger">*</span></label>
            <input type="date" name="tanggal" class="form-control @error('tanggal') is-invalid @enderror"
              value="{{ old('tanggal', date('Y-m-d')) }}">
            @error('tanggal')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-md-6">
            <label class="form-label">Jumlah (Rp) <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text">Rp</span>
              <input type="number" name="jumlah" class="form-control @error('jumlah') is-invalid @enderror"
                value="{{ old('jumlah') }}" min="0" step="1000" placeholder="0">
              @error('jumlah')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>

          <div class="col-md-6">
            <label class="form-label">Keterangan</label>
            <input type="text" name="keterangan" class="form-control @error('keterangan') is-invalid @enderror"
              value="{{ old('keterangan') }}" placeholder="Keterangan tambahan (opsional)">
            @error('keterangan')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
        </div>

        <div class="d-flex gap-2 mt-4 pt-4 border-top">
          <button type="submit" class="btn btn-primary px-4">
            <i class="bi bi-check-lg me-2"></i> Simpan
          </button>
          <a href="{{ route('simpanan.index') }}" class="btn btn-secondary">
            <i class="bi bi-x-lg me-2"></i> Batal
          </a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
