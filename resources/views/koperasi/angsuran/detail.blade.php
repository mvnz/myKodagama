@extends('layouts.aplikasi')

@section('judul', 'Detail Angsuran')

@section('konten')
<div class="row justify-content-center">
  <div class="col-xl-8">
    <div class="form-card">
      <div class="d-flex align-items-center justify-content-between mb-4 pb-4 border-bottom">
        <div class="d-flex align-items-center gap-3">
          <div class="d-flex align-items-center justify-content-center rounded-3 is-1e545767"
           >
            <i class="bi bi-receipt is-f1e54591"></i>
          </div>
          <div>
            <h5 class="mb-0 fw-700 is-5ca88772">Detail Angsuran</h5>
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
          <div class="is-175517f9">Anggota</div>
          <div class="is-f9935aba">
            {{ $angsuran->pinjaman->anggota->nama ?? '—' }}
          </div>
          <code class="text-primary">{{ $angsuran->pinjaman->anggota->nomor_anggota ?? '' }}</code>
        </div>
        <div class="col-md-6">
          <div class="is-175517f9">Status</div>
          <span class="badge-status {{ $angsuran->status === 'sudah' ? 'badge-sudah' : 'badge-belum' }}">
            {{ $angsuran->status === 'sudah' ? 'Lunas' : 'Belum Lunas' }}
          </span>
          @if($angsuran->status === 'belum' && $angsuran->tanggal_jatuh_tempo < now())
            <span class="badge-status badge-ditolak ms-1">Terlambat</span>
          @endif
        </div>

        <div class="col-md-3">
          <div class="is-175517f9">Angsuran Ke</div>
          <div class="is-0ba5188d">{{ $angsuran->ke }}</div>
        </div>
        <div class="col-md-3">
          <div class="is-175517f9">Pokok</div>
          <div class="is-6467c5ff">Rp {{ number_format($angsuran->pokok, 0, ',', '.') }}</div>
        </div>
        <div class="col-md-3">
          <div class="is-175517f9">Bunga</div>
          <div class="is-7716f061">Rp {{ number_format($angsuran->bunga, 0, ',', '.') }}</div>
        </div>
        <div class="col-md-3">
          <div class="is-175517f9">Total Angsuran</div>
          <div class="is-9ea3c5b2">Rp {{ number_format($angsuran->jumlah_angsuran, 0, ',', '.') }}</div>
        </div>

        @if($angsuran->status === 'sudah' && $angsuran->denda > 0)
        <div class="col-md-3">
          <div class="is-175517f9">Denda Keterlambatan</div>
          <div class="is-eced619d">
            Rp {{ number_format($angsuran->denda, 0, ',', '.') }}
            <small class="text-muted fw-normal">({{ $angsuran->hari_terlambat }} hari)</small>
          </div>
        </div>
        <div class="col-md-3">
          <div class="is-175517f9">Total Dibayar</div>
          <div class="is-ee920534">Rp {{ number_format($angsuran->total_tagihan, 0, ',', '.') }}</div>
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
                <small class="text-muted">(berdasarkan bunga keterlambatan {{ \App\Models\Koperasi\Pengaturan::get('sp_bunga_keterlambatan', 2) }}% / bulan)</small>
              </div>
            </div>
          </div>
        </div>
        @endif

        <div class="col-md-6">
          <div class="is-175517f9">Jatuh Tempo</div>
          <div class="is-6467c5ff">{{ $angsuran->tanggal_jatuh_tempo->format('d F Y') }}</div>
        </div>
        <div class="col-md-6">
          <div class="is-175517f9">Tanggal Bayar</div>
          <div class="is-f883f980">
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
