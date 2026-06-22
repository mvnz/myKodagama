@extends('layouts.aplikasi')

@section('judul', 'Data Simpanan')

@section('konten')

{{-- Page Banner --}}
<div class="dashboard-welcome mb-4">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
      <div class="welcome-label mb-1">Manajemen Data</div>
      <h4 class="mb-1 fw-700">Data Simpanan</h4>
      <p class="mb-0 welcome-date">
        <i class="bi bi-calendar3 me-1"></i>
        {{ now()->translatedFormat('l, d F Y') }}
      </p>
    </div>
    <div class="welcome-icon-wrap">
      <i class="bi bi-piggy-bank-fill"></i>
    </div>
  </div>
</div>

{{-- Stat Cards --}}
<div class="row g-4 mb-4">
  <div class="col-xl-3 col-sm-6">
    <div class="kstat-card kstat-blue">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-piggy-bank-fill"></i></div>
      <div class="kstat-value kstat-value-sm">Rp {{ number_format($totalPerJenis['pokok'] ?? 0, 0, ',', '.') }}</div>
      <div class="kstat-label">Simpanan Pokok</div>
      <div class="kstat-trend"><i class="bi bi-check-circle-fill me-1"></i> Kewajiban awal</div>
    </div>
  </div>
  <div class="col-xl-3 col-sm-6">
    <div class="kstat-card kstat-green">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-calendar-check-fill"></i></div>
      <div class="kstat-value kstat-value-sm">Rp {{ number_format($totalPerJenis['wajib'] ?? 0, 0, ',', '.') }}</div>
      <div class="kstat-label">Simpanan Wajib</div>
      <div class="kstat-trend"><i class="bi bi-arrow-up-circle-fill me-1"></i> Rutin bulanan</div>
    </div>
  </div>
  <div class="col-xl-3 col-sm-6">
    <div class="kstat-card kstat-amber">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-wallet2"></i></div>
      <div class="kstat-value kstat-value-sm">Rp {{ number_format($totalPerJenis['sukarela'] ?? 0, 0, ',', '.') }}</div>
      <div class="kstat-label">Simpanan Sukarela</div>
      <div class="kstat-trend"><i class="bi bi-stars me-1"></i> Bebas</div>
    </div>
  </div>
  <div class="col-xl-3 col-sm-6">
    <div class="kstat-card kstat-red">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-cash-stack"></i></div>
      <div class="kstat-value kstat-value-sm">Rp {{ number_format($totalSimpanan, 0, ',', '.') }}</div>
      <div class="kstat-label">Total Simpanan</div>
      <div class="kstat-trend"><i class="bi bi-receipt me-1"></i> {{ number_format($transaksiBuilanIni) }} transaksi bulan ini</div>
    </div>
  </div>
</div>

<!-- Filter & Toolbar -->
<div class="form-card mb-4">
  <form method="GET" action="{{ route('simpanan.index') }}" class="row g-3 align-items-end">
    @can('anggota.lihat')
    <div class="col">
      <label class="form-label">Cari Anggota</label>
      <div class="input-group">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input type="text" name="cari" class="form-control" placeholder="Nama atau nomor anggota..."
          value="{{ request('cari') }}">
      </div>
    </div>
    @endcan
    <div class="col-md-3">
      <label class="form-label">Jenis Simpanan</label>
      <select name="jenis" class="form-select">
        <option value="">Semua Jenis</option>
        <option value="pokok" {{ request('jenis') === 'pokok' ? 'selected' : '' }}>Pokok</option>
        <option value="wajib" {{ request('jenis') === 'wajib' ? 'selected' : '' }}>Wajib</option>
        <option value="sukarela" {{ request('jenis') === 'sukarela' ? 'selected' : '' }}>Sukarela</option>
      </select>
    </div>
    <div class="col-md-auto d-flex gap-2 align-items-end">
      <button type="submit" class="btn btn-primary">
        <i class="bi bi-funnel me-1"></i> Filter
      </button>
      @can('simpanan.tambah')
      <a href="{{ route('simpanan.create') }}" class="btn btn-success">
        <i class="bi bi-plus me-1"></i> Tambah
      </a>
      <a href="{{ route('import.simpanan.form') }}" class="btn btn-warning">
        <i class="bi bi-box-arrow-in-down me-1"></i> Import
      </a>
      @endcan
      <a href="{{ route('simpanan.cetak', array_filter(request()->only(['cari','jenis']))) }}" class="btn btn-outline-secondary" target="_blank">
        <i class="bi bi-printer me-1"></i> Cetak
      </a>
    </div>
  </form>
  </form>
</div>

<!-- Table -->
<div class="form-card p-0 overflow-hidden">
  <div class="p-4 border-bottom d-flex align-items-center justify-content-between">
    <h6 class="mb-0 fw-700 is-5ca88772">
      <i class="bi bi-piggy-bank me-2 is-bc519fd0"></i>Daftar Simpanan
      <span class="badge bg-primary ms-2">{{ $simpanan->total() }}</span>
    </h6>
    @if(request('cari') || request('jenis'))
      <a href="{{ route('simpanan.index') }}" class="btn btn-sm btn-outline-secondary">
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
          <th>Anggota</th>
          <th><a href="{{ $sUrl('jenis_simpanan') }}" class="text-decoration-none text-reset d-inline-flex align-items-center">Jenis {!! $sIco('jenis_simpanan') !!}</a></th>
          <th><a href="{{ $sUrl('jumlah') }}" class="text-decoration-none text-reset d-inline-flex align-items-center">Jumlah {!! $sIco('jumlah') !!}</a></th>
          <th><a href="{{ $sUrl('tanggal') }}" class="text-decoration-none text-reset d-inline-flex align-items-center">Tanggal {!! $sIco('tanggal') !!}</a></th>
          <th>Keterangan</th>
          <th>Petugas</th>
          <th class="text-center">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($simpanan as $item)
          <tr>
            <td class="text-muted">{{ $simpanan->firstItem() + $loop->index }}</td>
            <td>
              <div class="is-75bba466">{{ $item->anggota->nama ?? '—' }}</div>
              <div class="is-a7e9f0b8"><code>{{ $item->anggota->nomor_anggota ?? '' }}</code></div>
            </td>
            <td>
              @php $jenisMap = ['pokok'=>'badge-aktif','wajib'=>'badge-disetujui','sukarela'=>'badge-sudah']; @endphp
              <span class="badge-status {{ $jenisMap[$item->jenis_simpanan] ?? '' }}">
                {{ ucfirst($item->jenis_simpanan) }}
              </span>
            </td>
            <td class="fw-600 is-bcad93a6">
              Rp {{ number_format($item->jumlah, 0, ',', '.') }}
            </td>
            <td>
              <span class="is-7e7d71c3">
                <i class="bi bi-calendar3 is-3b8e2be5"></i>
                {{ $item->tanggal->format('d M Y') }}
              </span>
            </td>
            <td class="text-muted is-628f2cdc">{{ $item->keterangan ?? '—' }}</td>
            <td class="is-0de4e77b">{{ $item->petugas->name ?? '—' }}</td>
            <td>
              <div class="d-flex gap-1 justify-content-center">
                <a href="{{ route('simpanan.show', $item) }}" class="btn btn-sm btn-outline-info" title="Detail">
                  <i class="bi bi-eye"></i>
                </a>
                @can('simpanan.edit')
                <a href="{{ route('simpanan.edit', $item) }}" class="btn btn-sm btn-outline-warning" title="Ubah">
                  <i class="bi bi-pencil"></i>
                </a>
                @endcan
                @can('simpanan.hapus')
                <form action="{{ route('simpanan.destroy', $item) }}" method="POST" class="d-inline"
                  onsubmit="return confirm('Hapus data simpanan ini?')">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
                @endcan
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" class="text-center py-5 text-muted">
              <i class="bi bi-piggy-bank display-4 d-block mb-2 opacity-25"></i>
              Tidak ada simpanan ditemukan
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($simpanan->hasPages())
    <div class="p-4 border-top">{{ $simpanan->withQueryString()->links() }}</div>
  @endif
</div>
@endsection
