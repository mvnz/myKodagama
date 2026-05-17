@extends('layouts.aplikasi')

@section('judul', 'Dashboard Anggota')

@section('konten')

{{-- Welcome Banner --}}
<div class="dashboard-welcome mb-4">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
      <div class="welcome-label mb-1">Selamat datang,</div>
      <h4 class="mb-1 fw-700">{{ $anggota->nama }}</h4>
      <p class="mb-0 welcome-date">
        <code style="background:rgba(255,255,255,.2);color:inherit;border-radius:.3rem;padding:.1rem .5rem;font-size:.85rem">{{ $anggota->nomor_anggota }}</code>
        &nbsp;·&nbsp;
        <i class="bi bi-calendar3 me-1"></i>{{ now()->translatedFormat('l, d F Y') }}
      </p>
    </div>
    <div class="welcome-icon-wrap">
      <i class="bi bi-person-circle"></i>
    </div>
  </div>
</div>

{{-- Stat Cards --}}
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="kstat-card" style="border-left:4px solid #10b981">
      <div class="kstat-icon" style="background:rgba(16,185,129,.1);color:#059669"><i class="bi bi-piggy-bank-fill"></i></div>
      <div class="kstat-body">
        <div class="kstat-val" style="color:#059669;font-size:.95rem">Rp {{ number_format($totalSimpanan, 0, ',', '.') }}</div>
        <div class="kstat-lbl">Total Simpanan</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="kstat-card" style="border-left:4px solid #3b82f6">
      <div class="kstat-icon" style="background:rgba(59,130,246,.1);color:#2563eb"><i class="bi bi-bank"></i></div>
      <div class="kstat-body">
        <div class="kstat-val" style="color:#2563eb;font-size:.95rem">Rp {{ number_format($totalWajib, 0, ',', '.') }}</div>
        <div class="kstat-lbl">Simpanan Wajib</div>
        @if($wajibPerJenis)
          <div style="font-size:.7rem;color:#9ca3af">Rp {{ number_format($wajibPerJenis['nominal'],0,',','.') }}/bln</div>
        @endif
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="kstat-card" style="border-left:4px solid #f59e0b">
      <div class="kstat-icon" style="background:rgba(245,158,11,.1);color:#d97706"><i class="bi bi-cash-coin"></i></div>
      <div class="kstat-body">
        <div class="kstat-val" style="color:#d97706;font-size:.95rem">{{ $semuaPinjaman->count() }}</div>
        <div class="kstat-lbl">Total Pinjaman</div>
        <div style="font-size:.7rem;color:#9ca3af">{{ $pinjamanAktif->count() }} aktif</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="kstat-card" style="border-left:4px solid {{ $totalSisaHutang > 0 ? '#ef4444' : '#10b981' }}">
      <div class="kstat-icon" style="background:{{ $totalSisaHutang > 0 ? 'rgba(239,68,68,.1)' : 'rgba(16,185,129,.1)' }};color:{{ $totalSisaHutang > 0 ? '#dc2626' : '#059669' }}">
        <i class="bi bi-{{ $totalSisaHutang > 0 ? 'exclamation-circle' : 'check-circle' }}-fill"></i>
      </div>
      <div class="kstat-body">
        <div class="kstat-val" style="color:{{ $totalSisaHutang > 0 ? '#dc2626' : '#059669' }};font-size:.95rem">Rp {{ number_format(max(0,$totalSisaHutang), 0, ',', '.') }}</div>
        <div class="kstat-lbl">Sisa Hutang</div>
        <div style="font-size:.7rem;color:#9ca3af">{{ $totalSisaHutang > 0 ? 'Belum lunas' : 'Bersih' }}</div>
      </div>
    </div>
  </div>
</div>

<div class="row g-4">

  {{-- SIMPANAN RINGKASAN --}}
  <div class="col-lg-4">
    <div class="form-card h-100">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h6 class="mb-0 fw-700" style="color:#112344">
          <i class="bi bi-piggy-bank-fill me-2" style="color:#10b981"></i>Simpanan Saya
        </h6>
        <a href="{{ route('anggota.rekap', $anggota) }}" class="btn btn-sm btn-outline-success" style="font-size:.75rem">Detail</a>
      </div>
      @php
        $simpananTypes = [
          'pokok'    => ['label'=>'Pokok',    'color'=>'#8b5cf6','bg'=>'#faf5ff','border'=>'#e9d5ff','val'=>$totalPokok],
          'wajib'    => ['label'=>'Wajib',    'color'=>'#2563eb','bg'=>'#eff6ff','border'=>'#bfdbfe','val'=>$totalWajib],
          'sukarela' => ['label'=>'Sukarela', 'color'=>'#059669','bg'=>'#f0fdf4','border'=>'#bbf7d0','val'=>$totalSukarela],
        ];
      @endphp
      <div class="d-flex flex-column gap-2">
        @foreach($simpananTypes as $jenis => $cfg)
          @php $pct = $totalSimpanan > 0 ? round($cfg['val'] / $totalSimpanan * 100) : 0; @endphp
          <div style="background:{{ $cfg['bg'] }};border:1px solid {{ $cfg['border'] }};border-radius:.6rem;padding:.6rem .8rem">
            <div class="d-flex justify-content-between mb-1">
              <span style="font-size:.8rem;font-weight:600;color:{{ $cfg['color'] }}">{{ $cfg['label'] }}</span>
              <span style="font-size:.8rem;font-weight:700;color:{{ $cfg['color'] }}">Rp {{ number_format($cfg['val'],0,',','.') }}</span>
            </div>
            <div style="background:#e5e7eb;border-radius:50px;height:4px">
              <div style="background:{{ $cfg['color'] }};width:{{ $pct }}%;height:4px;border-radius:50px"></div>
            </div>
          </div>
        @endforeach
      </div>
      <hr class="my-3">
      <div class="d-flex justify-content-between">
        <span style="font-size:.85rem;font-weight:600">Total</span>
        <span style="font-weight:700;color:#059669">Rp {{ number_format($totalSimpanan,0,',','.') }}</span>
      </div>
    </div>
  </div>

  {{-- PINJAMAN AKTIF --}}
  <div class="col-lg-5">
    <div class="form-card h-100 p-0 overflow-hidden">
      <div class="p-3 border-bottom d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-700" style="color:#112344">
          <i class="bi bi-cash-coin me-2" style="color:#f59e0b"></i>Pinjaman Saya
        </h6>
        <a href="{{ route('anggota.rekap', $anggota) }}" class="btn btn-sm btn-outline-warning" style="font-size:.75rem">Detail</a>
      </div>
      @forelse($semuaPinjaman->sortByDesc('tanggal_pengajuan')->take(4) as $p)
        @php
          $terbayar   = $p->angsuran->where('status','sudah')->sum('jumlah_angsuran');
          $totalCicil = $p->angsuran->sum('jumlah_angsuran');
          $pct        = $totalCicil > 0 ? min(100, round($terbayar / $totalCicil * 100)) : 0;
          $badgeMap   = ['diajukan'=>'badge-diajukan','diverifikasi'=>'badge-diverifikasi','disetujui'=>'badge-disetujui','ditolak'=>'badge-ditolak','lunas'=>'badge-lunas'];
        @endphp
        <div style="padding:.75rem 1rem;border-bottom:1px solid #f1f5f9">
          <div class="d-flex justify-content-between align-items-start mb-1">
            <div>
              <code style="background:#eef2ff;color:#3b5bdb;border:1px solid #e0e7ff;border-radius:.3rem;padding:.1rem .4rem;font-size:.78rem">{{ $p->nomor_pinjaman }}</code>
              <span class="badge-status {{ $badgeMap[$p->status] ?? '' }} ms-1">{{ ucfirst($p->status) }}</span>
            </div>
            <span style="font-size:.8rem;font-weight:700">Rp {{ number_format($p->jumlah_pinjaman,0,',','.') }}</span>
          </div>
          @if($p->angsuran->count())
            <div style="background:#e5e7eb;border-radius:50px;height:5px;margin-top:.4rem">
              <div style="background:{{ $pct >= 100 ? '#059669' : '#2563eb' }};width:{{ $pct }}%;height:5px;border-radius:50px"></div>
            </div>
            <div style="font-size:.7rem;color:#9ca3af;margin-top:.2rem">
              {{ $p->angsuran->where('status','sudah')->count() }}/{{ $p->angsuran->count() }} angsuran · {{ $pct }}%
            </div>
          @endif
        </div>
      @empty
        <div class="text-center py-5 text-muted">
          <i class="bi bi-inbox d-block mb-2" style="font-size:1.6rem;opacity:.3"></i>
          <div style="font-size:.83rem">Belum ada pinjaman</div>
        </div>
      @endforelse
    </div>
  </div>

  {{-- ANGSURAN MENDATANG --}}
  <div class="col-lg-3">
    <div class="form-card h-100 p-0 overflow-hidden">
      <div class="p-3 border-bottom">
        <h6 class="mb-0 fw-700" style="color:#112344">
          <i class="bi bi-alarm-fill me-2" style="color:#f59e0b"></i>Angsuran Mendatang
        </h6>
      </div>
      @if($angsuranMendatang->count())
        @foreach($angsuranMendatang as $ang)
          @php $lewat = $ang->tanggal_jatuh_tempo < now(); @endphp
          <div style="padding:.65rem 1rem;border-bottom:1px solid #f1f5f9;{{ $lewat ? 'background:#fff8f8' : '' }}">
            <div class="d-flex align-items-center gap-2">
              <div class="flex-shrink-0 d-flex align-items-center justify-content-center rounded-circle"
                style="width:30px;height:30px;background:{{ $lewat ? 'rgba(239,68,68,.1)' : 'rgba(245,158,11,.1)' }};color:{{ $lewat ? '#dc2626' : '#d97706' }}">
                <i class="bi bi-{{ $lewat ? 'exclamation-triangle' : 'clock' }}" style="font-size:.8rem"></i>
              </div>
              <div class="flex-grow-1">
                <div style="font-size:.8rem;font-weight:600">Ke-{{ $ang->ke }}</div>
                <div style="font-size:.7rem;color:{{ $lewat ? '#dc2626' : '#9ca3af' }}">
                  {{ $ang->tanggal_jatuh_tempo->format('d M Y') }}
                  {{ $lewat ? '· Terlambat' : '' }}
                </div>
              </div>
              <div style="font-size:.8rem;font-weight:700">Rp {{ number_format($ang->jumlah_angsuran,0,',','.') }}</div>
            </div>
          </div>
        @endforeach
      @else
        <div class="text-center py-5 text-muted">
          <i class="bi bi-check2-all d-block mb-2" style="font-size:1.6rem;opacity:.3"></i>
          <div style="font-size:.82rem">Tidak ada angsuran<br>mendatang</div>
        </div>
      @endif
    </div>
  </div>

</div>

@endsection
