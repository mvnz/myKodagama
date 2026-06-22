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
      <div class="d-flex align-items-center gap-2 p-3 border-bottom is-f4e2dab1"
       >
        <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0 is-db3490b1"
         >
          <i class="bi bi-clock-history"></i>
        </div>
        <div class="flex-grow-1">
          <h6 class="mb-0 fw-700 is-7536573a">Menunggu Verifikasi</h6>
          <small class="text-muted">{{ $menungguVerifikasi->count() }} pengajuan baru</small>
        </div>
        <a href="{{ route('pinjaman.index') }}?status=diajukan" class="btn btn-sm btn-outline-primary is-640fb1f6">Semua</a>
      </div>

      @if($menungguVerifikasi->isEmpty())
        <div class="text-center py-4 text-muted">
          <i class="bi bi-check2-all d-block mb-1 is-30e48a87"></i>
          <div class="is-9ee0bb07">Tidak ada pengajuan menunggu</div>
        </div>
      @else
        @foreach($menungguVerifikasi->take(5) as $p)
        <div class="d-flex align-items-center gap-3 px-3 py-2 border-bottom">
          <div class="flex-grow-1 min-w-0">
            <div class="fw-600 is-1e28117f">{{ $p->anggota->nama ?? '—' }}</div>
            <div class="is-22508fb3">
              <code class="is-2ae29e8a">{{ $p->nomor_pinjaman }}</code>
              · {{ $p->tanggal_pengajuan->format('d M Y') }}
            </div>
          </div>
          <div class="text-end flex-shrink-0">
            <div class="is-2dee3f53">Rp {{ number_format($p->jumlah_pinjaman, 0, ',', '.') }}</div>
            <a href="{{ route('pinjaman.show', $p) }}" class="btn btn-xs btn-primary mt-1">
              <i class="bi bi-check-lg me-1"></i>Verifikasi
            </a>
          </div>
        </div>
        @endforeach
        @if($menungguVerifikasi->count() > 5)
          <div class="text-center py-2">
            <a href="{{ route('pinjaman.index') }}?status=diajukan" class="is-e4095274">
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
      <div class="d-flex align-items-center gap-2 p-3 border-bottom is-5844492e"
       >
        <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0 is-55b67465"
         >
          <i class="bi bi-hourglass-split"></i>
        </div>
        <div class="flex-grow-1">
          <h6 class="mb-0 fw-700 is-9a2b98e5">Menunggu Persetujuan</h6>
          <small class="text-muted">{{ $menungguPersetujuan->count() }} sudah diverifikasi</small>
        </div>
        <a href="{{ route('pinjaman.index') }}?status=diverifikasi" class="btn btn-sm btn-outline-warning is-640fb1f6">Semua</a>
      </div>

      @if($menungguPersetujuan->isEmpty())
        <div class="text-center py-4 text-muted">
          <i class="bi bi-check2-all d-block mb-1 is-30e48a87"></i>
          <div class="is-9ee0bb07">Tidak ada yang menunggu persetujuan</div>
        </div>
      @else
        @foreach($menungguPersetujuan->take(5) as $p)
        <div class="d-flex align-items-center gap-3 px-3 py-2 border-bottom">
          <div class="flex-grow-1 min-w-0">
            <div class="fw-600 is-1e28117f">{{ $p->anggota->nama ?? '—' }}</div>
            <div class="is-22508fb3">
              <code class="is-6497f4df">{{ $p->nomor_pinjaman }}</code>
              · {{ $p->tanggal_pengajuan->format('d M Y') }}
            </div>
          </div>
          <div class="text-end flex-shrink-0">
            <div class="is-2dee3f53">Rp {{ number_format($p->jumlah_pinjaman, 0, ',', '.') }}</div>
            <a href="{{ route('pinjaman.show', $p) }}" class="btn btn-xs btn-warning text-white mt-1">
              <i class="bi bi-check-circle me-1"></i>Setujui
            </a>
          </div>
        </div>
        @endforeach
        @if($menungguPersetujuan->count() > 5)
          <div class="text-center py-2">
            <a href="{{ route('pinjaman.index') }}?status=diverifikasi" class="is-801e5fc8">
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
      <div class="d-flex align-items-center gap-2 p-3 border-bottom is-7c743ce2"
       >
        <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0 is-d371cb9a"
         >
          <i class="bi bi-alarm-fill"></i>
        </div>
        <div class="flex-grow-1">
          <h6 class="mb-0 fw-700 is-217480ed">Angsuran Terlambat</h6>
          <small class="text-muted">{{ $angsuranTelat->count() }} tagihan belum dibayar</small>
        </div>
        <a href="{{ route('monitoring.pinjaman') }}" class="btn btn-sm btn-outline-danger is-640fb1f6">Monitoring</a>
      </div>

      @if($angsuranTelat->isEmpty())
        <div class="text-center py-4 text-muted">
          <i class="bi bi-check2-circle d-block mb-1 is-185155ba"></i>
          <div class="is-9ee0bb07">Tidak ada angsuran terlambat</div>
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
              <tr class="is-ec53da86">
                <td>
                  <div class="fw-600 is-1e28117f">{{ $ang->pinjaman->anggota->nama ?? '—' }}</div>
                </td>
                <td><code class="is-bb0425a5">{{ $ang->pinjaman->nomor_pinjaman ?? '—' }}</code></td>
                <td class="text-center">{{ $ang->ke }}</td>
                <td class="is-31a37b93">
                  {{ $ang->tanggal_jatuh_tempo->format('d M Y') }}
                  <div class="is-cc5f54d7">{{ $ang->tanggal_jatuh_tempo->diffForHumans() }}</div>
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
      <h6 class="fw-700 mb-3 is-5ca88772">
        <i class="bi bi-shield-check me-2 is-bc519fd0"></i>Hak Akses Anda
      </h6>
      <div class="d-flex flex-column gap-1">
        @forelse($permissions as $perm)
          <div class="d-flex align-items-center gap-2 py-1 is-0de4e77b">
            <i class="bi bi-check-circle-fill flex-shrink-0 is-25ff9a8a"></i>
            <span class="is-6d9a1d17">{{ $perm->nama }}</span>
          </div>
        @empty
          <div class="text-muted is-9ee0bb07">Tidak ada hak akses khusus.</div>
        @endforelse
      </div>
    </div>
  </div>

</div>

@endsection
