@extends('layouts.aplikasi')

@section('judul', 'Data Angsuran')

@section('konten')

{{-- Page Banner --}}
<div class="dashboard-welcome mb-4">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
      <div class="welcome-label mb-1">Manajemen Data</div>
      <h4 class="mb-1 fw-700">Data Angsuran</h4>
      <p class="mb-0 welcome-date">
        <i class="bi bi-calendar3 me-1"></i>
        {{ now()->translatedFormat('l, d F Y') }}
      </p>
    </div>
    <div class="welcome-icon-wrap">
      <i class="bi bi-receipt-cutoff"></i>
    </div>
  </div>
</div>

{{-- Stat Cards --}}
<div class="row g-4 mb-4">
  <div class="col-xl-3 col-sm-6">
    <div class="kstat-card kstat-blue">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-receipt-cutoff"></i></div>
      <div class="kstat-value">{{ number_format($statsAngsuran['total']) }}</div>
      <div class="kstat-label">Total Cicilan</div>
      <div class="kstat-trend"><i class="bi bi-list-check me-1"></i> Semua periode</div>
    </div>
  </div>
  <div class="col-xl-3 col-sm-6">
    <div class="kstat-card kstat-green">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-check-circle-fill"></i></div>
      <div class="kstat-value">{{ number_format($statsAngsuran['sudah']) }}</div>
      <div class="kstat-label">Sudah Lunas</div>
      <div class="kstat-trend"><i class="bi bi-patch-check-fill me-1"></i> Terbayar</div>
    </div>
  </div>
  <div class="col-xl-3 col-sm-6">
    <div class="kstat-card kstat-amber">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-clock-fill"></i></div>
      <div class="kstat-value">{{ number_format($statsAngsuran['belum']) }}</div>
      <div class="kstat-label">Belum Lunas</div>
      <div class="kstat-trend"><i class="bi bi-hourglass-split me-1"></i> Menunggu pembayaran</div>
    </div>
  </div>
  <div class="col-xl-3 col-sm-6">
    <div class="kstat-card kstat-red">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-alarm-fill"></i></div>
      <div class="kstat-value">{{ number_format($statsAngsuran['terlambat']) }}</div>
      <div class="kstat-label">Terlambat</div>
      <div class="kstat-trend">
        @if($statsAngsuran['terlambat'] > 0)
          <i class="bi bi-exclamation-triangle-fill me-1"></i> Melewati jatuh tempo
        @else
          <i class="bi bi-check-circle-fill me-1"></i> Tidak ada keterlambatan
        @endif
      </div>
    </div>
  </div>
</div>

<!-- Filter -->
<div class="form-card mb-4">
  <form method="GET" action="{{ route('angsuran.index') }}" class="row g-3 align-items-end">
    <div class="col-md-4">
      <label class="form-label">Cari Anggota</label>
      <div class="input-group">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input type="text" name="cari" class="form-control" placeholder="Nama anggota atau nomor pinjaman..."
          value="{{ request('cari') }}">
      </div>
    </div>
    <div class="col-md-3">
      <label class="form-label">Status</label>
      <select name="status" class="form-select">
        <option value="">Semua Status</option>
        <option value="belum" {{ request('status') === 'belum' ? 'selected' : '' }}>Belum Lunas</option>
        <option value="sudah" {{ request('status') === 'sudah' ? 'selected' : '' }}>Sudah Lunas</option>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">Jatuh Tempo</label>
      <select name="jatuh_tempo" class="form-select">
        <option value="">Semua</option>
        <option value="hari_ini" {{ request('jatuh_tempo') === 'hari_ini' ? 'selected' : '' }}>Hari Ini</option>
        <option value="bulan_ini" {{ request('jatuh_tempo') === 'bulan_ini' ? 'selected' : '' }}>Bulan Ini</option>
        <option value="terlambat" {{ request('jatuh_tempo') === 'terlambat' ? 'selected' : '' }}>Terlambat</option>
      </select>
    </div>
    <div class="col-md-2 d-flex gap-2">
      <button type="submit" class="btn btn-primary flex-fill"><i class="bi bi-funnel me-1"></i> Filter</button>
      <a href="{{ route('export.angsuran', request()->query()) }}" class="btn btn-outline-secondary" title="Export CSV">
        <i class="bi bi-file-earmark-spreadsheet"></i>
      </a>
    </div>
  </form>
</div>

<!-- Table -->
<div class="form-card p-0 overflow-hidden">
  <div class="p-4 border-bottom d-flex align-items-center justify-content-between">
    <h6 class="mb-0 fw-700" style="color:#112344">
      <i class="bi bi-list-check me-2" style="color:#175cdd"></i>Daftar Angsuran
      <span class="badge bg-primary ms-2">{{ $angsuran->total() }}</span>
    </h6>
    @if(request()->hasAny(['cari','status','jatuh_tempo']))
      <a href="{{ route('angsuran.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-x-circle me-1"></i> Reset
      </a>
    @endif
  </div>
  <div class="table-responsive">
    <table class="koperasi-table mb-0">
      <thead>
        <tr>
          <th>#</th>
          <th>No. Pinjaman</th>
          <th>Anggota</th>
          <th>Ke</th>
          <th>Total</th>
          <th>Pokok</th>
          <th>Bunga</th>
          <th>Denda</th>
          <th>Jatuh Tempo</th>
          <th>Tgl Bayar</th>
          <th>Status</th>
          <th class="text-center">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($angsuran as $item)
          @php
            $terlambat = $item->status === 'belum' && $item->tanggal_jatuh_tempo < now();
          @endphp
          <tr class="{{ $item->status === 'sudah' ? 'opacity-75' : ($terlambat ? '' : '') }}">
            <td class="text-muted">{{ $angsuran->firstItem() + $loop->index }}</td>
            <td><code style="font-size:.8rem">{{ $item->pinjaman->nomor_pinjaman ?? '—' }}</code></td>
            <td>
              <div style="font-weight:600;font-size:.88rem;color:#112344">
                {{ $item->pinjaman->anggota->nama ?? '—' }}
              </div>
            </td>
            <td class="text-center"><strong>{{ $item->ke }}</strong></td>
            <td style="font-weight:700;color:#112344">Rp {{ number_format($item->jumlah_angsuran, 0, ',', '.') }}</td>
            <td style="font-size:.85rem">Rp {{ number_format($item->pokok, 0, ',', '.') }}</td>
            <td style="font-size:.85rem;color:#f59e0b">Rp {{ number_format($item->bunga, 0, ',', '.') }}</td>
            <td style="font-size:.85rem">
              @if($item->status === 'sudah' && $item->denda > 0)
                <span style="color:#ef4444;font-weight:600">Rp {{ number_format($item->denda, 0, ',', '.') }}</span>
                <div><small class="text-muted">{{ $item->hari_terlambat }} hari</small></div>
              @elseif($terlambat)
                @php $estDenda = $item->hitungDenda() @endphp
                @if($estDenda > 0)
                  <span style="color:#f59e0b;font-size:.8rem">~Rp {{ number_format($estDenda, 0, ',', '.') }}</span>
                @else
                  <span class="text-muted">—</span>
                @endif
              @else
                <span class="text-muted">—</span>
              @endif
            </td>
            <td>
              <span class="{{ $terlambat ? 'text-danger fw-600' : '' }}">
                {{ $item->tanggal_jatuh_tempo->format('d M Y') }}
              </span>
              @if($terlambat)
                <div><small class="text-danger"><i class="bi bi-exclamation-circle me-1"></i>Terlambat</small></div>
              @endif
            </td>
            <td>
              @if($item->tanggal_bayar)
                {{ $item->tanggal_bayar->format('d M Y') }}
              @else
                <span class="text-muted">—</span>
              @endif
            </td>
            <td>
              <span class="badge-status {{ $item->status === 'sudah' ? 'badge-sudah' : 'badge-belum' }}">
                {{ $item->status === 'sudah' ? 'Lunas' : 'Belum' }}
              </span>
            </td>
            <td>
              <div class="d-flex gap-1 justify-content-center">
                <a href="{{ route('angsuran.show', $item) }}" class="btn btn-sm btn-outline-info" title="Detail">
                  <i class="bi bi-eye"></i>
                </a>
                @if($item->status === 'belum')
                  @can('angsuran.bayar')
                  <form action="{{ route('angsuran.bayar', $item) }}" method="POST"
                    onsubmit="return confirm('Tandai angsuran ke-{{ $item->ke }} untuk {{ $item->pinjaman->anggota->nama ?? '' }} sebagai lunas?')">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-success" title="Bayar">
                      <i class="bi bi-check2"></i>
                    </button>
                  </form>
                  @endcan
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr>
              <td colspan="12" class="text-center py-5 text-muted">
              <i class="bi bi-calendar-x display-4 d-block mb-2 opacity-25"></i>
              Tidak ada angsuran ditemukan
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($angsuran->hasPages())
    <div class="p-4 border-top">{{ $angsuran->withQueryString()->links() }}</div>
  @endif
</div>
@endsection
