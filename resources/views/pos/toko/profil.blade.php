@extends('layouts.pos')
@section('judul','Profil Toko')
@section('breadcrumb')<li class="breadcrumb-item active">Profil Toko</li>@endsection

@section('konten')

{{-- Header --}}
<div class="d-flex justify-content-between align-items-center mb-4">
  <h5 class="fw-bold mb-0 is-feb6723d">
    <i class="bi bi-shop-window me-2 is-f571fe9b"></i>Profil Toko
  </h5>
  <a href="{{ route('pos.toko.edit') }}" class="btn btn-sm btn-warning text-white fw-semibold">
    <i class="bi bi-pencil-square me-1"></i>Edit Profil
  </a>
</div>

<div class="row g-4 mb-4">

  {{-- Info Toko --}}
  <div class="col-lg-4">
    <div class="form-card h-auto">
      <div class="text-center mb-4">
        <div class="mx-auto d-flex align-items-center justify-content-center mb-3 is-5fdd1ee6"
            >
          <i class="bi bi-shop"></i>
        </div>
        <h5 class="fw-700 mb-1 is-feb6723d">{{ $toko->nama }}</h5>
        @if($toko->pemilik)
          <div class="text-muted is-1e28117f"><i class="bi bi-person me-1"></i>{{ $toko->pemilik }}</div>
        @endif
      </div>

      <div class="d-flex flex-column gap-2 is-58180d3d">
        @if($toko->telepon)
        <div class="d-flex gap-2 align-items-start">
          <i class="bi bi-telephone text-muted is-f005b881"></i>
          <span>{{ $toko->telepon }}</span>
        </div>
        @endif
        @if($toko->email)
        <div class="d-flex gap-2 align-items-start">
          <i class="bi bi-envelope text-muted is-f005b881"></i>
          <span>{{ $toko->email }}</span>
        </div>
        @endif
        @if($toko->alamat)
        <div class="d-flex gap-2 align-items-start">
          <i class="bi bi-geo-alt text-muted is-f005b881"></i>
          <span>{{ $toko->alamat }}{{ $toko->kota ? ', ' . $toko->kota : '' }}</span>
        </div>
        @endif
        @if($toko->deskripsi)
        <div class="d-flex gap-2 align-items-start mt-1">
          <i class="bi bi-info-circle text-muted is-f005b881"></i>
          <span class="text-muted">{{ $toko->deskripsi }}</span>
        </div>
        @endif
      </div>

      @if(!$toko->telepon && !$toko->email && !$toko->alamat && !$toko->deskripsi)
        <div class="text-center text-muted mt-3 is-9ee0bb07">
          <i class="bi bi-pencil-square me-1"></i>
          <a href="{{ route('pos.toko.edit') }}" class="text-warning">Lengkapi profil toko</a>
        </div>
      @endif
    </div>
  </div>

  {{-- Statistik Keuangan --}}
  <div class="col-lg-8">
    <div class="row g-3 mb-3">
      {{-- Saldo Toko --}}
      <div class="col-sm-6">
        <div class="card border-0 shadow-sm h-100 is-5e4692a7">
          <div class="card-body">
            <div class="d-flex align-items-center gap-3 mb-2">
              <div class="rounded-circle d-flex align-items-center justify-content-center bg-warning-subtle text-warning is-deb0a778">
                <i class="bi bi-wallet2"></i>
              </div>
              <div class="text-muted small fw-600">Saldo Toko</div>
            </div>
            <div class="fw-700 is-1e6e05c1">
              Rp {{ number_format($toko->saldo, 0, ',', '.') }}
            </div>
            <div class="text-muted is-640fb1f6">Akumulasi harga pokok terjual</div>
          </div>
        </div>
      </div>

      {{-- Total Keuntungan --}}
      <div class="col-sm-6">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <div class="d-flex align-items-center gap-3 mb-2">
              <div class="rounded-circle d-flex align-items-center justify-content-center bg-success-subtle text-success is-deb0a778">
                <i class="bi bi-graph-up-arrow"></i>
              </div>
              <div class="text-muted small fw-600">Total Keuntungan</div>
            </div>
            <div class="fw-700 is-a0a55456">
              Rp {{ number_format($totalKeuntungan, 0, ',', '.') }}
            </div>
            <div class="text-muted is-640fb1f6">Omzet − Harga Pokok</div>
          </div>
        </div>
      </div>

      {{-- Omzet Total --}}
      <div class="col-sm-6">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <div class="d-flex align-items-center gap-3 mb-2">
              <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary-subtle text-primary is-deb0a778">
                <i class="bi bi-cash-stack"></i>
              </div>
              <div class="text-muted small fw-600">Total Omzet</div>
            </div>
            <div class="fw-700 is-23bda5b7">
              Rp {{ number_format($totalOmzet, 0, ',', '.') }}
            </div>
            <div class="text-muted is-640fb1f6">{{ number_format($totalTransaksi) }} transaksi</div>
          </div>
        </div>
      </div>

      {{-- Keuntungan Bulan Ini --}}
      <div class="col-sm-6">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <div class="d-flex align-items-center gap-3 mb-2">
              <div class="rounded-circle d-flex align-items-center justify-content-center bg-info-subtle text-info is-deb0a778">
                <i class="bi bi-calendar-month"></i>
              </div>
              <div class="text-muted small fw-600">Keuntungan Bulan Ini</div>
            </div>
            <div class="fw-700 is-76eafdfb">
              Rp {{ number_format($keuntunganBulanIni, 0, ',', '.') }}
            </div>
            <div class="text-muted is-640fb1f6">Omzet Rp {{ number_format($omzetBulanIni, 0, ',', '.') }}</div>
          </div>
        </div>
      </div>
    </div>

    {{-- Breakdown --}}
    <div class="card border-0 shadow-sm">
      <div class="card-body py-3 px-4">
        <div class="row text-center g-0">
          <div class="col border-end py-2">
            <div class="text-muted is-640fb1f6">Omzet Bulan Ini</div>
            <div class="fw-700 is-feb6723d">Rp {{ number_format($omzetBulanIni, 0, ',', '.') }}</div>
          </div>
          <div class="col border-end py-2">
            <div class="text-muted is-640fb1f6">Harga Pokok</div>
            <div class="fw-700 text-danger">Rp {{ number_format($hpBulanIni, 0, ',', '.') }}</div>
          </div>
          <div class="col py-2">
            <div class="text-muted is-640fb1f6">Laba Kotor</div>
            <div class="fw-700 text-success">Rp {{ number_format($keuntunganBulanIni, 0, ',', '.') }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Log Saldo --}}
<div class="card border-0 shadow-sm">
  <div class="card-header bg-white py-3 px-4 border-bottom">
    <h6 class="mb-0 fw-700 is-feb6723d">
      <i class="bi bi-clock-history me-2 is-f571fe9b"></i>Riwayat Saldo Toko
    </h6>
  </div>
  <div class="table-responsive">
    <table class="table tabel-koperasi mb-0">
      <thead class="is-98f358ac">
        <tr>
          <th>Waktu</th>
          <th>Transaksi</th>
          <th class="text-end">Harga Pokok Masuk</th>
          <th class="text-end">Saldo Sebelum</th>
          <th class="text-end">Saldo Sesudah</th>
          <th>Keterangan</th>
        </tr>
      </thead>
      <tbody>
        @forelse($saldoLog as $log)
        <tr>
          <td class="is-dadd1655">
            {{ $log->created_at->format('d M Y H:i') }}
          </td>
          <td>
            @if($log->transaksi)
              <a href="{{ route('pos.riwayat.show', $log->transaksi) }}"
                 class="text-decoration-none fw-600 is-link-amber-sm">
                {{ $log->transaksi->nomor }}
              </a>
            @else
              <span class="text-muted">—</span>
            @endif
          </td>
          <td class="text-end fw-600 text-success">
            + Rp {{ number_format($log->jumlah, 0, ',', '.') }}
          </td>
          <td class="text-end text-muted is-1e28117f">
            Rp {{ number_format($log->saldo_sebelum, 0, ',', '.') }}
          </td>
          <td class="text-end fw-600 is-feb6723d">
            Rp {{ number_format($log->saldo_sesudah, 0, ',', '.') }}
          </td>
          <td class="text-muted is-9ee0bb07">{{ $log->keterangan }}</td>
        </tr>
        @empty
        <tr>
          <td colspan="6" class="text-center text-muted py-4">
            <i class="bi bi-inbox is-a4ea9cc4"></i>
            <div class="mt-2">Belum ada riwayat saldo.</div>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($saldoLog->hasPages())
  <div class="card-footer bg-white border-top py-2 px-4">
    {{ $saldoLog->links() }}
  </div>
  @endif
</div>

@endsection
