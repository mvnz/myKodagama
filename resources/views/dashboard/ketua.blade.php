@extends('layouts.aplikasi')

@section('judul', 'Dashboard Ketua')

@section('konten')

{{-- Welcome Banner --}}
<div class="dashboard-welcome mb-4">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
      <div class="welcome-label mb-1">Selamat datang,</div>
      <h4 class="mb-1 fw-700">{{ auth()->user()->name }}</h4>
      <p class="mb-0 welcome-date">
        <i class="bi bi-person-badge me-1"></i>
        {{ auth()->user()->roleModel?->nama ?? 'Ketua' }}
        &nbsp;·&nbsp;
        <i class="bi bi-calendar3 me-1"></i>
        {{ now()->translatedFormat('l, d F Y') }}
      </p>
    </div>
    <div class="welcome-icon-wrap">
      <i class="bi bi-shield-check"></i>
    </div>
  </div>
</div>

{{-- Stat Cards --}}
<div class="row g-3 mb-4">
  <div class="col-sm-4">
    <div class="kstat-card kstat-blue">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-people-fill"></i></div>
      <div class="kstat-value">{{ number_format($totalAnggota) }}</div>
      <div class="kstat-label">Anggota Aktif</div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="kstat-card kstat-green">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-piggy-bank-fill"></i></div>
      <div class="kstat-value kstat-value-sm">Rp {{ number_format($totalSimpanan, 0, ',', '.') }}</div>
      <div class="kstat-label">Total Simpanan</div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="kstat-card kstat-amber">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-cash-coin"></i></div>
      <div class="kstat-value kstat-value-sm">Rp {{ number_format($totalPinjaman, 0, ',', '.') }}</div>
      <div class="kstat-label">Pinjaman Aktif</div>
    </div>
  </div>
</div>

<div class="row g-4 mb-4">

  {{-- MENUNGGU VERIFIKASI --}}
  <div class="col-lg-6">
    <div class="form-card p-0 overflow-hidden">
      <div class="d-flex align-items-center gap-2 p-3 border-bottom"
        style="background:linear-gradient(135deg,#eff6ff,#dbeafe)">
        <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0"
          style="width:36px;height:36px;background:rgba(59,130,246,.15);color:#2563eb">
          <i class="bi bi-clock-history"></i>
        </div>
        <div class="flex-grow-1">
          <h6 class="mb-0 fw-700" style="color:#1e3a8a">Menunggu Verifikasi</h6>
          <small class="text-muted">{{ $menungguVerifikasi->count() }} pengajuan baru</small>
        </div>
        <a href="{{ route('pinjaman.index') }}?status=diajukan" class="btn btn-sm btn-outline-primary" style="font-size:.75rem">Semua</a>
      </div>

      @if($menungguVerifikasi->isEmpty())
        <div class="text-center py-4 text-muted">
          <i class="bi bi-check2-all d-block mb-1" style="font-size:1.6rem;opacity:.4"></i>
          <div style="font-size:.83rem">Tidak ada pengajuan menunggu</div>
        </div>
      @else
        @foreach($menungguVerifikasi->take(5) as $p)
        <div class="d-flex align-items-center gap-3 px-3 py-2 border-bottom">
          <div class="flex-grow-1 min-w-0">
            <div class="fw-600" style="font-size:.85rem">{{ $p->anggota->nama ?? '—' }}</div>
            <div style="font-size:.73rem;color:#9ca3af">
              <code style="background:#eef2ff;color:#3b5bdb;border:1px solid #e0e7ff;border-radius:.25rem;padding:0 .3rem">{{ $p->nomor_pinjaman }}</code>
              · {{ $p->tanggal_pengajuan->format('d M Y') }}
            </div>
          </div>
          <div class="text-end flex-shrink-0">
            <div style="font-size:.85rem;font-weight:700">Rp {{ number_format($p->jumlah_pinjaman, 0, ',', '.') }}</div>
            <a href="{{ route('pinjaman.show', $p) }}" class="btn btn-xs btn-primary mt-1">
              <i class="bi bi-check-lg me-1"></i>Verifikasi
            </a>
          </div>
        </div>
        @endforeach
        @if($menungguVerifikasi->count() > 5)
          <div class="text-center py-2">
            <a href="{{ route('pinjaman.index') }}?status=diajukan" style="font-size:.8rem;color:#2563eb">
              +{{ $menungguVerifikasi->count() - 5 }} lainnya
            </a>
          </div>
        @endif
      @endif
    </div>
  </div>

  {{-- MENUNGGU PERSETUJUAN --}}
  <div class="col-lg-6">
    <div class="form-card p-0 overflow-hidden">
      <div class="d-flex align-items-center gap-2 p-3 border-bottom"
        style="background:linear-gradient(135deg,#fff7ed,#ffedd5)">
        <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0"
          style="width:36px;height:36px;background:rgba(245,158,11,.15);color:#d97706">
          <i class="bi bi-hourglass-split"></i>
        </div>
        <div class="flex-grow-1">
          <h6 class="mb-0 fw-700" style="color:#92400e">Menunggu Persetujuan</h6>
          <small class="text-muted">{{ $menungguPersetujuan->count() }} sudah diverifikasi</small>
        </div>
        <a href="{{ route('pinjaman.index') }}?status=diverifikasi" class="btn btn-sm btn-outline-warning" style="font-size:.75rem">Semua</a>
      </div>

      @if($menungguPersetujuan->isEmpty())
        <div class="text-center py-4 text-muted">
          <i class="bi bi-check2-all d-block mb-1" style="font-size:1.6rem;opacity:.4"></i>
          <div style="font-size:.83rem">Tidak ada yang menunggu persetujuan</div>
        </div>
      @else
        @foreach($menungguPersetujuan->take(5) as $p)
        <div class="d-flex align-items-center gap-3 px-3 py-2 border-bottom">
          <div class="flex-grow-1 min-w-0">
            <div class="fw-600" style="font-size:.85rem">{{ $p->anggota->nama ?? '—' }}</div>
            <div style="font-size:.73rem;color:#9ca3af">
              <code style="background:#fff7ed;color:#c2410c;border:1px solid #fed7aa;border-radius:.25rem;padding:0 .3rem">{{ $p->nomor_pinjaman }}</code>
              · {{ $p->tanggal_pengajuan->format('d M Y') }}
            </div>
          </div>
          <div class="text-end flex-shrink-0">
            <div style="font-size:.85rem;font-weight:700">Rp {{ number_format($p->jumlah_pinjaman, 0, ',', '.') }}</div>
            <a href="{{ route('pinjaman.show', $p) }}" class="btn btn-xs btn-warning text-white mt-1">
              <i class="bi bi-check-circle me-1"></i>Setujui
            </a>
          </div>
        </div>
        @endforeach
        @if($menungguPersetujuan->count() > 5)
          <div class="text-center py-2">
            <a href="{{ route('pinjaman.index') }}?status=diverifikasi" style="font-size:.8rem;color:#d97706">
              +{{ $menungguPersetujuan->count() - 5 }} lainnya
            </a>
          </div>
        @endif
      @endif
    </div>
  </div>

</div>

{{-- ANGSURAN TELAT + HAK AKSES --}}
<div class="row g-4">

  {{-- ANGSURAN TELAT --}}
  <div class="col-lg-8">
    <div class="form-card p-0 overflow-hidden">
      <div class="d-flex align-items-center gap-2 p-3 border-bottom"
        style="background:linear-gradient(135deg,#fff5f5,#fef2f2)">
        <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0"
          style="width:36px;height:36px;background:rgba(239,68,68,.15);color:#dc2626">
          <i class="bi bi-alarm-fill"></i>
        </div>
        <div class="flex-grow-1">
          <h6 class="mb-0 fw-700" style="color:#991b1b">Angsuran Terlambat</h6>
          <small class="text-muted">{{ $angsuranTelat->count() }} tagihan belum dibayar</small>
        </div>
        <a href="{{ route('monitoring.pinjaman') }}" class="btn btn-sm btn-outline-danger" style="font-size:.75rem">Monitoring</a>
      </div>

      @if($angsuranTelat->isEmpty())
        <div class="text-center py-4 text-muted">
          <i class="bi bi-check2-circle d-block mb-1" style="font-size:1.6rem;color:#10b981;opacity:.6"></i>
          <div style="font-size:.83rem">Tidak ada angsuran terlambat</div>
        </div>
      @else
        <div class="table-responsive">
          <table class="koperasi-table mb-0">
            <thead>
              <tr>
                <th>Anggota</th>
                <th>No. Pinjaman</th>
                <th>Ke</th>
                <th>Jatuh Tempo</th>
                <th class="text-end">Jumlah</th>
              </tr>
            </thead>
            <tbody>
              @foreach($angsuranTelat as $ang)
              <tr style="background:#fff8f8">
                <td>
                  <div class="fw-600" style="font-size:.85rem">{{ $ang->pinjaman->anggota->nama ?? '—' }}</div>
                </td>
                <td><code style="font-size:.78rem">{{ $ang->pinjaman->nomor_pinjaman ?? '—' }}</code></td>
                <td class="text-center">{{ $ang->ke }}</td>
                <td style="color:#dc2626;font-size:.83rem;font-weight:600">
                  {{ $ang->tanggal_jatuh_tempo->format('d M Y') }}
                  <div style="font-size:.7rem;font-weight:400">{{ $ang->tanggal_jatuh_tempo->diffForHumans() }}</div>
                </td>
                <td class="text-end fw-700">Rp {{ number_format($ang->jumlah_angsuran, 0, ',', '.') }}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </div>
  </div>

  {{-- HAK AKSES --}}
  <div class="col-lg-4">
    <div class="form-card h-100">
      <h6 class="fw-700 mb-3" style="color:#112344">
        <i class="bi bi-shield-check me-2" style="color:#175cdd"></i>Hak Akses Anda
      </h6>
      <div class="d-flex flex-column gap-1">
        @forelse($permissions as $perm)
          <div class="d-flex align-items-center gap-2 py-1" style="font-size:.82rem">
            <i class="bi bi-check-circle-fill flex-shrink-0" style="color:#10b981;font-size:.75rem"></i>
            <span style="color:#374151">{{ $perm->nama }}</span>
          </div>
        @empty
          <div class="text-muted" style="font-size:.83rem">Tidak ada hak akses khusus.</div>
        @endforelse
      </div>
    </div>
  </div>

</div>

@endsection
