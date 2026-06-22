@extends('layouts.aplikasi')

@section('judul', 'Dashboard')
@section('konten')

{{-- Welcome Banner --}}
<div class="dashboard-welcome mb-4">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
      <div class="welcome-label mb-1">Selamat datang kembali,</div>
      <h4 class="mb-1 fw-700">{{ auth()->user()->name }}</h4>
      <p class="mb-0 welcome-date">
        <i class="bi bi-calendar3 me-1"></i>
        {{ now()->translatedFormat('l, d F Y') }}
      </p>
    </div>
    <div class="welcome-icon-wrap">
      <i class="bi bi-bank2"></i>
    </div>
  </div>
</div>

{{-- Stat Cards --}}
<div class="row g-4 mb-4">
  <div class="col-xl-3 col-sm-6">
    <div class="kstat-card kstat-blue">
      <div class="kstat-deco"></div>
      <div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-people-fill"></i></div>
      <div class="kstat-value">{{ number_format($totalAnggota) }}</div>
      <div class="kstat-label">Anggota Aktif</div>
      <div class="kstat-trend">
        <i class="bi bi-{{ $anggotaBaru > 0 ? 'arrow-up-circle-fill' : 'dash-circle-fill' }} me-1"></i>
        {{ $anggotaBaru > 0 ? $anggotaBaru.' baru bulan ini' : 'Tidak ada yang baru' }}
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-sm-6">
    <div class="kstat-card kstat-green">
      <div class="kstat-deco"></div>
      <div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-piggy-bank-fill"></i></div>
      <div class="kstat-value kstat-value-sm">Rp {{ number_format($totalSimpanan, 0, ',', '.') }}</div>
      <div class="kstat-label">Total Simpanan</div>
      <div class="kstat-trend">
        <i class="bi bi-arrow-up-circle-fill me-1"></i> Meningkat
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-sm-6">
    <div class="kstat-card kstat-amber">
      <div class="kstat-deco"></div>
      <div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-cash-coin"></i></div>
      <div class="kstat-value kstat-value-sm">Rp {{ number_format($totalPinjaman, 0, ',', '.') }}</div>
      <div class="kstat-label">Pinjaman Aktif</div>
      <div class="kstat-trend">
        <i class="bi bi-arrow-up-circle-fill me-1"></i> Berjalan
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-sm-6">
    <div class="kstat-card kstat-red">
      <div class="kstat-deco"></div>
      <div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-hourglass-split"></i></div>
      <div class="kstat-value">{{ number_format($pengajuanBaru) }}</div>
      <div class="kstat-label">Pengajuan Baru</div>
      <div class="kstat-trend">
        @if($pengajuanBaru > 0)
          <i class="bi bi-exclamation-circle-fill me-1"></i> Perlu ditinjau
        @else
          <i class="bi bi-check-circle-fill me-1"></i> Semua diproses
        @endif
      </div>
    </div>
  </div>
</div>

{{-- Secondary Row --}}
<div class="row g-4 mb-4">
  {{-- Angsuran Jatuh Tempo --}}
  <div class="col-xl-4 col-md-6">
    <div class="info-card info-purple h-100">
      <div class="info-card-icon">
        <i class="bi bi-calendar-event-fill"></i>
      </div>
      <div class="info-card-body">
        <div class="info-card-value">{{ number_format($angsuranJatuhTempoHariIni) }}</div>
        <div class="info-card-label">Angsuran Jatuh Tempo Hari Ini</div>
        <a href="{{ route('angsuran.index') }}" class="info-card-link mt-2">
          Lihat semua angsuran <i class="bi bi-arrow-right ms-1"></i>
        </a>
      </div>
    </div>
  </div>

  {{-- Simpanan Terbaru --}}
  <div class="col-xl-4 col-md-6">
    <div class="form-card h-100 p-0 overflow-hidden">
      <div class="px-4 py-3 border-bottom d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-700 d-flex align-items-center gap-2 is-c9882108">
          <i class="bi bi-piggy-bank-fill is-29fb9df2"></i> Simpanan Terbaru
        </h6>
        <a href="{{ route('simpanan.index') }}" class="btn btn-sm btn-outline-primary is-18deaefd">Semua</a>
      </div>
      <div class="p-3 d-flex flex-column gap-2">
        @forelse($simpananTerbaru->take(4) as $s)
          <div class="d-flex align-items-center gap-2">
            <div class="flex-shrink-0 d-flex align-items-center justify-content-center rounded-circle is-b4cba0ab"
             >
              <i class="bi bi-person is-24b9491f"></i>
            </div>
            <div class="flex-grow-1 min-w-0">
              <div class="text-truncate is-38269d4d">{{ $s->anggota->nama ?? 'n/a' }}</div>
              <div class="is-22508fb3">{{ $s->tanggal?->format('d M Y') ?? '-' }}</div>
            </div>
            <div class="is-72294a24">
              +Rp {{ number_format($s->jumlah, 0, ',', '.') }}
            </div>
          </div>
        @empty
          <p class="text-muted text-center py-2 mb-0 is-9ee0bb07">Belum ada simpanan</p>
        @endforelse
      </div>
    </div>
  </div>

  {{-- Akses Cepat --}}
  <div class="col-xl-4">
    <div class="form-card h-100">
      <h6 class="mb-3 fw-700 d-flex align-items-center gap-2 is-c9882108">
        <i class="bi bi-lightning-fill is-2a4cda41"></i> Akses Cepat
      </h6>
      <div class="d-flex flex-column gap-2">
        <a href="{{ route('anggota.create') }}" class="quick-link-btn blue">
          <i class="bi bi-person-plus-fill"></i> Tambah Anggota
        </a>
        <a href="{{ route('simpanan.create') }}" class="quick-link-btn green">
          <i class="bi bi-plus-circle-fill"></i> Catat Simpanan
        </a>
        <a href="{{ route('pinjaman.create') }}" class="quick-link-btn amber">
          <i class="bi bi-cash-stack"></i> Ajukan Pinjaman
        </a>
        <a href="{{ route('laporan.keuangan') }}" class="quick-link-btn purple">
          <i class="bi bi-file-earmark-bar-graph-fill"></i> Laporan Keuangan
        </a>
      </div>
    </div>
  </div>
</div>

{{-- Tables Row --}}
<div class="row g-4">
  {{-- Pinjaman Terbaru --}}
  <div class="col-xl-8">
    <div class="form-card p-0 overflow-hidden">
      <div class="p-4 d-flex align-items-center justify-content-between border-bottom">
        <h6 class="mb-0 fw-700 is-5ca88772">
          <i class="bi bi-cash-coin me-2 is-bc519fd0"></i>Pinjaman Terbaru
        </h6>
        <a href="{{ route('pinjaman.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
      </div>
      <div class="table-responsive">
        <table class="koperasi-table mb-0">
          <thead>
            <tr>
              <th>No. Pinjaman</th>
              <th>Anggota</th>
              <th>Jumlah</th>
              <th>Status</th>
              <th>Tanggal</th>
            </tr>
          </thead>
          <tbody>
            @forelse($pinjamanTerbaru as $pinjaman)
              <tr>
                <td><code>{{ $pinjaman->nomor_pinjaman }}</code></td>
                <td>{{ $pinjaman->anggota->nama ?? 'n/a' }}</td>
                <td>Rp {{ number_format($pinjaman->jumlah_pinjaman, 0, ',', '.') }}</td>
                <td>
                  @php $badgeMap = ['diajukan'=>'badge-diajukan','disetujui'=>'badge-disetujui','ditolak'=>'badge-ditolak','lunas'=>'badge-lunas']; @endphp
                  <span class="badge-status {{ $badgeMap[$pinjaman->status] ?? '' }}">{{ ucfirst($pinjaman->status) }}</span>
                </td>
                <td>{{ $pinjaman->tanggal_pengajuan->format('d M Y') }}</td>
              </tr>
            @empty
              <tr><td colspan="5" class="text-center text-muted py-4">Belum ada pinjaman</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- Angsuran Mendatang --}}
  <div class="col-xl-4">
    <div class="form-card p-0 overflow-hidden">
      <div class="p-4 d-flex align-items-center justify-content-between border-bottom">
        <h6 class="mb-0 fw-700 is-5ca88772">
          <i class="bi bi-clock-history me-2 is-bc519fd0"></i>Angsuran Mendatang
        </h6>
        <a href="{{ route('angsuran.index') }}" class="btn btn-sm btn-outline-primary">Semua</a>
      </div>
      <div class="p-3 d-flex flex-column gap-3">
        @forelse($angsuranMendatang as $angsuran)
          <div class="d-flex align-items-center gap-3 p-3 rounded-3 is-e0b82637">
            <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0 is-21a90fac"
             >
              <i class="bi bi-{{ $angsuran->status === 'sudah' ? 'check-circle-fill' : 'clock' }}"
                class="{{ $angsuran->status === 'sudah' ? 'text-success' : 'is-color-accent' }}"></i>
            </div>
            <div class="flex-grow-1 min-w-0">
              <div class="text-truncate is-a90fa8ce">
                {{ $angsuran->pinjaman->anggota->nama ?? 'n/a' }}
              </div>
              <div class="is-84ebbd67">
                Ke-{{ $angsuran->ke }} &middot; {{ $angsuran->tanggal_jatuh_tempo->format('d M') }}
              </div>
            </div>
            <div class="is-10c2b176">
              Rp&nbsp;{{ number_format($angsuran->jumlah_angsuran, 0, ',', '.') }}
            </div>
          </div>
        @empty
          <p class="text-center text-muted py-3 mb-0">Tidak ada angsuran mendatang</p>
        @endforelse
      </div>
    </div>
  </div>
</div>

@endsection
