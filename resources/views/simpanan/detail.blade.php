@extends('layouts.aplikasi')

@section('judul', 'Detail Simpanan')

@section('konten')
<div class="row justify-content-center">
  <div class="col-xl-8">
    <div class="form-card">
      <div class="d-flex align-items-center justify-content-between mb-4 pb-4 border-bottom">
        <div class="d-flex align-items-center gap-3">
          @php $jenisWarna = ['pokok'=>'#175cdd','wajib'=>'#10b981','sukarela'=>'#f59e0b']; @endphp
          <div class="d-flex align-items-center justify-content-center rounded-3"
            style="width:50px;height:50px;background:rgba(16,185,129,.1)">
            <i class="bi bi-piggy-bank-fill" style="color:#10b981;font-size:1.4rem"></i>
          </div>
          <div>
            <h5 class="mb-0 fw-700" style="color:#112344">Detail Simpanan</h5>
            <small class="text-muted">ID #{{ $simpanan->id }}</small>
          </div>
        </div>
        <div class="d-flex gap-2">
          @can('simpanan.edit')
          <a href="{{ route('simpanan.edit', $simpanan) }}" class="btn btn-warning">
            <i class="bi bi-pencil me-1"></i> Ubah
          </a>
          @endcan
          @can('simpanan.hapus')
          <form action="{{ route('simpanan.destroy', $simpanan) }}" method="POST"
            onsubmit="return confirm('Hapus data simpanan ini?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-outline-danger">
              <i class="bi bi-trash me-1"></i> Hapus
            </button>
          </form>
          @endcan
        </div>
      </div>

      <div class="row g-4">
        <div class="col-md-6">
          <div style="font-size:.75rem;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;margin-bottom:.4rem">Anggota</div>
          <div style="font-weight:700;font-size:1.1rem;color:#112344">{{ $simpanan->anggota->nama ?? '—' }}</div>
          <code style="color:#175cdd">{{ $simpanan->anggota->nomor_anggota ?? '' }}</code>
        </div>
        <div class="col-md-6">
          <div style="font-size:.75rem;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;margin-bottom:.4rem">Jumlah</div>
          <div style="font-weight:700;font-size:1.5rem;color:#10b981">
            Rp {{ number_format($simpanan->jumlah, 0, ',', '.') }}
          </div>
        </div>
        <div class="col-md-4">
          <div style="font-size:.75rem;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;margin-bottom:.4rem">Jenis</div>
          @php $jenisMap = ['pokok'=>'badge-aktif','wajib'=>'badge-disetujui','sukarela'=>'badge-sudah']; @endphp
          <span class="badge-status {{ $jenisMap[$simpanan->jenis_simpanan] ?? '' }}">
            {{ ucfirst($simpanan->jenis_simpanan) }}
          </span>
        </div>
        <div class="col-md-4">
          <div style="font-size:.75rem;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;margin-bottom:.4rem">Tanggal</div>
          <div style="font-weight:500;color:#112344">{{ $simpanan->tanggal->format('d F Y') }}</div>
        </div>
        <div class="col-md-4">
          <div style="font-size:.75rem;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;margin-bottom:.4rem">Dicatat Oleh</div>
          <div style="font-weight:500;color:#112344">{{ $simpanan->petugas->name ?? '—' }}</div>
        </div>
        <div class="col-12">
          <div style="font-size:.75rem;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;margin-bottom:.4rem">Keterangan</div>
          <div style="color:#374151">{{ $simpanan->keterangan ?? 'Tidak ada keterangan' }}</div>
        </div>
      </div>

      <div class="mt-4 pt-4 border-top">
        <a href="{{ route('simpanan.index') }}" class="btn btn-outline-secondary">
          <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
        </a>
        @can('anggota.lihat')
        <a href="{{ route('anggota.show', $simpanan->anggota_id) }}" class="btn btn-outline-primary ms-2">
          <i class="bi bi-person me-1"></i> Profil Anggota
        </a>
        @endcan
      </div>
    </div>
  </div>
</div>
@endsection
