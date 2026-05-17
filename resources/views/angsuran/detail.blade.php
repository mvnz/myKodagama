@extends('layouts.aplikasi')

@section('judul', 'Detail Angsuran')

@section('konten')
<div class="row justify-content-center">
  <div class="col-xl-8">
    <div class="form-card">
      <div class="d-flex align-items-center justify-content-between mb-4 pb-4 border-bottom">
        <div class="d-flex align-items-center gap-3">
          <div class="d-flex align-items-center justify-content-center rounded-3"
            style="width:50px;height:50px;background:rgba(23,92,221,.1)">
            <i class="bi bi-receipt" style="color:#175cdd;font-size:1.4rem"></i>
          </div>
          <div>
            <h5 class="mb-0 fw-700" style="color:#112344">Detail Angsuran</h5>
            <small class="text-muted">
              <code>{{ $angsuran->pinjaman->nomor_pinjaman ?? '' }}</code>
              — Ke-{{ $angsuran->ke }}
            </small>
          </div>
        </div>
        @if($angsuran->status === 'belum')
          @can('angsuran.bayar')
          <form action="{{ route('angsuran.bayar', $angsuran) }}" method="POST" id="formBayar"
            onsubmit="return confirm('Konfirmasi pembayaran angsuran ini?')">
            @csrf
            <div class="d-flex align-items-center gap-2">
              <input type="date" name="tanggal_bayar" id="tanggalBayar" class="form-control form-control-sm"
                value="{{ today()->toDateString() }}" style="width:160px">
              <button type="submit" class="btn btn-success">
                <i class="bi bi-check2-circle me-2"></i> Tandai Lunas
              </button>
            </div>
          </form>
          @endcan
        @endif
      </div>

      <div class="row g-4">
        <div class="col-md-6">
          <div style="font-size:.75rem;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;margin-bottom:.35rem">Anggota</div>
          <div style="font-weight:700;font-size:1.1rem;color:#112344">
            {{ $angsuran->pinjaman->anggota->nama ?? '—' }}
          </div>
          <code class="text-primary">{{ $angsuran->pinjaman->anggota->nomor_anggota ?? '' }}</code>
        </div>
        <div class="col-md-6">
          <div style="font-size:.75rem;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;margin-bottom:.35rem">Status</div>
          <span class="badge-status {{ $angsuran->status === 'sudah' ? 'badge-sudah' : 'badge-belum' }}">
            {{ $angsuran->status === 'sudah' ? 'Lunas' : 'Belum Lunas' }}
          </span>
          @if($angsuran->status === 'belum' && $angsuran->tanggal_jatuh_tempo < now())
            <span class="badge-status badge-ditolak ms-1">Terlambat</span>
          @endif
        </div>

        <div class="col-md-3">
          <div style="font-size:.75rem;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;margin-bottom:.35rem">Angsuran Ke</div>
          <div style="font-size:2rem;font-weight:700;color:#175cdd">{{ $angsuran->ke }}</div>
        </div>
        <div class="col-md-3">
          <div style="font-size:.75rem;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;margin-bottom:.35rem">Pokok</div>
          <div style="font-weight:600;color:#112344">Rp {{ number_format($angsuran->pokok, 0, ',', '.') }}</div>
        </div>
        <div class="col-md-3">
          <div style="font-size:.75rem;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;margin-bottom:.35rem">Bunga</div>
          <div style="font-weight:600;color:#f59e0b">Rp {{ number_format($angsuran->bunga, 0, ',', '.') }}</div>
        </div>
        <div class="col-md-3">
          <div style="font-size:.75rem;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;margin-bottom:.35rem">Total Angsuran</div>
          <div style="font-weight:700;font-size:1.2rem;color:#175cdd">Rp {{ number_format($angsuran->jumlah_angsuran, 0, ',', '.') }}</div>
        </div>

        @if($angsuran->status === 'sudah' && $angsuran->denda > 0)
        <div class="col-md-3">
          <div style="font-size:.75rem;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;margin-bottom:.35rem">Denda Keterlambatan</div>
          <div style="font-weight:600;color:#ef4444">
            Rp {{ number_format($angsuran->denda, 0, ',', '.') }}
            <small class="text-muted fw-normal">({{ $angsuran->hari_terlambat }} hari)</small>
          </div>
        </div>
        <div class="col-md-3">
          <div style="font-size:.75rem;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;margin-bottom:.35rem">Total Dibayar</div>
          <div style="font-weight:700;font-size:1.2rem;color:#112344">Rp {{ number_format($angsuran->total_tagihan, 0, ',', '.') }}</div>
        </div>
        @endif

        @if($angsuran->status === 'belum' && $angsuran->tanggal_jatuh_tempo < now())
        <div class="col-12">
          <div class="alert alert-warning d-flex align-items-start gap-2 mb-0 py-2">
            <i class="bi bi-clock-history mt-1"></i>
            <div>
              <strong>Estimasi Denda Keterlambatan</strong>
              <div class="mt-1" id="dendaEstimasi">
                Terlambat {{ $angsuran->tanggal_jatuh_tempo->diffInDays(now()) }} hari —
                Denda ≈ <strong>Rp {{ number_format($angsuran->hitungDenda(), 0, ',', '.') }}</strong>
                <small class="text-muted">(berdasarkan bunga keterlambatan {{ \App\Models\Pengaturan::get('sp_bunga_keterlambatan', 2) }}% / bulan)</small>
              </div>
            </div>
          </div>
        </div>
        @endif

        <div class="col-md-6">
          <div style="font-size:.75rem;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;margin-bottom:.35rem">Jatuh Tempo</div>
          <div style="font-weight:600;color:#112344">{{ $angsuran->tanggal_jatuh_tempo->format('d F Y') }}</div>
        </div>
        <div class="col-md-6">
          <div style="font-size:.75rem;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;margin-bottom:.35rem">Tanggal Bayar</div>
          <div style="font-weight:600;color:{{ $angsuran->tanggal_bayar ? '#10b981' : '#9ca3af' }}">
            {{ $angsuran->tanggal_bayar ? $angsuran->tanggal_bayar->format('d F Y') : 'Belum dibayar' }}
          </div>
        </div>
      </div>

      <div class="mt-4 pt-4 border-top d-flex gap-2">
        <a href="{{ route('angsuran.index') }}" class="btn btn-outline-secondary">
          <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
        <a href="{{ route('pinjaman.show', $angsuran->pinjaman_id) }}" class="btn btn-outline-primary">
          <i class="bi bi-cash-coin me-1"></i> Lihat Pinjaman
        </a>
      </div>
    </div>
  </div>
</div>
@endsection
