@extends('layouts.aplikasi')

@section('judul', 'Data Angsuran')

@section('konten')

@php
  $sortBy = $sortBy ?? request('sort', 'created_at');
  $sortDir = $sortDir ?? request('dir', 'desc');
@endphp

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
    <div class="col">
      <label class="form-label">Cari Anggota</label>
      <div class="input-group">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input type="text" name="cari" class="form-control" placeholder="Nama anggota atau nomor pinjaman..."
          value="{{ request('cari') }}">
      </div>
    </div>
    <div class="col-md-2">
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
    <div class="col-md-auto d-flex gap-2 align-items-end">
      <button type="submit" class="btn btn-primary"><i class="bi bi-funnel me-1"></i> Filter</button>
      @can('angsuran.bayar')
      @if((auth()->user()->roleModel?->slug ?? auth()->user()->role) !== 'anggota')
      <a href="{{ route('import.angsuran.form') }}" class="btn btn-warning">
        <i class="bi bi-box-arrow-in-down me-1"></i> Import
      </a>
      @endif
      @endcan
      <a href="{{ route('angsuran.cetak', array_filter(request()->only(['cari','status','jatuh_tempo']))) }}" class="btn btn-outline-secondary" target="_blank">
        <i class="bi bi-printer me-1"></i> Cetak
      </a>
    </div>
  </form>
</div>

<!-- Table -->
<div class="form-card p-0 overflow-hidden">
  <div class="p-4 border-bottom d-flex align-items-center justify-content-between">
    <h6 class="mb-0 fw-700 is-5ca88772">
      <i class="bi bi-list-check me-2 is-bc519fd0"></i>Daftar Angsuran
      <span class="badge bg-primary ms-2">{{ $angsuran->total() }}</span>
    </h6>
    @if(request()->hasAny(['cari','status','jatuh_tempo']))
      <a href="{{ route('angsuran.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-x-circle me-1"></i> Reset
      </a>
    @endif
  </div>
  <div class="table-responsive">
    @php
      $sUrl = fn($k) => request()->fullUrlWithQuery(['sort' => $k, 'dir' => ($sortBy===$k && $sortDir==='asc') ? 'desc' : 'asc']);
      $sIco = fn($k) => $sortBy===$k
        ? ($sortDir==='asc'
            ? '<i class="bi bi-sort-up ms-1 is-0a789cad"></i>'
            : '<i class="bi bi-sort-down ms-1 is-0a789cad"></i>')
        : '<i class="bi bi-arrow-down-up text-muted opacity-50 ms-1 is-bc9efe64"></i>';
    @endphp
    <table class="koperasi-table mb-0">
      <thead>
        <tr>
          <th>#</th>
          <th>No. Pinjaman</th>
          <th>Anggota</th>
          <th><a href="{{ $sUrl('ke') }}" class="text-decoration-none text-reset d-inline-flex align-items-center">Ke {!! $sIco('ke') !!}</a></th>
          <th>Total</th>
          <th>Pokok</th>
          <th>Bunga</th>
          <th>Denda</th>
          <th><a href="{{ $sUrl('tanggal_jatuh_tempo') }}" class="text-decoration-none text-reset d-inline-flex align-items-center">Jatuh Tempo {!! $sIco('tanggal_jatuh_tempo') !!}</a></th>
          <th><a href="{{ $sUrl('tanggal_bayar') }}" class="text-decoration-none text-reset d-inline-flex align-items-center">Tgl Bayar {!! $sIco('tanggal_bayar') !!}</a></th>
          <th><a href="{{ $sUrl('status') }}" class="text-decoration-none text-reset d-inline-flex align-items-center">Status {!! $sIco('status') !!}</a></th>
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
            <td><code class="is-6c660c40">{{ $item->pinjaman->nomor_pinjaman ?? '—' }}</code></td>
            <td>
              <div class="is-a76dd80f">
                {{ $item->pinjaman->anggota->nama ?? '—' }}
              </div>
            </td>
            <td class="text-center"><strong>{{ $item->ke }}</strong></td>
            <td class="is-1d4db756">Rp {{ number_format($item->jumlah_angsuran, 0, ',', '.') }}</td>
            <td class="is-1e28117f">Rp {{ number_format($item->pokok, 0, ',', '.') }}</td>
            <td class="is-82665b19">Rp {{ number_format($item->bunga, 0, ',', '.') }}</td>
            <td class="is-1e28117f">
              @if($item->status === 'sudah' && $item->denda > 0)
                <span class="is-2ebc546d">Rp {{ number_format($item->denda, 0, ',', '.') }}</span>
                <div><small class="text-muted">{{ $item->hari_terlambat }} hari</small></div>
              @elseif($terlambat)
                @php $estDenda = $item->hitungDenda() @endphp
                @if($estDenda > 0)
                  <span class="is-988b6ff5">~Rp {{ number_format($estDenda, 0, ',', '.') }}</span>
                @else
                  <span class="text-muted">—</span>
                @endif
              @else
                <span class="text-muted">—</span>
              @endif
            </td>
            <td>
              <span class="{{ $terlambat ? 'text-danger fw-600' : '' }} is-2dbe781b">
                <i class="bi bi-calendar3 is-5562148d"></i>
                {{ $item->tanggal_jatuh_tempo->format('d M Y') }}
              </span>
              @if($terlambat)
                <div><small class="text-danger"><i class="bi bi-exclamation-circle me-1"></i>Terlambat</small></div>
              @endif
            </td>
            <td>
              @if($item->tanggal_bayar)
                <span class="is-7e7d71c3">
                  <i class="bi bi-calendar3 is-3b8e2be5"></i>
                  {{ $item->tanggal_bayar->format('d M Y') }}
                </span>
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
