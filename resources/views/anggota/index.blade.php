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
@php $jumlahMenunggu = \App\Models\Anggota::where('status','menunggu')->count(); @endphp
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
    <div class="col-md-5">
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
    <div class="col-md-2">
      <button type="submit" class="btn btn-primary w-100">
        <i class="bi bi-funnel me-1"></i> Filter
      </button>
    </div>
    <div class="col-md-2 d-flex gap-2">
      <a href="{{ route('anggota.create') }}" class="btn btn-success flex-fill">
        <i class="bi bi-person-plus me-1"></i> Tambah
      </a>
      <a href="{{ route('export.anggota', request()->query()) }}" class="btn btn-outline-secondary" title="Export CSV">
        <i class="bi bi-file-earmark-spreadsheet"></i>
      </a>
    </div>
  </form>
</div>

<!-- Table -->
<div class="form-card p-0 overflow-hidden">
  <div class="p-4 border-bottom d-flex align-items-center justify-content-between">
    <h6 class="mb-0 fw-700" style="color:#112344">
      <i class="bi bi-people-fill me-2" style="color:#175cdd"></i>Daftar Anggota
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
        <tr>
          <th>#</th>
          <th>No. Anggota</th>
          <th>Nama</th>
          <th>NIK</th>
          <th>Telepon</th>
          <th>Bergabung</th>
          <th>Status</th>
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
                  <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0 text-white"
                    style="width:34px;height:34px;background:#175cdd;font-weight:600;font-size:.8rem">
                    {{ strtoupper(substr($item->nama, 0, 1)) }}
                  </div>
                @endif
                <div>
                  <div style="font-weight:600;font-size:.9rem;color:#112344">{{ $item->nama }}</div>
                  <div style="font-size:.75rem;color:#6b7280">
                    {{ $item->jenis_anggota ?? $item->unitKerja->nama ?? '-' }}
                  </div>
                </div>
              </div>
            </td>
            <td>{{ $item->nik }}</td>
            <td>{{ $item->telepon }}</td>
            <td>{{ $item->tanggal_bergabung->format('d M Y') }}</td>
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
                <span style="display:inline-flex;align-items:center;gap:.3rem;font-size:.78rem;font-weight:600;color:#9ca3af;background:#f9fafb;border:1px solid #e5e7eb;border-radius:.4rem;padding:.2rem .55rem">
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
            <td colspan="8" class="text-center py-5 text-muted">
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
