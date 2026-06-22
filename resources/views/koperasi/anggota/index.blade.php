@extends('layouts.aplikasi')

@section('judul', 'Data Anggota')

@section('konten')

{{-- Page Banner --}}
<div class="dashboard-welcome mb-4">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
      <div class="welcome-label mb-1">Manajemen Data</div>
      <h4 class="mb-1 fw-700">Data Anggota</h4>
      <p class="mb-0 welcome-date">
        <i class="bi bi-calendar3 me-1"></i>
        {{ now()->translatedFormat('l, d F Y') }}
      </p>
    </div>
    <div class="welcome-icon-wrap">
      <i class="bi bi-people-fill"></i>
    </div>
  </div>
</div>

{{-- Stat Cards --}}
<div class="row g-4 mb-4">
  <div class="col-xl-3 col-sm-6">
    <div class="kstat-card kstat-blue">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-person-check-fill"></i></div>
      <div class="kstat-value">{{ number_format($statsAnggota['aktif']) }}</div>
      <div class="kstat-label">Anggota Aktif</div>
      <div class="kstat-trend"><i class="bi bi-people-fill me-1"></i> Terdaftar</div>
    </div>
  </div>
  <div class="col-xl-3 col-sm-6">
    <div class="kstat-card kstat-green">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-person-plus-fill"></i></div>
      <div class="kstat-value">{{ number_format($statsAnggota['baru']) }}</div>
      <div class="kstat-label">Bergabung Bulan Ini</div>
      <div class="kstat-trend"><i class="bi bi-calendar-check-fill me-1"></i> {{ now()->translatedFormat('F Y') }}</div>
    </div>
  </div>
  <div class="col-xl-3 col-sm-6">
    <div class="kstat-card kstat-amber">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-person-dash-fill"></i></div>
      <div class="kstat-value">{{ number_format($statsAnggota['tidak_aktif']) }}</div>
      <div class="kstat-label">Tidak Aktif</div>
      <div class="kstat-trend"><i class="bi bi-x-circle-fill me-1"></i> Dinonaktifkan</div>
    </div>
  </div>
  <div class="col-xl-3 col-sm-6">
    <div class="kstat-card kstat-red">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-hourglass-split"></i></div>
      <div class="kstat-value">{{ number_format($statsAnggota['menunggu']) }}</div>
      <div class="kstat-label">Menunggu Verifikasi</div>
      <div class="kstat-trend">
        @if($statsAnggota['menunggu'] > 0)
          <i class="bi bi-exclamation-circle-fill me-1"></i> Perlu ditinjau
        @else
          <i class="bi bi-check-circle-fill me-1"></i> Semua diproses
        @endif
      </div>
    </div>
  </div>
</div>

{{-- Alert pendaftaran menunggu --}}
@php $jumlahMenunggu = \App\Models\Koperasi\Anggota::where('status','menunggu')->count(); @endphp
@if($jumlahMenunggu > 0)
  <div class="alert alert-warning d-flex align-items-center gap-3 mb-4" role="alert">
    <i class="bi bi-hourglass-split fs-4"></i>
    <div>
      Ada <strong>{{ $jumlahMenunggu }}</strong> pendaftaran anggota menunggu verifikasi.
      <a href="{{ route('anggota.index', ['status' => 'menunggu']) }}" class="alert-link ms-1">Lihat sekarang &rarr;</a>
    </div>
  </div>
@endif

<!-- Filter & Search Bar -->
<div class="form-card mb-4">
  <form method="GET" action="{{ route('anggota.index') }}" class="row g-3 align-items-end">
    @csrf
    <div class="col">
      <label class="form-label">Cari Anggota</label>
      <div class="input-group">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input type="text" name="cari" class="form-control" placeholder="Nama, nomor, atau NIK..."
          value="{{ request('cari') }}">
      </div>
    </div>
    <div class="col-md-3">
      <label class="form-label">Status</label>
      <select name="status" class="form-select">
        <option value="">Semua Status</option>
        <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
        <option value="tidak_aktif" {{ request('status') === 'tidak_aktif' ? 'selected' : '' }}>Tidak Aktif</option>
        <option value="menunggu" {{ request('status') === 'menunggu' ? 'selected' : '' }}>Menunggu Verifikasi</option>
      </select>
    </div>
    <div class="col-md-auto d-flex gap-2 align-items-end">
      <button type="submit" class="btn btn-primary">
        <i class="bi bi-funnel me-1"></i> Filter
      </button>
      @can('anggota.tambah')
      <a href="{{ route('anggota.create') }}" class="btn btn-success">
        <i class="bi bi-plus me-1"></i> Tambah
      </a>
      <a href="{{ route('import.anggota.form') }}" class="btn btn-warning">
        <i class="bi bi-box-arrow-in-down me-1"></i> Import
      </a>
      @if(($anggotaBelumDigenerate ?? 0) > 0)
      <button
        type="submit"
        class="btn btn-outline-primary"
        formaction="{{ route('anggota.generate-akun-login') }}"
        formmethod="POST"
        formnovalidate
        onclick="return confirm('Generate akun login maksimal 30 anggota per sekali proses? Sisa belum digenerate: {{ $anggotaBelumDigenerate }}')">
        <i class="bi bi-person-gear me-1"></i> Generate Akun Login ({{ $anggotaBelumDigenerate }})
      </button>
      @endif
      @endcan
      <a href="{{ route('anggota.cetak', array_filter(request()->only(['cari','status']))) }}" class="btn btn-outline-secondary" target="_blank">
        <i class="bi bi-printer me-1"></i> Cetak
      </a>
    </div>
  </form>
</div>

<!-- Table -->
<div class="form-card p-0 overflow-hidden">
  <div class="p-4 border-bottom d-flex align-items-center justify-content-between">
    <h6 class="mb-0 fw-700 is-5ca88772">
      <i class="bi bi-people-fill me-2 is-bc519fd0"></i>Daftar Anggota
      <span class="badge bg-primary ms-2">{{ $anggota->total() }}</span>
    </h6>
    @if(request('cari') || request('status'))
      <a href="{{ route('anggota.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-x-circle me-1"></i> Reset Filter
      </a>
    @endif
  </div>
  <div class="table-responsive">
    <table class="koperasi-table mb-0">
      <thead>
        @php
          function sortUrl($kolom, $sortBy, $sortDir) {
            $dir = ($sortBy === $kolom && $sortDir === 'asc') ? 'desc' : 'asc';
            return request()->fullUrlWithQuery(['sort' => $kolom, 'dir' => $dir]);
          }
          function sortIcon($kolom, $sortBy, $sortDir) {
            if ($sortBy !== $kolom) return '<i class="bi bi-arrow-down-up text-muted opacity-50 ms-1 is-bc9efe64"></i>';
            return $sortDir === 'asc'
              ? '<i class="bi bi-sort-up ms-1 is-0a789cad"></i>'
              : '<i class="bi bi-sort-down ms-1 is-0a789cad"></i>';
          }
        @endphp
        <tr>
          <th>#</th>
          <th><a href="{{ sortUrl('nomor_anggota', $sortBy, $sortDir) }}" class="text-decoration-none text-reset d-inline-flex align-items-center">No. Anggota {!! sortIcon('nomor_anggota', $sortBy, $sortDir) !!}</a></th>
          <th><a href="{{ sortUrl('nama', $sortBy, $sortDir) }}" class="text-decoration-none text-reset d-inline-flex align-items-center">Nama {!! sortIcon('nama', $sortBy, $sortDir) !!}</a></th>
          <th>NIK</th>
          <th>Telepon</th>
          <th><a href="{{ sortUrl('tanggal_bergabung', $sortBy, $sortDir) }}" class="text-decoration-none text-reset d-inline-flex align-items-center">Bergabung {!! sortIcon('tanggal_bergabung', $sortBy, $sortDir) !!}</a></th>
          <th><a href="{{ sortUrl('status', $sortBy, $sortDir) }}" class="text-decoration-none text-reset d-inline-flex align-items-center">Status {!! sortIcon('status', $sortBy, $sortDir) !!}</a></th>
          <th class="text-center">Akun Login</th>
          <th class="text-center">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($anggota as $item)
          <tr>
            <td class="text-muted">{{ $anggota->firstItem() + $loop->index }}</td>
            <td><code>{{ $item->nomor_anggota }}</code></td>
            <td>
              <div class="d-flex align-items-center gap-2">
                @if($item->foto)
                  <img src="{{ Storage::url($item->foto) }}" alt=""
                    class="rounded-circle object-fit-cover"
                    style="width:34px;height:34px;border:2px solid #e5ecfa">
                @else
                  <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0 text-white is-88624c15"
                   >
                    {{ strtoupper(substr($item->nama, 0, 1)) }}
                  </div>
                @endif
                <div>
                  <div class="is-75bba466">{{ $item->nama }}</div>
                  <div class="is-986f48ab">
                    @if($item->jenis_anggota)
                      <i class="bi bi-tag-fill is-eb86c7ca"></i> {{ $item->jenis_anggota }}
                    @elseif($item->unitKerja)
                      <i class="bi bi-building-fill is-95d7825e"></i> {{ $item->unitKerja->nama }}
                    @else
                      <span class="opacity-50">—</span>
                    @endif
                  </div>
                </div>
              </div>
            </td>
            <td><code>{{ $item->nik }}</code></td>
            <td>
              <a href="tel:{{ $item->telepon }}" class="text-decoration-none" style="color:#374151">
                <i class="bi bi-telephone me-1 is-81eeaec5"></i>{{ $item->telepon }}
              </a>
            </td>
            <td>
              <span class="is-7e7d71c3">
                <i class="bi bi-calendar3 is-3b8e2be5"></i>
                {{ $item->tanggal_bergabung->format('d M Y') }}
              </span>
            </td>
            <td>
              <span class="badge-status
                {{ $item->status === 'aktif' ? 'badge-aktif' : ($item->status === 'menunggu' ? 'badge-menunggu' : 'badge-ditolak') }}">
                {{ $item->status === 'aktif' ? 'Aktif' : ($item->status === 'menunggu' ? 'Menunggu' : 'Tidak Aktif') }}
              </span>
            </td>
            <td class="text-center">
              @if($item->user)
                <span title="{{ $item->user->email }}" style="display:inline-flex;align-items:center;gap:.3rem;font-size:.78rem;font-weight:600;color:#059669;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:.4rem;padding:.2rem .55rem">
                  <i class="bi bi-person-check-fill"></i> Ada
                </span>
              @else
                <span class="is-dda54cc9">
                  <i class="bi bi-person-x"></i> Belum
                </span>
              @endif
            </td>
            <td>
              <div class="d-flex gap-1 justify-content-center">
                @if($item->status === 'menunggu')
                  <form action="{{ route('anggota.setujui', $item) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-success" title="Setujui">
                      <i class="bi bi-check-lg"></i> Setujui
                    </button>
                  </form>
                  <form action="{{ route('anggota.tolak', $item) }}" method="POST" class="d-inline"
                    onsubmit="return confirm('Tolak pendaftaran {{ $item->nama }}?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger" title="Tolak">
                      <i class="bi bi-x-lg"></i> Tolak
                    </button>
                  </form>
                @else
                  <a href="{{ route('anggota.rekap', $item) }}" class="btn btn-sm btn-outline-primary" title="Rekap Profil">
                    <i class="bi bi-bar-chart-line"></i>
                  </a>
                  <a href="{{ route('anggota.kartu', $item) }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="Kartu Anggota">
                    <i class="bi bi-credit-card-2-front"></i>
                  </a>
                  <a href="{{ route('anggota.show', $item) }}" class="btn btn-sm btn-outline-info" title="Detail">
                    <i class="bi bi-eye"></i>
                  </a>
                  <a href="{{ route('anggota.edit', $item) }}" class="btn btn-sm btn-outline-warning" title="Ubah">
                    <i class="bi bi-pencil"></i>
                  </a>
                  <form action="{{ route('anggota.destroy', $item) }}" method="POST" class="d-inline"
                    onsubmit="return confirm('Hapus anggota {{ $item->nama }}? Tindakan ini tidak dapat dibatalkan.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                      <i class="bi bi-trash"></i>
                    </button>
                  </form>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="9" class="text-center py-5 text-muted">
              <i class="bi bi-people display-4 d-block mb-2 opacity-25"></i>
              Tidak ada anggota ditemukan
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($anggota->hasPages())
    <div class="p-4 border-top">
      {{ $anggota->withQueryString()->links() }}
    </div>
  @endif
</div>
@endsection
