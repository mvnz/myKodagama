@extends('layouts.aplikasi')

@section('judul', 'Monitoring Angsuran')

@section('breadcrumb')
  <li class="breadcrumb-item">Monitoring</li>
  <li class="breadcrumb-item active">Angsuran</li>
@endsection

@section('konten')

@php
  $namaBulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
  $tahunSekarang = now()->year;
  $totalTagihan  = $angsuranBelum->sum('jumlah_angsuran');
  $totalDenda    = $angsuranBelum->filter(fn($a) => $a->tanggal_jatuh_tempo < now())->sum(fn($a) => $a->hitungDenda());
@endphp

{{-- Page Banner --}}
<div class="dashboard-welcome mb-4">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
      <div class="welcome-label mb-1">Monitoring Keuangan</div>
      <h4 class="mb-1 fw-700">Monitoring Angsuran Pinjaman</h4>
      <p class="mb-0 welcome-date">
        <i class="bi bi-calendar3 me-1"></i>
        {{ now()->translatedFormat('l, d F Y') }}
      </p>
    </div>
    <div class="welcome-icon-wrap">
      <i class="bi bi-alarm-fill"></i>
    </div>
  </div>
</div>

{{-- Stat Cards --}}
<div class="row g-4 mb-4">
  <div class="col-xl-3 col-sm-6">
    <div class="kstat-card kstat-red">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-people-fill"></i></div>
      <div class="kstat-value">{{ number_format($perAnggota->count()) }}</div>
      <div class="kstat-label">Anggota Menunggak</div>
      <div class="kstat-trend"><i class="bi bi-exclamation-circle-fill me-1"></i> {{ $namaBulan[$bulan] }} {{ $tahun }}</div>
    </div>
  </div>
  <div class="col-xl-3 col-sm-6">
    <div class="kstat-card kstat-amber">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-receipt"></i></div>
      <div class="kstat-value">{{ number_format($angsuranBelum->count()) }}</div>
      <div class="kstat-label">Tagihan Belum Bayar</div>
      <div class="kstat-trend"><i class="bi bi-file-earmark-text-fill me-1"></i> Total tagihan</div>
    </div>
  </div>
  <div class="col-xl-3 col-sm-6">
    <div class="kstat-card kstat-blue">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-cash-stack"></i></div>
      <div class="kstat-value" style="font-size:1.3rem">Rp {{ number_format($totalTagihan,0,',','.') }}</div>
      <div class="kstat-label">Total Pokok Belum Bayar</div>
      <div class="kstat-trend"><i class="bi bi-wallet2 me-1"></i> Akumulasi tunggakan</div>
    </div>
  </div>
  <div class="col-xl-3 col-sm-6">
    <div class="kstat-card kstat-green">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
      <div class="kstat-value" style="font-size:1.3rem">~Rp {{ number_format($totalDenda,0,',','.') }}</div>
      <div class="kstat-label">Estimasi Denda</div>
      <div class="kstat-trend"><i class="bi bi-calculator-fill me-1"></i> Perkiraan denda</div>
    </div>
  </div>
</div>

{{-- ─── FILTER ─── --}}
<form method="GET" action="{{ route('monitoring.pinjaman') }}" class="form-card mb-4">
  <div class="row g-3 align-items-end">
    <div class="col-sm-4 col-md-3">
      <label class="form-label fw-600">Bulan</label>
      <select name="bulan" class="form-select">
        @for($m = 1; $m <= 12; $m++)
          <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>{{ $namaBulan[$m] }}</option>
        @endfor
      </select>
    </div>
    <div class="col-sm-4 col-md-3">
      <label class="form-label fw-600">Tahun</label>
      <select name="tahun" class="form-select">
        @for($y = $tahunSekarang; $y >= $tahunSekarang - 5; $y--)
          <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
        @endfor
      </select>
    </div>
    <div class="col-sm-4 col-md-2">
      <button type="submit" class="btn btn-primary w-100">
        <i class="bi bi-search me-1"></i>Filter
      </button>
    </div>
  </div>
</form>

{{-- ─── TABEL ANGSURAN BELUM BAYAR ─── --}}
<div class="form-card p-0 overflow-hidden">
  <div class="d-flex align-items-center gap-2 p-3 border-bottom"
    style="background:linear-gradient(135deg,#fff7ed,#ffedd5)">
    <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0"
      style="width:34px;height:34px;background:rgba(245,158,11,.15);color:#d97706">
      <i class="bi bi-alarm-fill"></i>
    </div>
    <div>
      <h6 class="mb-0 fw-700" style="color:#92400e">Angsuran Belum Dibayar</h6>
      <small class="text-muted">{{ $namaBulan[$bulan] }} {{ $tahun }} · {{ $angsuranBelum->count() }} tagihan</small>
    </div>
  </div>

  @if($angsuranBelum->isEmpty())
    <div class="text-center py-5 text-muted">
      <i class="bi bi-check2-circle d-block mb-2" style="font-size:2rem;color:#10b981;opacity:.6"></i>
      <div>Tidak ada angsuran belum bayar pada bulan ini!</div>
    </div>
  @else
    <div class="table-responsive">
      <table class="koperasi-table">
        <thead>
          <tr>
            <th>No</th>
            <th>Anggota</th>
            <th>No. Pinjaman</th>
            <th>Angsuran Ke</th>
            <th>Jatuh Tempo</th>
            <th class="text-end">Jumlah</th>
            <th class="text-end">Denda Est.</th>
            <th>Status</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          @foreach($angsuranBelum as $i => $ang)
            @php
              $lewat = $ang->tanggal_jatuh_tempo < now();
              $denda = $lewat ? $ang->hitungDenda() : 0;
              $anggota = $ang->pinjaman->anggota ?? null;
            @endphp
          <tr style="{{ $lewat ? 'background:#fff8f8' : '' }}">
            <td class="text-muted" style="font-size:.82rem">{{ $i + 1 }}</td>
            <td>
              @if($anggota)
                <div class="fw-600">{{ $anggota->nama }}</div>
                <div style="font-size:.73rem;color:#9ca3af">
                  {{ $anggota->nomor_anggota }}
                  @if($anggota->unitKerja) · {{ $anggota->unitKerja->nama }} @endif
                </div>
              @else
                <span class="text-muted">—</span>
              @endif
            </td>
            <td>
              <code style="background:#eef2ff;color:#3b5bdb;border:1px solid #e0e7ff;border-radius:.3rem;padding:.1rem .4rem;font-size:.78rem">
                {{ $ang->pinjaman->nomor_pinjaman ?? '—' }}
              </code>
            </td>
            <td class="text-center fw-600">{{ $ang->ke }}</td>
            <td>
              <span style="font-size:.85rem;{{ $lewat ? 'color:#dc2626;font-weight:600' : '' }}">
                {{ $ang->tanggal_jatuh_tempo->format('d M Y') }}
              </span>
              @if($lewat)
                <div style="font-size:.72rem;color:#dc2626">
                  <i class="bi bi-exclamation-circle me-1"></i>{{ $ang->tanggal_jatuh_tempo->diffForHumans() }}
                </div>
              @endif
            </td>
            <td class="text-end fw-700">Rp {{ number_format($ang->jumlah_angsuran,0,',','.') }}</td>
            <td class="text-end" style="color:{{ $denda > 0 ? '#dc2626' : '#9ca3af' }};font-size:.83rem">
              {{ $denda > 0 ? '~Rp '.number_format($denda,0,',','.') : '—' }}
            </td>
            <td>
              @if($lewat)
                <span class="badge-status badge-ditolak">Terlambat</span>
              @else
                <span class="badge-status badge-diajukan">Belum Bayar</span>
              @endif
            </td>
            <td>
              @if($ang->pinjaman)
                <a href="{{ route('pinjaman.show', $ang->pinjaman) }}" class="btn btn-xs btn-outline-info" title="Detail Pinjaman">
                  <i class="bi bi-eye"></i>
                </a>
                @if($anggota)
                  <a href="{{ route('pinjaman.show', $ang->pinjaman) }}" class="btn btn-xs btn-success" title="Bayar Angsuran">
                    <i class="bi bi-cash-coin"></i>
                  </a>
                @endif
              @endif
            </td>
          </tr>
          @endforeach
        </tbody>
        <tfoot>
          <tr style="background:#f8fafc;font-weight:700">
            <td colspan="5" class="text-end" style="color:#374151">Total</td>
            <td class="text-end" style="color:#2563eb">Rp {{ number_format($totalTagihan,0,',','.') }}</td>
            <td class="text-end" style="color:#dc2626">~Rp {{ number_format($totalDenda,0,',','.') }}</td>
            <td colspan="2"></td>
          </tr>
        </tfoot>
      </table>
    </div>
  @endif
</div>

@endsection
