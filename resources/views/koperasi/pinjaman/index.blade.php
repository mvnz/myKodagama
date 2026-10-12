@extends('layouts.aplikasi')

@section('judul', 'Data Pinjaman')

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
      <h4 class="mb-1 fw-700">Data Pinjaman</h4>
      <p class="mb-0 welcome-date">
        <i class="bi bi-calendar3 me-1"></i>
        {{ now()->translatedFormat('l, d F Y') }}
      </p>
    </div>
    <div class="welcome-icon-wrap">
      <i class="bi bi-cash-coin"></i>
    </div>
  </div>
</div>

{{-- Stat Cards --}}
<div class="row g-4 mb-4">
  <div class="col-xl-3 col-sm-6">
    <div class="kstat-card kstat-blue">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-cash-coin"></i></div>
      <div class="kstat-value kstat-value-sm">Rp {{ number_format($statsPinjaman['nilai_aktif'], 0, ',', '.') }}</div>
      <div class="kstat-label">Nilai Pinjaman Berjalan</div>
      <div class="kstat-trend"><i class="bi bi-arrow-up-circle-fill me-1"></i> {{ $statsPinjaman['disetujui'] }} pinjaman aktif</div>
    </div>
  </div>
  <div class="col-xl-3 col-sm-6">
    <div class="kstat-card kstat-amber">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-hourglass-split"></i></div>
      <div class="kstat-value">{{ number_format($statsPinjaman['diajukan']) }}</div>
      <div class="kstat-label">Pengajuan Menunggu</div>
      <div class="kstat-trend">
        @if($statsPinjaman['diajukan'] > 0)
          <i class="bi bi-exclamation-circle-fill me-1"></i> Perlu ditinjau
        @else
          <i class="bi bi-check-circle-fill me-1"></i> Semua diproses
        @endif
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-sm-6">
    <div class="kstat-card kstat-green">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-check-circle-fill"></i></div>
      <div class="kstat-value">{{ number_format($statsPinjaman['lunas']) }}</div>
      <div class="kstat-label">Pinjaman Lunas</div>
      <div class="kstat-trend"><i class="bi bi-patch-check-fill me-1"></i> Selesai</div>
    </div>
  </div>
  <div class="col-xl-3 col-sm-6">
    <div class="kstat-card kstat-red">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-shield-fill-check"></i></div>
      <div class="kstat-value">{{ number_format($statsPinjaman['disetujui']) }}</div>
      <div class="kstat-label">Pinjaman Disetujui</div>
      <div class="kstat-trend"><i class="bi bi-arrow-right-circle-fill me-1"></i> Sedang berjalan</div>
    </div>
  </div>
</div>

<!-- Filter & Toolbar -->
<div class="form-card mb-4">
  <form method="GET" action="{{ route('pinjaman.index') }}" class="row g-3 align-items-end">
    @can('anggota.lihat')
    <div class="col">
      <label class="form-label">Cari</label>
      <div class="input-group">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input type="text" name="cari" class="form-control" placeholder="Nama anggota atau nomor..."
          value="{{ request('cari') }}">
      </div>
    </div>
    @endcan
    <div class="col-md-3">
      <label class="form-label">Status</label>
      <select name="status" class="form-select">
        <option value="">Semua Status</option>
        @foreach(['diajukan','disetujui','ditolak','lunas'] as $st)
          <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-md-auto d-flex gap-2 align-items-end">
      <button type="submit" class="btn btn-primary"><i class="bi bi-funnel me-1"></i> Filter</button>
      @can('pinjaman.tambah')
      <a href="{{ route('pinjaman.create') }}" class="btn btn-success">
        <i class="bi bi-plus me-1"></i> Tambah
      </a>
      @if((auth()->user()->roleModel?->slug ?? auth()->user()->role) !== 'anggota')
      <a href="{{ route('import.pinjaman.form') }}" class="btn btn-warning">
        <i class="bi bi-box-arrow-in-down me-1"></i> Import
      </a>
      @endif
      @endcan
      <a href="{{ route('pinjaman.cetak', array_filter(request()->only(['cari','status']))) }}" class="btn btn-outline-secondary" target="_blank">
        <i class="bi bi-printer me-1"></i> Cetak
      </a>
    </div>
  </form>
</div>

<!-- Table -->
<div class="form-card p-0 overflow-hidden">
  <div class="p-4 border-bottom d-flex align-items-center justify-content-between">
    <h6 class="mb-0 fw-700 is-5ca88772">
      <i class="bi bi-cash-coin me-2 is-bc519fd0"></i>Daftar Pinjaman
      <span class="badge bg-primary ms-2">{{ $pinjaman->total() }}</span>
    </h6>
    @if(request('cari') || request('status'))
      <a href="{{ route('pinjaman.index') }}" class="btn btn-sm btn-outline-secondary">
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
          <th><a href="{{ $sUrl('nomor_pinjaman') }}" class="text-decoration-none text-reset d-inline-flex align-items-center">No. Pinjaman {!! $sIco('nomor_pinjaman') !!}</a></th>
          <th>Anggota</th>
          <th><a href="{{ $sUrl('jumlah_pinjaman') }}" class="text-decoration-none text-reset d-inline-flex align-items-center">Jumlah {!! $sIco('jumlah_pinjaman') !!}</a></th>
          <th><a href="{{ $sUrl('jangka_waktu') }}" class="text-decoration-none text-reset d-inline-flex align-items-center">Jangka {!! $sIco('jangka_waktu') !!}</a></th>
          <th>Cicilan/Bln</th>
          <th><a href="{{ $sUrl('status') }}" class="text-decoration-none text-reset d-inline-flex align-items-center">Status {!! $sIco('status') !!}</a></th>
          <th><a href="{{ $sUrl('tanggal_pengajuan') }}" class="text-decoration-none text-reset d-inline-flex align-items-center">Tanggal {!! $sIco('tanggal_pengajuan') !!}</a></th>
          <th class="text-center">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($pinjaman as $item)
          <tr>
            <td class="text-muted">{{ $pinjaman->firstItem() + $loop->index }}</td>
            <td><code>{{ $item->nomor_pinjaman }}</code>
              @if($item->jenis_pinjaman)
                <div><span class="is-11d6e5bb">
                  <i class="bi bi-tag-fill me-1"></i>{{ $item->jenis_pinjaman }}
                </span></div>
              @endif
            </td>
            <td>
              <div class="is-75bba466">{{ $item->anggota->nama ?? '—' }}</div>
              <div class="is-b9edd143"><code>{{ $item->anggota->nomor_anggota ?? '' }}</code></div>
            </td>
            <td class="is-1d4db756">Rp {{ number_format($item->jumlah_pinjaman, 0, ',', '.') }}</td>
            <td>{{ $item->jangka_waktu }} bln</td>
            <td class="is-e91f2cae">
              @if($item->cicilan_per_bulan)
                Rp {{ number_format($item->cicilan_per_bulan, 0, ',', '.') }}
              @else —
              @endif
            </td>
            <td>
              @php $badgeMap = ['diajukan'=>'badge-diajukan','diverifikasi'=>'badge-diverifikasi','disetujui'=>'badge-disetujui','ditolak'=>'badge-ditolak','lunas'=>'badge-lunas']; @endphp
              <span class="badge-status {{ $badgeMap[$item->status] ?? '' }}">{{ ucfirst($item->status) }}</span>
            </td>
            <td>
              <span class="is-7e7d71c3">
                <i class="bi bi-calendar3 is-3b8e2be5"></i>
                {{ $item->tanggal_pengajuan->format('d M Y') }}
              </span>
            </td>
            <td>
              <div class="d-flex gap-1 justify-content-center">
                <a href="{{ route('pinjaman.show', $item) }}" class="btn btn-sm btn-outline-info" title="Detail">
                  <i class="bi bi-eye"></i>
                </a>
                @can('pinjaman.edit')
                @if(!in_array($item->status, ['disetujui', 'lunas']))
                <a href="{{ route('pinjaman.edit', $item) }}" class="btn btn-sm btn-outline-warning" title="Ubah">
                  <i class="bi bi-pencil"></i>
                </a>
                @endif
                @endcan
                @can('pinjaman.hapus')
                <form action="{{ route('pinjaman.destroy', $item) }}" method="POST" class="d-inline"
                  onsubmit="return confirm('Hapus data pinjaman ini?')">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                </form>
                @endcan
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="9" class="text-center py-5 text-muted">
              <i class="bi bi-cash-coin display-4 d-block mb-2 opacity-25"></i>
              Tidak ada pinjaman ditemukan
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($pinjaman->hasPages())
    <div class="p-4 border-top">{{ $pinjaman->withQueryString()->links() }}</div>
  @endif
</div>

{{-- Modal: pinjaman masih aktif --}}
@if(session('pinjaman_aktif'))
<div class="modal fade" id="modalPinjamanAktif" tabindex="-1" aria-labelledby="modalPinjamanAktifLabel" aria-modal="true" role="dialog">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-700 is-5ca88772" id="modalPinjamanAktifLabel">
          <i class="bi bi-exclamation-circle-fill me-2 is-2a4cda41"></i>Tidak Dapat Mengajukan
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body pt-2">
        <p class="mb-0 is-6d9a1d17">
          Anda masih memiliki pinjaman yang <strong>sedang berjalan</strong>.
          Selesaikan pinjaman sebelumnya terlebih dahulu sebelum mengajukan pinjaman baru.
        </p>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Mengerti</button>
      </div>
    </div>
  </div>
</div>
@endif
@endsection

@push('js')
<script src="{{ asset('js/pages/pinjaman-index.js') }}"></script>
@endpush
