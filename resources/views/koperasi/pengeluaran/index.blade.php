@extends('layouts.aplikasi')

@section('judul', 'Pengeluaran Operasional')

@section('breadcrumb')
  <li class="breadcrumb-item active">Pengeluaran</li>
@endsection

@section('konten')

{{-- Stat cards --}}
<div class="row g-3 mb-4">
  <div class="col-sm-6 col-lg-3">
    <div class="kstat-card kstat-red">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-cash-coin"></i></div>
      <div class="kstat-value">Rp {{ number_format($totalTahun, 0, ',', '.') }}</div>
      <div class="kstat-label">Total Pengeluaran {{ $tahun }}</div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="kstat-card kstat-amber">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-filter-circle-fill"></i></div>
      <div class="kstat-value">Rp {{ number_format($totalFilter, 0, ',', '.') }}</div>
      <div class="kstat-label">Total Filter Aktif</div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="kstat-card kstat-blue">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-receipt"></i></div>
      <div class="kstat-value">{{ $pengeluaran->total() }}</div>
      <div class="kstat-label">Jumlah Transaksi</div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="kstat-card kstat-green">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-tag-fill"></i></div>
      <div class="kstat-value">{{ $rekapKategori->count() }}</div>
      <div class="kstat-label">Kategori Aktif {{ $tahun }}</div>
    </div>
  </div>
</div>

{{-- Filter --}}
<div class="form-card mb-4">
  <form method="GET" action="{{ route('pengeluaran.index') }}" class="row g-3 align-items-end">
    <div class="col-md-2">
      <label class="form-label">Tahun</label>
      <select name="tahun" class="form-select">
        @foreach($tahunList as $t)
          <option value="{{ $t }}" @selected($tahun == $t)>{{ $t }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-md-2">
      <label class="form-label">Bulan</label>
      <select name="bulan" class="form-select">
        <option value="">Semua Bulan</option>
        @foreach(range(1,12) as $m)
          <option value="{{ $m }}" @selected($bulan == $m)>{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label">Kategori</label>
      <select name="kategori" class="form-select">
        <option value="">Semua Kategori</option>
        @foreach($labelKategori as $key => $label)
          <option value="{{ $key }}" @selected($kategori === $key)>{{ $label }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-md-auto d-flex gap-2 align-items-end">
      <button type="submit" class="btn btn-primary">
        <i class="bi bi-funnel me-1"></i> Filter
      </button>
      @can('pengeluaran.kelola')
      <a href="{{ route('pengeluaran.create') }}" class="btn btn-success">
        <i class="bi bi-plus me-1"></i> Tambah
      </a>
      @endcan
      <a href="{{ route('pengeluaran.cetak', array_filter(request()->only(['tahun','bulan','kategori']))) }}" class="btn btn-outline-secondary" target="_blank">
        <i class="bi bi-printer me-1"></i> Cetak
      </a>
    </div>
  </form>
</div>

<div class="row g-4">
  {{-- Tabel utama --}}
  <div class="col-lg-8">
    <div class="form-card p-0 overflow-hidden">

      {{-- Header tabel --}}
      <div class="p-4 pb-2 d-flex align-items-center">
        <h6 class="mb-0 fw-700 is-5ca88772">
          <i class="bi bi-cash-coin me-2 is-bc8099b4"></i>Daftar Pengeluaran
        </h6>
      </div>

      @if(session('sukses'))
        <div class="mx-4 alert alert-success py-2">{{ session('sukses') }}</div>
      @endif

      <div class="table-responsive">
        @php
          $sUrl = fn($k) => request()->fullUrlWithQuery(['sort' => $k, 'dir' => ($sortBy===$k && $sortDir==='asc') ? 'desc' : 'asc']);
          $sIco = fn($k) => $sortBy===$k
            ? ($sortDir==='asc'
                ? '<i class="bi bi-sort-up ms-1 is-0a789cad"></i>'
                : '<i class="bi bi-sort-down ms-1 is-0a789cad"></i>')
            : '<i class="bi bi-arrow-down-up text-muted opacity-50 ms-1 is-bc9efe64"></i>';
        @endphp
        <table class="koperasi-table table mb-0">
          <thead>
            <tr>
              <th><a href="{{ $sUrl('tanggal') }}" class="text-decoration-none text-reset d-inline-flex align-items-center">Tanggal {!! $sIco('tanggal') !!}</a></th>
              <th><a href="{{ $sUrl('judul') }}" class="text-decoration-none text-reset d-inline-flex align-items-center">Judul / Uraian {!! $sIco('judul') !!}</a></th>
              <th><a href="{{ $sUrl('kategori') }}" class="text-decoration-none text-reset d-inline-flex align-items-center">Kategori {!! $sIco('kategori') !!}</a></th>
              <th class="text-end"><a href="{{ $sUrl('jumlah') }}" class="text-decoration-none text-reset d-inline-flex align-items-center justify-content-end">Jumlah (Rp) {!! $sIco('jumlah') !!}</a></th>
              <th width="80"></th>
            </tr>
          </thead>
          <tbody>
            @forelse($pengeluaran as $p)
            <tr>
              <td>
                <span class="is-7e7d71c3">
                  <i class="bi bi-calendar3 is-3b8e2be5"></i>
                  {{ $p->tanggal->format('d M Y') }}
                </span>
              </td>
              <td>
                {{ $p->judul }}
                @if($p->keterangan)
                  <div class="text-muted is-6c660c40">{{ Str::limit($p->keterangan, 60) }}</div>
                @endif
              </td>
              <td>
                <span class="badge bg-secondary bg-opacity-75 text-dark is-640fb1f6">
                  {{ $p->label_kategori }}
                </span>
              </td>
              <td class="text-end fw-600 text-danger">{{ number_format($p->jumlah, 0, ',', '.') }}</td>
              <td>
                <div class="d-flex gap-1 justify-content-end">
                  <a href="{{ route('pengeluaran.show', $p) }}" class="btn btn-sm btn-outline-primary py-0 px-2" title="Detail">
                    <i class="bi bi-eye"></i>
                  </a>
                  @can('pengeluaran.kelola')
                  <a href="{{ route('pengeluaran.edit', $p) }}" class="btn btn-sm btn-outline-secondary py-0 px-2" title="Edit">
                    <i class="bi bi-pencil"></i>
                  </a>
                  @endcan
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="5" class="text-center text-muted py-4">
                <i class="bi bi-inbox me-2"></i>Belum ada data pengeluaran.
              </td>
            </tr>
            @endforelse
          </tbody>
          @if($pengeluaran->count())
          <tfoot>
            <tr class="table-light fw-700">
              <td colspan="3" class="text-end">Total</td>
              <td class="text-end text-danger">{{ number_format($pengeluaran->sum('jumlah'), 0, ',', '.') }}</td>
              <td></td>
            </tr>
          </tfoot>
          @endif
        </table>
      </div>

      @if($pengeluaran->hasPages())
      <div class="px-4 py-3 border-top">
        {{ $pengeluaran->links() }}
      </div>
      @endif

    </div>
  </div>

  {{-- Rekap kategori --}}
  <div class="col-lg-4">
    <div class="form-card">
      <h6 class="fw-700 mb-3">
        <i class="bi bi-pie-chart-fill me-2 text-primary"></i>Rekap per Kategori {{ $tahun }}
        @if($bulan) <span class="text-muted fw-400 is-6c660c40">– Bln {{ $bulan }}</span> @endif
      </h6>

      @if($rekapKategori->isEmpty())
        <p class="text-muted text-center py-3 mb-0 is-58180d3d">Tidak ada data.</p>
      @else
        @php
          $grandTotal = $rekapKategori->sum();
        @endphp
        <div class="d-flex flex-column gap-2">
          @foreach($rekapKategori->sortDesc() as $kat => $total)
          @php
            $persen = $grandTotal > 0 ? round(($total / $grandTotal) * 100) : 0;
          @endphp
          <div>
            <div class="d-flex justify-content-between mb-1 is-9ee0bb07">
              <span>{{ $labelKategori[$kat] ?? $kat }}</span>
              <span class="fw-600 text-danger">{{ number_format($total, 0, ',', '.') }}</span>
            </div>
            <div class="progress is-339f805b">
              <div class="progress-bar bg-danger is-0118ec6d"></div>
            </div>
          </div>
          @endforeach
        </div>
        <hr class="my-3">
        <div class="d-flex justify-content-between fw-700">
          <span>Total</span>
          <span class="text-danger">Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
        </div>
      @endif
    </div>
  </div>
</div>

@endsection
