@extends('layouts.aplikasi')

@section('judul', 'Ubah Pinjaman')

@section('konten')
<div class="row justify-content-center">
  <div class="col-xl-10">
    <div class="form-card">
      <div class="d-flex align-items-center gap-3 mb-4 pb-4 border-bottom">
        <div class="d-flex align-items-center justify-content-center rounded-3"
          style="width:50px;height:50px;background:rgba(245,158,11,.1)">
          <i class="bi bi-pencil-square" style="color:#f59e0b;font-size:1.4rem"></i>
        </div>
        <div>
          <h5 class="mb-0 fw-700" style="color:#112344">Ubah Data Pinjaman</h5>
          <small class="text-muted"><code>{{ $pinjaman->nomor_pinjaman }}</code> — {{ $pinjaman->anggota->nama ?? '' }}</small>
        </div>
      </div>

      <form action="{{ route('pinjaman.update', $pinjaman) }}" method="POST">
        @csrf @method('PUT')

        <div class="row g-3">
          <div class="col-12">
            <label class="form-label">Anggota</label>
            <input type="text" class="form-control" value="{{ $pinjaman->anggota->nama ?? '' }}" readonly>
          </div>

          <div class="col-md-4">
            <label class="form-label">Jumlah Pinjaman (Rp)</label>
            <div class="input-group">
              <span class="input-group-text">Rp</span>
              <input type="number" name="jumlah_pinjaman"
                class="form-control @error('jumlah_pinjaman') is-invalid @enderror"
                value="{{ old('jumlah_pinjaman', $pinjaman->jumlah_pinjaman) }}" min="0" step="100000">
              @error('jumlah_pinjaman')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>

          <div class="col-md-4">
            <label class="form-label">Jangka Waktu (Bulan)</label>
            <select name="jangka_waktu" class="form-select @error('jangka_waktu') is-invalid @enderror">
              @foreach([3,6,12,18,24,36] as $j)
                <option value="{{ $j }}" {{ old('jangka_waktu', $pinjaman->jangka_waktu) == $j ? 'selected' : '' }}>{{ $j }} Bulan</option>
              @endforeach
            </select>
            @error('jangka_waktu')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-md-4">
            <label class="form-label">Bunga (% / Bulan)</label>
            <div class="input-group">
              <input type="number" name="bunga"
                class="form-control @error('bunga') is-invalid @enderror"
                value="{{ old('bunga', $pinjaman->bunga) }}" min="0" max="100" step="0.1">
              <span class="input-group-text">%</span>
              @error('bunga')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>

          <!-- Status -->
          <div class="col-md-6">
            <label class="form-label">Status <span class="text-danger">*</span></label>
            <select name="status" class="form-select @error('status') is-invalid @enderror">
              @foreach(['diajukan','diverifikasi','disetujui','ditolak','lunas'] as $st)
                <option value="{{ $st }}" {{ old('status', $pinjaman->status) === $st ? 'selected' : '' }}>
                  {{ ucfirst($st) }}
                </option>
              @endforeach
            </select>
            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-md-6">
            <label class="form-label">Tanggal Verifikasi</label>
            <input type="date" name="tanggal_verifikasi"
              class="form-control @error('tanggal_verifikasi') is-invalid @enderror"
              value="{{ old('tanggal_verifikasi', $pinjaman->tanggal_verifikasi?->format('Y-m-d')) }}">
            @error('tanggal_verifikasi')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-md-6">
            <label class="form-label">Tanggal Persetujuan</label>
            <input type="date" name="tanggal_persetujuan"
              class="form-control @error('tanggal_persetujuan') is-invalid @enderror"
              value="{{ old('tanggal_persetujuan', $pinjaman->tanggal_persetujuan?->format('Y-m-d')) }}">
            <div class="form-text">Isi jika status disetujui. Jadwal angsuran akan dibuat otomatis.</div>
            @error('tanggal_persetujuan')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-md-4">
            <label class="form-label">Tanggal Pengajuan</label>
            <input type="date" name="tanggal_pengajuan"
              class="form-control @error('tanggal_pengajuan') is-invalid @enderror"
              value="{{ old('tanggal_pengajuan', $pinjaman->tanggal_pengajuan->format('Y-m-d')) }}">
            @error('tanggal_pengajuan')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-12">
            <label class="form-label">Tujuan Pinjaman</label>
            <textarea name="tujuan" class="form-control @error('tujuan') is-invalid @enderror"
              rows="3">{{ old('tujuan', $pinjaman->tujuan) }}</textarea>
            @error('tujuan')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
        </div>

        @if($pinjaman->status !== 'diajukan')
          <div class="alert alert-warning mt-3">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            Mengubah pinjaman yang sudah diproses dapat memengaruhi jadwal angsuran yang ada.
          </div>
        @endif

        <div class="d-flex gap-2 mt-4">
          <button type="submit" class="btn-simpan">
            <i class="bi bi-check-lg me-2"></i> Simpan Perubahan
          </button>
          <a href="{{ route('pinjaman.show', $pinjaman) }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-2"></i> Kembali
          </a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
