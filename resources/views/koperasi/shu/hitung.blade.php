@extends('layouts.aplikasi')

@section('judul', 'Hitung SHU')

@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('shu.index') }}">SHU</a></li>
  <li class="breadcrumb-item active">Hitung SHU</li>
@endsection

@push('css')
<style>
  .shu-searchbar {
    display: flex;
    align-items: center;
    gap: .5rem;
    flex-wrap: nowrap;
  }

  .shu-searchbar .shu-search-input {
    width: clamp(280px, 34vw, 460px);
    border-radius: 10px;
    overflow: hidden;
  }

  .shu-searchbar .input-group-text {
    background: #f8fafc;
    border-color: #dbe5f4;
    color: #4b6690;
  }

  .shu-searchbar .form-control {
    border-color: #dbe5f4;
    min-height: 42px;
  }

  .shu-searchbar .form-control:focus {
    border-color: #9ec1ff;
    box-shadow: 0 0 0 .16rem rgba(23, 92, 221, .12);
  }

  .shu-searchbar .shu-search-input .btn {
    min-height: 42px;
    border-color: #175cdd;
    border-radius: 0;
    padding-inline: .9rem;
    white-space: nowrap;
  }

  .shu-searchbar .btn {
    min-height: 42px;
    border-radius: 10px;
    padding-inline: .85rem;
    white-space: nowrap;
  }

  @media (max-width: 768px) {
    .shu-searchbar {
      width: 100%;
      flex-wrap: wrap;
    }

    .shu-searchbar .shu-search-input {
      width: 100%;
    }

    .shu-searchbar .btn {
      min-height: 40px;
    }
  }
</style>
@endpush

@section('konten')

@php
  $totalPengeluaran = $totalPengeluaran ?? 0;
@endphp

<div class="dashboard-welcome mb-4">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
      <div class="welcome-label mb-1">Laporan</div>
      <h4 class="mb-1 fw-700">Hitung SHU Baru</h4>
      <p class="mb-0 welcome-date"><i class="bi bi-calculator me-1"></i>Sisa Hasil Usaha Koperasi</p>
    </div>
    <div class="welcome-icon-wrap"><i class="bi bi-calculator"></i></div>
  </div>
</div>

{{-- Filter Tahun --}}
<div class="form-card mb-4">
  <form method="GET" action="{{ route('shu.hitung') }}" class="row g-3 align-items-end">
    <div class="col-md-3">
      <label class="form-label fw-600">Tahun</label>
      <select name="tahun" class="form-select">
        @foreach($tahunList as $t)
          <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>{{ $t }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-md-2">
      <button type="submit" class="btn btn-primary w-100">
        <i class="bi bi-funnel me-1"></i> Tampilkan
      </button>
    </div>
  </form>
</div>

@if($existing && $existing->status === 'final')
  <div class="alert alert-warning">
    <i class="bi bi-lock me-2"></i>SHU tahun <strong>{{ $tahun }}</strong> sudah <strong>final</strong> dan tidak dapat diubah.
    <a href="{{ route('shu.show', $existing) }}" class="alert-link ms-2">Lihat detail &rarr;</a>
  </div>
@else

{{-- Preview Statistik --}}
<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="kstat-card kstat-blue">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-piggy-bank"></i></div>
      <div class="kstat-value">Rp {{ number_format($totalSimpanan, 0, ',', '.') }}</div>
      <div class="kstat-label">Total Simpanan {{ $tahun }}</div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="kstat-card kstat-green">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-cash-coin"></i></div>
      <div class="kstat-value">Rp {{ number_format($totalPinjaman, 0, ',', '.') }}</div>
      <div class="kstat-label">Total Pinjaman Disetujui {{ $tahun }}</div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="kstat-card kstat-red">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <div class="kstat-icon"><i class="bi bi-receipt-cutoff"></i></div>
      <div class="kstat-value">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</div>
      <div class="kstat-label">Total Pengeluaran {{ $tahun }}</div>
    </div>
  </div>
</div>

{{-- Form Input SHU --}}
<div class="form-card mb-4">
  <h6 class="fw-700 mb-4 is-5ca88772">
    <i class="bi bi-pencil-square me-2 is-bc519fd0"></i>Input Data SHU Tahun {{ $tahun }}
  </h6>

  @if($errors->any())
    <div class="alert alert-danger mb-4">
      <ul class="mb-0">
        @foreach($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  @if($existing)
    <div class="alert alert-info mb-4">
      <i class="bi bi-info-circle me-2"></i>Sudah ada data SHU draft untuk tahun {{ $tahun }}. Menghitung ulang akan menghapus data draft tersebut.
    </div>
  @endif

  <form action="{{ route('shu.store') }}" method="POST" id="formShu">
    @csrf
    <input type="hidden" name="tahun" value="{{ $tahun }}">

    <div class="row g-4">
      <div class="col-md-6">
        <label class="form-label fw-600">Total Pendapatan (Rp) <span class="text-danger">*</span></label>
        <input type="number" name="total_pendapatan" class="form-control @error('total_pendapatan') is-invalid @enderror"
               value="{{ old('total_pendapatan') }}" min="0" step="1000" required
               placeholder="Contoh: 50000000">
        <div class="form-text">Total pendapatan koperasi tahun {{ $tahun }}</div>
        @error('total_pendapatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>
      <div class="col-md-6">
        <label class="form-label fw-600">Total Biaya Operasional (Rp) <span class="text-danger">*</span></label>
        <div class="input-group">
          <input type="number" name="total_biaya" id="totalBiaya"
                 class="form-control @error('total_biaya') is-invalid @enderror is-bf063d1c"
                 value="{{ old('total_biaya', $totalPengeluaran) }}"
                 min="0" step="1000" readonly>
          <span class="input-group-text" title="Otomatis dari data pengeluaran">
            <i class="bi bi-lock-fill text-primary"></i>
          </span>
        </div>
        <div class="form-text">
          <i class="bi bi-info-circle me-1"></i>
          Diambil otomatis dari total transaksi pengeluaran tahun {{ $tahun }}.
          <a href="{{ route('pengeluaran.index', ['tahun' => $tahun]) }}" target="_blank" class="ms-1">
            Lihat pengeluaran <i class="bi bi-box-arrow-up-right"></i>
          </a>
        </div>
        @error('total_biaya') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
      </div>

      <div class="col-12">
        <div class="p-3 rounded is-7359c1bc">
          <div class="d-flex justify-content-between">
            <span class="fw-600">Total SHU (Pendapatan - Biaya)</span>
            <span class="fw-700 text-success" id="previewShu">Rp 0</span>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <label class="form-label fw-600">% Jasa Modal <span class="text-danger">*</span></label>
        <div class="input-group">
          <input type="number" name="persentase_jasa_modal" id="pctModal"
                 class="form-control @error('persentase_jasa_modal') is-invalid @enderror"
                 value="{{ old('persentase_jasa_modal', 30) }}" min="0" max="100" step="0.01" required>
          <span class="input-group-text">%</span>
        </div>
        <div class="form-text">Porsi SHU untuk jasa simpanan anggota</div>
        @error('persentase_jasa_modal') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
      </div>
      <div class="col-md-6">
        <label class="form-label fw-600">% Jasa Usaha <span class="text-danger">*</span></label>
        <div class="input-group">
          <input type="number" name="persentase_jasa_usaha" id="pctUsaha"
                 class="form-control @error('persentase_jasa_usaha') is-invalid @enderror"
                 value="{{ old('persentase_jasa_usaha', 40) }}" min="0" max="100" step="0.01" required>
          <span class="input-group-text">%</span>
        </div>
        <div class="form-text">Porsi SHU berdasarkan pinjaman anggota</div>
        @error('persentase_jasa_usaha') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
      </div>

      <div class="col-12">
        <label class="form-label fw-600">Keterangan</label>
        <textarea name="keterangan" class="form-control" rows="2" maxlength="1000"
                  placeholder="Opsional...">{{ old('keterangan') }}</textarea>
      </div>
    </div>

    <hr class="my-4">

    <div class="d-flex gap-3">
      <button type="submit" class="btn btn-success px-4">
        <i class="bi bi-calculator me-1"></i> Hitung & Simpan
      </button>
      <a href="{{ route('shu.index') }}" class="btn btn-outline-secondary">Batal</a>
    </div>
  </form>
</div>

{{-- Preview Anggota (jika ada data) --}}
@if($anggotaList->count())
<div class="form-card p-0 overflow-hidden">
  <div class="p-4 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-3">
    <h6 class="mb-0 fw-700 is-5ca88772">
      <i class="bi bi-people me-2 is-bc519fd0"></i>
      Preview Anggota Tahun {{ $tahun }}
      <span class="badge bg-primary ms-2">{{ $anggotaList->total() }}</span>
    </h6>
    <form method="GET" action="{{ route('shu.hitung') }}" class="shu-searchbar">
      <input type="hidden" name="tahun" value="{{ $tahun }}">
      <div class="input-group input-group-sm shu-search-input">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input
          type="text"
          name="q_nama"
          class="form-control"
          placeholder="Cari nama anggota..."
          value="{{ $qNama ?? '' }}">
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-search me-1"></i>Cari
        </button>
      </div>
      @if(!empty($qNama))
        <a href="{{ route('shu.hitung', ['tahun' => $tahun]) }}" class="btn btn-sm btn-outline-secondary">
          <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
        </a>
      @endif
    </form>
  </div>
  <div class="table-responsive">
    <table class="table koperasi-table mb-0">
      <thead>
        <tr>
          <th class="text-center" style="width:72px;">No</th>
          <th>Nama Anggota</th>
          <th class="text-end">Total Simpanan</th>
          <th class="text-end">Total Pinjaman</th>
        </tr>
      </thead>
      <tbody>
        @foreach($anggotaList as $anggota)
          <tr>
            <td class="text-center text-muted">{{ $anggotaList->firstItem() + $loop->index }}</td>
            <td>{{ $anggota->nama }}</td>
            <td class="text-end">Rp {{ number_format($anggota->total_simpanan ?? 0, 0, ',', '.') }}</td>
            <td class="text-end">Rp {{ number_format($anggota->total_pinjaman ?? 0, 0, ',', '.') }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @if($anggotaList->hasPages())
    <div class="p-3 border-top bg-white">
      {{ $anggotaList->links() }}
    </div>
  @endif
</div>
@endif

@endif

@endsection

@push('js')
<script src="{{ asset('js/pages/shu-hitung.js') }}"></script>
@endpush