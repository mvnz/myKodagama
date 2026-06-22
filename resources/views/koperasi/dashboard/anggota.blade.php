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
        <code class="is-0b8979e0">{{ $anggota->nomor_anggota }}</code>
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
<div class="row g-4 mb-4">
  <div class="col-xl col-sm-6">
    <div class="kstat-card kstat-blue">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-bank2"></i></div>
      <div class="kstat-value kstat-value-sm">Rp {{ number_format($totalSimpananKoperasi, 0, ',', '.') }}</div>
      <div class="kstat-label">Total Simpanan Koperasi</div>
      <div class="kstat-trend"><i class="bi bi-people-fill me-1"></i> {{ number_format($totalAnggotaAktif) }} anggota aktif</div>
    </div>
  </div>
  <div class="col-xl col-sm-6">
    <div class="kstat-card kstat-green">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-piggy-bank-fill"></i></div>
      <div class="kstat-value kstat-value-sm">Rp {{ number_format($totalSimpanan, 0, ',', '.') }}</div>
      <div class="kstat-label">Simpanan Saya</div>
      <div class="kstat-trend"><i class="bi bi-wallet2 me-1"></i> Pokok + Wajib + Sukarela</div>
    </div>
  </div>
  <div class="col-xl col-sm-6">
    <div class="kstat-card kstat-blue">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-calendar-check-fill"></i></div>
      <div class="kstat-value kstat-value-sm">Rp {{ number_format($totalWajib, 0, ',', '.') }}</div>
      <div class="kstat-label">Simpanan Wajib Saya</div>
      <div class="kstat-trend">
        @if($wajibPerJenis)
          <i class="bi bi-arrow-up-circle-fill me-1"></i> Rp {{ number_format($wajibPerJenis['nominal'],0,',','.') }}/bln
        @else
          <i class="bi bi-arrow-up-circle-fill me-1"></i> Rutin bulanan
        @endif
      </div>
    </div>
  </div>
  <div class="col-xl col-sm-6">
    <div class="kstat-card kstat-amber">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-cash-coin"></i></div>
      <div class="kstat-value kstat-value-sm">{{ $semuaPinjaman->count() }}</div>
      <div class="kstat-label">Total Pinjaman</div>
      <div class="kstat-trend"><i class="bi bi-activity me-1"></i> {{ $pinjamanAktif->count() }} aktif</div>
    </div>
  </div>
  <div class="col-xl col-sm-6">
    <div class="kstat-card {{ $totalSisaHutang > 0 ? 'kstat-red' : 'kstat-green' }}">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-{{ $totalSisaHutang > 0 ? 'exclamation-circle' : 'check-circle' }}-fill"></i></div>
      <div class="kstat-value kstat-value-sm">Rp {{ number_format(max(0,$totalSisaHutang), 0, ',', '.') }}</div>
      <div class="kstat-label">Sisa Hutang</div>
      <div class="kstat-trend">
        <i class="bi bi-{{ $totalSisaHutang > 0 ? 'clock-history' : 'patch-check-fill' }} me-1"></i>
        {{ $totalSisaHutang > 0 ? 'Belum lunas' : 'Bersih' }}
      </div>
    </div>
  </div>
</div>

<div class="row g-4">

  {{-- SIMPANAN RINGKASAN --}}
  <div class="col-lg-4">
    <div class="form-card h-100">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h6 class="mb-0 fw-700 is-5ca88772">
          <i class="bi bi-piggy-bank-fill me-2 is-29fb9df2"></i>Simpanan Saya
        </h6>
        <a href="{{ route('anggota.rekap', $anggota) }}" class="btn btn-sm btn-outline-success is-640fb1f6">Detail</a>
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
          <div class="is-57779888">
            <div class="d-flex justify-content-between mb-1">
              <span class="is-d19f4a3d">{{ $cfg['label'] }}</span>
              <span class="is-80283511">Rp {{ number_format($cfg['val'],0,',','.') }}</span>
            </div>
            <div class="is-ef544b6b">
              <div class="is-93f96cc8"></div>
            </div>
          </div>
        @endforeach
      </div>
      <hr class="my-3">
      <div class="d-flex justify-content-between">
        <span class="is-3f8b0c8f">Total</span>
        <span class="is-cc0f872a">Rp {{ number_format($totalSimpanan,0,',','.') }}</span>
      </div>
    </div>
  </div>

  {{-- PINJAMAN AKTIF --}}
  <div class="col-lg-5">
    <div class="form-card h-100 p-0 overflow-hidden">
      <div class="p-3 border-bottom d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-700 is-5ca88772">
          <i class="bi bi-cash-coin me-2 is-2a4cda41"></i>Pinjaman Saya
        </h6>
        <a href="{{ route('anggota.rekap', $anggota) }}" class="btn btn-sm btn-outline-warning is-640fb1f6">Detail</a>
      </div>
      @forelse($semuaPinjaman->sortByDesc('tanggal_pengajuan')->take(4) as $p)
        @php
          $terbayar   = $p->angsuran->where('status','sudah')->sum('jumlah_angsuran');
          $totalCicil = $p->angsuran->sum('jumlah_angsuran');
          $pct        = $totalCicil > 0 ? min(100, round($terbayar / $totalCicil * 100)) : 0;
          $badgeMap   = ['diajukan'=>'badge-diajukan','diverifikasi'=>'badge-diverifikasi','disetujui'=>'badge-disetujui','ditolak'=>'badge-ditolak','lunas'=>'badge-lunas'];
        @endphp
        <div class="is-621eafb9">
          <div class="d-flex justify-content-between align-items-start mb-1">
            <div>
              <code class="is-0bf00d29">{{ $p->nomor_pinjaman }}</code>
              <span class="badge-status {{ $badgeMap[$p->status] ?? '' }} ms-1">{{ ucfirst($p->status) }}</span>
            </div>
            <span class="is-880c67fd">Rp {{ number_format($p->jumlah_pinjaman,0,',','.') }}</span>
          </div>
          @if($p->angsuran->count())
            <div class="is-dabb2c00">
              <div class="is-eb383064"></div>
            </div>
            <div class="is-32f18a6a">
              {{ $p->angsuran->where('status','sudah')->count() }}/{{ $p->angsuran->count() }} angsuran · {{ $pct }}%
            </div>
          @endif
        </div>
      @empty
        <div class="text-center py-5 text-muted">
          <i class="bi bi-inbox d-block mb-2 is-0356d33d"></i>
          <div class="is-9ee0bb07">Belum ada pinjaman</div>
        </div>
      @endforelse
    </div>
  </div>

  {{-- ANGSURAN MENDATANG --}}
  <div class="col-lg-3">
    <div class="form-card h-100 p-0 overflow-hidden">
      <div class="p-3 border-bottom">
        <h6 class="mb-0 fw-700 is-5ca88772">
          <i class="bi bi-alarm-fill me-2 is-2a4cda41"></i>Angsuran Mendatang
        </h6>
      </div>
      @if($angsuranMendatang->count())
        @foreach($angsuranMendatang as $ang)
          @php $lewat = $ang->tanggal_jatuh_tempo < now(); @endphp
          <div class="is-ee105cb9">
            <div class="d-flex align-items-center gap-2">
              <div class="flex-shrink-0 d-flex align-items-center justify-content-center rounded-circle is-c6d037ed"
               >
                <i class="bi bi-{{ $lewat ? 'exclamation-triangle' : 'clock' }} is-6c660c40"></i>
              </div>
              <div class="flex-grow-1">
                <div class="is-28d53b44">Ke-{{ $ang->ke }}</div>
                <div class="is-3c42df57">
                  {{ $ang->tanggal_jatuh_tempo->format('d M Y') }}
                  {{ $lewat ? '· Terlambat' : '' }}
                </div>
              </div>
              <div class="is-880c67fd">Rp {{ number_format($ang->jumlah_angsuran,0,',','.') }}</div>
            </div>
          </div>
        @endforeach
      @else
        <div class="text-center py-5 text-muted">
          <i class="bi bi-check2-all d-block mb-2 is-0356d33d"></i>
          <div class="is-0de4e77b">Tidak ada angsuran<br>mendatang</div>
        </div>
      @endif
    </div>
  </div>

</div>

@endsection
