@extends('layouts.aplikasi')

@section('judul', 'Rekap Profil — ' . $anggota->nama)

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('anggota.index') }}">Anggota</a></li>
  <li class="breadcrumb-item active">Rekap Profil</li>
@endsection

@section('konten')

@php
  $totalSimpanan   = $anggota->simpanan->sum('jumlah');
  $totalPokok      = $anggota->simpanan->where('jenis_simpanan','pokok')->sum('jumlah');
  $totalWajib      = $anggota->simpanan->where('jenis_simpanan','wajib')->sum('jumlah');
  $totalSukarela   = $anggota->simpanan->where('jenis_simpanan','sukarela')->sum('jumlah');

  $semuaPinjaman   = $anggota->pinjaman;
  $totalPinjaman   = $semuaPinjaman->whereIn('status',['disetujui','lunas'])->sum('jumlah_pinjaman');
  $pinjamanAktif   = $semuaPinjaman->where('status','disetujui');
  $totalTerbayar   = $semuaPinjaman->flatMap(fn($p) => $p->angsuran)->where('status','sudah')->sum('jumlah_angsuran');
  $totalSisaHutang = $pinjamanAktif
    ->map(fn($p) => $p->jumlah_pinjaman - $p->angsuran->where('status','sudah')->sum('jumlah_angsuran'))
    ->sum();
  $totalDendaEstimasi = $semuaPinjaman->flatMap(fn($p) => $p->angsuran)
    ->where('status','belum')
    ->filter(fn($a) => $a->tanggal_jatuh_tempo < now())
    ->sum(fn($a) => $a->hitungDenda());
@endphp

{{-- ─── PROFILE HEADER ─── --}}
<div class="rekap-header mb-4">
  <div class="d-flex align-items-center gap-4 flex-wrap">
    @if($anggota->foto)
      <img src="{{ Storage::url($anggota->foto) }}" alt="{{ $anggota->nama }}"
        class="rounded-circle object-fit-cover flex-shrink-0 shadow"
        style="width:80px;height:80px;border:3px solid rgba(255,255,255,.6)">
    @else
      <div class="rekap-avatar flex-shrink-0">{{ strtoupper(substr($anggota->nama,0,1)) }}</div>
    @endif
    <div class="flex-grow-1 min-w-0">
      <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
        <h4 class="mb-0 fw-700" style="color:#112344">{{ $anggota->nama }}</h4>
        <span class="badge-status {{ $anggota->status === 'aktif' ? 'badge-aktif' : 'badge-ditolak' }}">
          {{ $anggota->status === 'aktif' ? 'Aktif' : 'Tidak Aktif' }}
        </span>
        @if($anggota->jenis_anggota)
          <span class="rekap-badge-jenis">{{ $anggota->jenis_anggota }}</span>
        @endif
      </div>
      <div class="d-flex gap-4 flex-wrap" style="font-size:.85rem;color:#475569">
        <span><i class="bi bi-credit-card-2-front me-1"></i><code class="text-primary fw-600" style="background:#eef2ff;padding:.1rem .4rem;border-radius:.3rem;font-size:.82rem">{{ $anggota->nomor_anggota }}</code></span>
        @if($anggota->unitKerja)
          <span><i class="bi bi-building me-1"></i>{{ $anggota->unitKerja->nama }}</span>
        @endif
        <span><i class="bi bi-calendar3 me-1"></i>Bergabung {{ $anggota->tanggal_bergabung->format('d M Y') }}</span>
        @if($anggota->telepon)
          <span><i class="bi bi-telephone me-1"></i>{{ $anggota->telepon }}</span>
        @endif
        @if($anggota->nik)
          <span><i class="bi bi-person-vcard me-1"></i>NIK {{ $anggota->nik }}</span>
        @endif
        @if($anggota->tempat_lahir && $anggota->tanggal_lahir)
          <span><i class="bi bi-geo-alt me-1"></i>{{ $anggota->tempat_lahir }}, {{ $anggota->tanggal_lahir->format('d M Y') }}</span>
        @endif
      </div>
    </div>
    <div class="d-flex gap-2 flex-shrink-0">
      <a href="{{ route('anggota.edit', $anggota) }}" class="btn btn-sm btn-outline-warning">
        <i class="bi bi-pencil me-1"></i>Ubah
      </a>
      <a href="{{ route('anggota.show', $anggota) }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-person-vcard me-1"></i>Detail
      </a>
    </div>
  </div>
</div>

{{-- ─── 4 STAT CARDS ─── --}}
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="rekap-stat-card" style="border-left-color:#10b981">
      <div class="rekap-stat-icon" style="background:rgba(16,185,129,.1);color:#059669"><i class="bi bi-piggy-bank-fill"></i></div>
      <div class="rekap-stat-label">Total Simpanan</div>
      <div class="rekap-stat-value" style="color:#059669">Rp {{ number_format($totalSimpanan,0,',','.') }}</div>
      <div class="rekap-stat-sub">{{ $anggota->simpanan->count() }} transaksi</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="rekap-stat-card" style="border-left-color:#3b82f6">
      <div class="rekap-stat-icon" style="background:rgba(59,130,246,.1);color:#2563eb"><i class="bi bi-bank"></i></div>
      <div class="rekap-stat-label">Simpanan Wajib</div>
      <div class="rekap-stat-value" style="color:#2563eb">Rp {{ number_format($totalWajib,0,',','.') }}</div>
      <div class="rekap-stat-sub">
        @if($wajibPerJenis) Seharusnya Rp {{ number_format($wajibPerJenis['nominal'],0,',','.') }}/bln
        @else — @endif
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="rekap-stat-card" style="border-left-color:#f59e0b">
      <div class="rekap-stat-icon" style="background:rgba(245,158,11,.1);color:#d97706"><i class="bi bi-cash-stack"></i></div>
      <div class="rekap-stat-label">Total Pinjaman</div>
      <div class="rekap-stat-value" style="color:#d97706">Rp {{ number_format($totalPinjaman,0,',','.') }}</div>
      <div class="rekap-stat-sub">{{ $semuaPinjaman->count() }} pengajuan</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="rekap-stat-card" style="border-left-color:{{ $totalSisaHutang > 0 ? '#ef4444' : '#10b981' }}">
      <div class="rekap-stat-icon" style="background:{{ $totalSisaHutang > 0 ? 'rgba(239,68,68,.1)' : 'rgba(16,185,129,.1)' }};color:{{ $totalSisaHutang > 0 ? '#dc2626' : '#059669' }}">
        <i class="bi bi-{{ $totalSisaHutang > 0 ? 'exclamation-circle' : 'check-circle' }}-fill"></i>
      </div>
      <div class="rekap-stat-label">Sisa Hutang</div>
      <div class="rekap-stat-value" style="color:{{ $totalSisaHutang > 0 ? '#dc2626' : '#059669' }}">Rp {{ number_format(max(0,$totalSisaHutang),0,',','.') }}</div>
      <div class="rekap-stat-sub">
        {{ $totalSisaHutang > 0 ? 'Belum lunas' : 'Bersih' }}
        @if($totalDendaEstimasi > 0)
          <span class="text-danger"> · Denda ~Rp {{ number_format($totalDendaEstimasi,0,',','.') }}</span>
        @endif
      </div>
    </div>
  </div>
</div>

<div class="row g-4">

  {{-- ─── SIMPANAN ─── --}}
  <div class="col-lg-4">
    <div class="form-card h-100">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h6 class="mb-0 fw-700" style="color:#112344">
          <i class="bi bi-piggy-bank-fill me-2" style="color:#10b981"></i>Simpanan
        </h6>
        <a href="{{ route('simpanan.create') }}?anggota_id={{ $anggota->id }}" class="btn btn-sm btn-success">
          <i class="bi bi-plus"></i>
        </a>
      </div>
      @php
        $simpananTypes = [
          'pokok'    => ['label'=>'Simpanan Pokok',    'color'=>'#8b5cf6', 'bg'=>'#faf5ff', 'border'=>'#e9d5ff'],
          'wajib'    => ['label'=>'Simpanan Wajib',    'color'=>'#2563eb', 'bg'=>'#eff6ff', 'border'=>'#bfdbfe'],
          'sukarela' => ['label'=>'Simpanan Sukarela', 'color'=>'#059669', 'bg'=>'#f0fdf4', 'border'=>'#bbf7d0'],
        ];
      @endphp
      <div class="d-flex flex-column gap-2">
        @foreach($simpananTypes as $jenis => $cfg)
          @php
            $jml = $anggota->simpanan->where('jenis_simpanan',$jenis)->sum('jumlah');
            $pct = $totalSimpanan > 0 ? round($jml / $totalSimpanan * 100) : 0;
            $cnt = $anggota->simpanan->where('jenis_simpanan',$jenis)->count();
          @endphp
          <div style="background:{{ $cfg['bg'] }};border:1px solid {{ $cfg['border'] }};border-radius:.6rem;padding:.7rem .9rem">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <span style="font-size:.82rem;font-weight:600;color:{{ $cfg['color'] }}">{{ $cfg['label'] }}</span>
              <span style="font-size:.82rem;font-weight:700;color:{{ $cfg['color'] }}">Rp {{ number_format($jml,0,',','.') }}</span>
            </div>
            <div style="background:#e5e7eb;border-radius:50px;height:5px">
              <div style="background:{{ $cfg['color'] }};width:{{ $pct }}%;height:5px;border-radius:50px"></div>
            </div>
            <div style="font-size:.72rem;color:#9ca3af;margin-top:.25rem">{{ $cnt }} transaksi · {{ $pct }}%</div>
          </div>
        @endforeach
      </div>
      <hr class="my-3">
      <div class="d-flex justify-content-between align-items-center">
        <span style="font-size:.85rem;font-weight:600;color:#374151">Total Simpanan</span>
        <span style="font-size:1rem;font-weight:700;color:#059669">Rp {{ number_format($totalSimpanan,0,',','.') }}</span>
      </div>
    </div>
  </div>

  {{-- ─── PINJAMAN ─── --}}
  <div class="col-lg-5">
    <div class="form-card h-100 p-0 overflow-hidden">
      <div class="p-3 border-bottom d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-700" style="color:#112344">
          <i class="bi bi-cash-coin me-2" style="color:#f59e0b"></i>Pinjaman
        </h6>
        <a href="{{ route('pinjaman.create') }}?anggota_id={{ $anggota->id }}" class="btn btn-sm btn-warning text-white">
          <i class="bi bi-plus"></i>
        </a>
      </div>
      @forelse($semuaPinjaman->sortByDesc('tanggal_pengajuan') as $p)
        @php
          $terbayar   = $p->angsuran->where('status','sudah')->sum('jumlah_angsuran');
          $totalCicil = $p->angsuran->sum('jumlah_angsuran');
          $pct        = $totalCicil > 0 ? min(100, round($terbayar / $totalCicil * 100)) : 0;
          $sisa       = $p->angsuran->where('status','belum')->count();
          $badgeMap   = ['diajukan'=>'badge-diajukan','diverifikasi'=>'badge-diverifikasi','disetujui'=>'badge-disetujui','ditolak'=>'badge-ditolak','lunas'=>'badge-lunas'];
        @endphp
        <div class="pinjaman-rekap-item">
          <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
            <div>
              <code style="background:#eef2ff;color:#3b5bdb;border:1px solid #e0e7ff;border-radius:.3rem;padding:.1rem .4rem;font-size:.78rem;font-weight:700">{{ $p->nomor_pinjaman }}</code>
              <span class="badge-status {{ $badgeMap[$p->status] ?? '' }} ms-1">{{ ucfirst($p->status) }}</span>
              @if($p->jenis_pinjaman)
                <span class="ms-1" style="font-size:.7rem;background:rgba(23,92,221,.08);color:#175cdd;border-radius:.3rem;padding:.1rem .4rem">{{ $p->jenis_pinjaman }}</span>
              @endif
            </div>
            <a href="{{ route('pinjaman.show', $p) }}" class="btn btn-sm btn-outline-info py-0 px-2 flex-shrink-0" style="font-size:.75rem">
              <i class="bi bi-eye"></i>
            </a>
          </div>
          <div class="row g-2 mb-2" style="font-size:.8rem">
            <div class="col-6">
              <div style="color:#9ca3af;font-size:.7rem">Jumlah Pinjaman</div>
              <div style="font-weight:700">Rp {{ number_format($p->jumlah_pinjaman,0,',','.') }}</div>
            </div>
            <div class="col-6">
              <div style="color:#9ca3af;font-size:.7rem">Cicilan/Bulan</div>
              <div style="font-weight:700;color:#175cdd">Rp {{ number_format($p->cicilan_per_bulan,0,',','.') }}</div>
            </div>
            <div class="col-6">
              <div style="color:#9ca3af;font-size:.7rem">Sudah Dibayar</div>
              <div style="font-weight:700;color:#059669">Rp {{ number_format($terbayar,0,',','.') }}</div>
            </div>
            <div class="col-6">
              <div style="color:#9ca3af;font-size:.7rem">Sisa Pokok</div>
              <div style="font-weight:700;color:{{ $sisa > 0 ? '#dc2626' : '#059669' }}">
                @if($sisa > 0) Rp {{ number_format(max(0,$totalCicil - $terbayar),0,',','.') }}
                @else <i class="bi bi-check-circle-fill me-1"></i>Lunas
                @endif
              </div>
            </div>
          </div>
          @if($p->angsuran->count())
            <div style="background:#e5e7eb;border-radius:50px;height:7px">
              <div style="background:{{ $pct >= 100 ? 'linear-gradient(90deg,#059669,#10b981)' : 'linear-gradient(90deg,#2563eb,#60a5fa)' }};width:{{ $pct }}%;height:7px;border-radius:50px"></div>
            </div>
            <div class="d-flex justify-content-between mt-1" style="font-size:.7rem;color:#9ca3af">
              <span>{{ $p->angsuran->where('status','sudah')->count() }}/{{ $p->angsuran->count() }} angsuran · {{ $pct }}%</span>
              <span>{{ $p->tanggal_pengajuan->format('d M Y') }}</span>
            </div>
          @endif
        </div>
      @empty
        <div class="text-center py-5 text-muted">
          <i class="bi bi-cash-coin d-block mb-1" style="font-size:1.8rem;opacity:.3"></i>
          Belum ada pinjaman
        </div>
      @endforelse
    </div>
  </div>

  {{-- ─── ANGSURAN MENDATANG ─── --}}
  <div class="col-lg-3">
    <div class="form-card h-100 p-0 overflow-hidden">
      <div class="p-3 border-bottom">
        <h6 class="mb-0 fw-700" style="color:#112344">
          <i class="bi bi-alarm-fill me-2" style="color:#f59e0b"></i>Angsuran Mendatang
        </h6>
      </div>
      @if($angsuranMendatang->count())
        <div class="d-flex flex-column">
          @foreach($angsuranMendatang as $ang)
            @php $lewat = $ang->tanggal_jatuh_tempo < now(); @endphp
            <div style="padding:.7rem 1rem;border-bottom:1px solid #f1f5f9;{{ $lewat ? 'background:#fff8f8' : '' }}">
              <div class="d-flex align-items-center gap-2">
                <div class="flex-shrink-0 d-flex align-items-center justify-content-center rounded-circle"
                  style="width:32px;height:32px;background:{{ $lewat ? 'rgba(239,68,68,.1)' : 'rgba(245,158,11,.1)' }};color:{{ $lewat ? '#dc2626' : '#d97706' }}">
                  <i class="bi bi-{{ $lewat ? 'exclamation-triangle' : 'clock' }}" style="font-size:.85rem"></i>
                </div>
                <div class="flex-grow-1 min-w-0">
                  <div style="font-size:.8rem;font-weight:600;color:#112344">
                    Ke-{{ $ang->ke }}
                    <span style="font-size:.72rem;color:#9ca3af;font-weight:400">{{ $ang->pinjaman->nomor_pinjaman ?? '' }}</span>
                  </div>
                  <div style="font-size:.72rem;color:{{ $lewat ? '#dc2626' : '#9ca3af' }}">
                    {{ $ang->tanggal_jatuh_tempo->format('d M Y') }}
                    {{ $lewat ? '· Terlambat' : '' }}
                  </div>
                </div>
                <div class="text-end flex-shrink-0">
                  <div style="font-size:.82rem;font-weight:700">Rp {{ number_format($ang->jumlah_angsuran,0,',','.') }}</div>
                  @if($lewat) @php $est = $ang->hitungDenda() @endphp
                    @if($est > 0) <div style="font-size:.7rem;color:#dc2626">+Rp {{ number_format($est,0,',','.') }}</div> @endif
                  @endif
                </div>
              </div>
            </div>
          @endforeach
        </div>
      @else
        <div class="text-center py-5 text-muted">
          <i class="bi bi-check2-all d-block mb-2" style="font-size:1.8rem;opacity:.3"></i>
          <div style="font-size:.83rem">Tidak ada angsuran<br>mendatang</div>
        </div>
      @endif
    </div>
  </div>

</div>
@endsection
