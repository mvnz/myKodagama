@extends('layouts.aplikasi')

@section('judul', 'Laporan Keuangan')

@section('breadcrumb')
  <li class="breadcrumb-item active">Laporan Keuangan</li>
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('css/pages/laporan-keuangan.css') }}">
@endpush

@section('konten')

@php
  $namaBulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
  $periodeLabel = !empty($bulan) ? ($namaBulan[(int)$bulan] . ' ' . $tahun) : ('Tahun ' . $tahun);
@endphp

<div class="row justify-content-center">
<div class="col-12">

  {{-- ── Page Banner ────────────────────────────────────────── --}}
  <div class="dashboard-welcome mb-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
      <div>
        <div class="welcome-label mb-1">Laporan Keuangan</div>
        <h4 class="mb-1 fw-700">Laporan Keuangan Koperasi</h4>
        <p class="mb-0 welcome-date">
          <i class="bi bi-calendar3 me-1"></i>
          Periode: <strong>{{ $periodeLabel }}</strong>
        </p>
      </div>
      <div class="welcome-icon-wrap">
        <i class="bi bi-file-earmark-bar-graph"></i>
      </div>
    </div>
  </div>

  {{-- ── Kartu Ringkasan ───────────────────────────────────────── --}}
  <div class="row g-4 mb-4">

    {{-- Pemasukan --}}
    <div class="col-xl-3 col-md-6">
      <div class="kstat-card kstat-green">
        <div class="kstat-deco"></div><div class="kstat-deco2"></div>
        <div class="kstat-icon"><i class="bi bi-arrow-down-circle-fill"></i></div>
        <div class="kstat-value kstat-value-sm">Rp {{ number_format($totalPemasukan, 0, ',', '.') }}</div>
        <div class="kstat-label">Total Pemasukan</div>
        <div class="kstat-trend"><i class="bi bi-arrow-down-circle-fill me-1"></i> Masuk</div>
      </div>
    </div>

    {{-- Pengeluaran --}}
    <div class="col-xl-3 col-md-6">
      <div class="kstat-card kstat-red">
        <div class="kstat-deco"></div><div class="kstat-deco2"></div>
        <div class="kstat-icon"><i class="bi bi-arrow-up-circle-fill"></i></div>
        <div class="kstat-value kstat-value-sm">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</div>
        <div class="kstat-label">Total Pengeluaran</div>
        <div class="kstat-trend"><i class="bi bi-arrow-up-circle-fill me-1"></i> Keluar</div>
      </div>
    </div>

    {{-- Arus Kas Bersih --}}
    <div class="col-xl-3 col-md-6">
      <div class="kstat-card {{ $arusKasBersih >= 0 ? 'kstat-blue' : 'kstat-red' }}">
        <div class="kstat-deco"></div><div class="kstat-deco2"></div>
        <div class="kstat-icon"><i class="bi bi-wallet2"></i></div>
        <div class="kstat-value kstat-value-sm">{{ $arusKasBersih >= 0 ? '+' : '' }}Rp {{ number_format($arusKasBersih, 0, ',', '.') }}</div>
        <div class="kstat-label">Arus Kas Bersih</div>
        <div class="kstat-trend">
          <i class="bi bi-{{ $arusKasBersih >= 0 ? 'arrow-up' : 'arrow-down' }}-circle-fill me-1"></i>
          {{ $arusKasBersih >= 0 ? 'Surplus' : 'Defisit' }}
        </div>
      </div>
    </div>

    {{-- Pinjaman Dicairkan --}}
    <div class="col-xl-3 col-md-6">
      <div class="kstat-card kstat-amber">
        <div class="kstat-deco"></div><div class="kstat-deco2"></div>
        <div class="kstat-icon"><i class="bi bi-cash-coin"></i></div>
        <div class="kstat-value">{{ $jumlahPinjamanBaru }}</div>
        <div class="kstat-label">Pinjaman Dicairkan</div>
        <div class="kstat-trend"><i class="bi bi-cash-stack me-1"></i> Rp {{ number_format($pinjamanDicairkan, 0, ',', '.') }}</div>
      </div>
    </div>

  </div>

  {{-- ── Filter Periode ───────────────────────────────────────── --}}
  <div class="form-card mb-4">
    <form method="GET" action="{{ route('laporan.keuangan') }}" class="row g-3 align-items-end">

      <div class="col-md-3">
        <label class="form-label fw-600">Tahun</label>
        <select name="tahun" class="form-select">
          @foreach($tahunTersedia as $t)
            <option value="{{ $t }}" {{ (int)$tahun === (int)$t ? 'selected' : '' }}>{{ $t }}</option>
          @endforeach
          {{-- Pastikan tahun aktif selalu tersedia --}}
          @if(!$tahunTersedia->contains($tahun))
            <option value="{{ $tahun }}" selected>{{ $tahun }}</option>
          @endif
        </select>
      </div>

      <div class="col-md-3">
        <label class="form-label fw-600">Bulan <span class="text-muted fw-400">(opsional)</span></label>
        <select name="bulan" class="form-select">
          <option value="" {{ $bulan === '' ? 'selected' : '' }}>Semua Bulan</option>
          @foreach($namaBulan as $i => $nb)
            @if($i > 0)
              <option value="{{ $i }}" {{ (string)$bulan === (string)$i ? 'selected' : '' }}>{{ $nb }}</option>
            @endif
          @endforeach
        </select>
      </div>

      <div class="col-md-2">
        <button type="submit" class="btn btn-primary w-100">
          <i class="bi bi-funnel me-1"></i> Tampilkan
        </button>
      </div>

      <div class="col-md-2">
        <a href="{{ route('laporan.keuangan') }}" class="btn btn-outline-secondary w-100">
          <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
        </a>
      </div>

      <div class="col-md-2">
        <a href="{{ route('laporan.keuangan.cetak', ['tahun' => $tahun, 'bulan' => $bulan]) }}"
          target="_blank" class="btn btn-outline-success w-100">
          <i class="bi bi-printer me-1"></i> Cetak
        </a>
      </div>

    </form>
  </div>

  <div class="row g-4">

    {{-- ── Rincian Pemasukan ─────────────────────────────────── --}}
    <div class="col-xl-6">
      <div class="form-card h-100">
        <h6 class="fw-700 mb-4 d-flex align-items-center gap-2 is-bc519fd0">
          <i class="bi bi-arrow-down-circle-fill text-success"></i> Rincian Pemasukan
        </h6>

        {{-- Simpanan --}}
        <div class="mb-3">
          <div class="d-flex justify-content-between mb-1">
            <span class="fw-600 small">Simpanan Masuk</span>
            <span class="fw-700 small">Rp {{ number_format($totalSimpananMasuk, 0, ',', '.') }}</span>
          </div>
          @php $maxSimpan = max($simpananPokok, $simpananWajib, $simpananSukarela, 1); @endphp

          <div class="d-flex justify-content-between small text-muted mb-1 mt-3">
            <span><span class="badge bg-primary badge-jenis me-1">Pokok</span> Rp {{ number_format($simpananPokok, 0, ',', '.') }}</span>
            <span>{{ $totalSimpananMasuk > 0 ? number_format($simpananPokok / $totalSimpananMasuk * 100, 1) : 0 }}%</span>
          </div>
          <div class="progress progress-bar-thin mb-2">
            <div class="progress-bar bg-primary is-6ab9adde"></div>
          </div>

          <div class="d-flex justify-content-between small text-muted mb-1">
            <span><span class="badge bg-success badge-jenis me-1">Wajib</span> Rp {{ number_format($simpananWajib, 0, ',', '.') }}</span>
            <span>{{ $totalSimpananMasuk > 0 ? number_format($simpananWajib / $totalSimpananMasuk * 100, 1) : 0 }}%</span>
          </div>
          <div class="progress progress-bar-thin mb-2">
            <div class="progress-bar bg-success is-0c6c9f73"></div>
          </div>

          <div class="d-flex justify-content-between small text-muted mb-1">
            <span><span class="badge bg-warning text-dark badge-jenis me-1">Sukarela</span> Rp {{ number_format($simpananSukarela, 0, ',', '.') }}</span>
            <span>{{ $totalSimpananMasuk > 0 ? number_format($simpananSukarela / $totalSimpananMasuk * 100, 1) : 0 }}%</span>
          </div>
          <div class="progress progress-bar-thin mb-3">
            <div class="progress-bar bg-warning is-d354d56b"></div>
          </div>
        </div>

        <hr class="my-3">

        {{-- Angsuran --}}
        <div>
          <div class="d-flex justify-content-between mb-1">
            <span class="fw-600 small">Angsuran Diterima</span>
            <span class="fw-700 small">Rp {{ number_format($totalAngsuranDiterima, 0, ',', '.') }}</span>
          </div>
          <div class="d-flex justify-content-between small text-muted mb-1 mt-2">
            <span><span class="badge bg-info badge-jenis me-1">Pokok</span> Rp {{ number_format($totalPokokDiterima, 0, ',', '.') }}</span>
          </div>
          <div class="progress progress-bar-thin mb-2">
            <div class="progress-bar bg-info is-636455d1"></div>
          </div>
          <div class="d-flex justify-content-between small text-muted mb-1">
            <span><span class="badge bg-danger badge-jenis me-1">Bunga</span> Rp {{ number_format($totalBungaDiterima, 0, ',', '.') }}</span>
          </div>
          <div class="progress progress-bar-thin">
            <div class="progress-bar bg-danger is-62b6dde3"></div>
          </div>
        </div>

      </div>
    </div>

    {{-- ── Rincian Pengeluaran ───────────────────────────────── --}}
    <div class="col-xl-6">
      <div class="form-card h-100">
        <h6 class="fw-700 mb-4 d-flex align-items-center gap-2 is-bc519fd0">
          <i class="bi bi-arrow-up-circle-fill text-danger"></i> Rincian Pengeluaran
        </h6>
        <div class="mb-3">
          <div class="d-flex justify-content-between mb-1">
            <span class="fw-600 small">Pinjaman Dicairkan</span>
            <span class="fw-700 small">Rp {{ number_format($pinjamanDicairkan, 0, ',', '.') }}</span>
          </div>
          <div class="progress progress-bar-thin mt-2">
            <div class="progress-bar bg-danger is-6d567387"></div>
          </div>
          <small class="text-muted mt-2 d-block">{{ $jumlahPinjamanBaru }} transaksi pinjaman disetujui pada periode ini.</small>
        </div>

        <hr class="my-3">

        {{-- Rekap Arus Kas --}}
        <h6 class="fw-700 mb-3 small text-uppercase text-muted">Rekap Arus Kas</h6>
        <table class="table table-sm mb-0 is-1e28117f">
          <tbody>
            <tr>
              <td class="text-muted">Total Simpanan Masuk</td>
              <td class="text-end fw-600 text-success">+ Rp {{ number_format($totalSimpananMasuk, 0, ',', '.') }}</td>
            </tr>
            <tr>
              <td class="text-muted">Total Angsuran Diterima</td>
              <td class="text-end fw-600 text-success">+ Rp {{ number_format($totalAngsuranDiterima, 0, ',', '.') }}</td>
            </tr>
            <tr>
              <td class="text-muted">Pinjaman Dicairkan</td>
              <td class="text-end fw-600 text-danger">− Rp {{ number_format($pinjamanDicairkan, 0, ',', '.') }}</td>
            </tr>
            <tr class="border-top">
              <td class="fw-700">Arus Kas Bersih</td>
              <td class="text-end fw-700 {{ $arusKasBersih >= 0 ? 'text-success' : 'text-danger' }}">
                {{ $arusKasBersih >= 0 ? '+' : '' }} Rp {{ number_format($arusKasBersih, 0, ',', '.') }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    {{-- ── Tabel Detail Simpanan ─────────────────────────────── --}}
    <div class="col-12">
      <div class="form-card p-0 overflow-hidden">
        <div class="d-flex align-items-center gap-2 px-4 py-3 border-bottom">
          <i class="bi bi-piggy-bank-fill text-primary"></i>
          <h6 class="fw-700 mb-0 is-5ca88772">Transaksi Simpanan Terbaru</h6>
          <span class="badge bg-primary ms-auto">{{ $periodeLabel }}</span>
        </div>
        @if($detailSimpanan->isEmpty())
          <div class="text-center py-5 text-muted">
            <i class="bi bi-inbox fs-2 d-block mb-2"></i> Tidak ada data simpanan pada periode ini.
          </div>
        @else
        <div class="table-responsive">
          <table class="table tabel-detail mb-0">
            <thead>
              <tr>
                <th>Tanggal</th>
                <th>Anggota</th>
                <th>Jenis</th>
                <th class="text-end">Jumlah</th>
                <th>Keterangan</th>
              </tr>
            </thead>
            <tbody>
              @foreach($detailSimpanan as $s)
              <tr>
                <td class="text-muted">{{ $s->tanggal->format('d M Y') }}</td>
                <td class="fw-600">{{ $s->anggota->nama }}</td>
                <td>
                  @php $warna = ['pokok'=>'primary','wajib'=>'success','sukarela'=>'warning']; @endphp
                  <span class="badge bg-{{ $warna[$s->jenis_simpanan] ?? 'secondary' }} badge-jenis">
                    {{ ucfirst($s->jenis_simpanan) }}
                  </span>
                </td>
                <td class="text-end fw-600 text-success">Rp {{ number_format($s->jumlah, 0, ',', '.') }}</td>
                <td class="text-muted">{{ $s->keterangan ?? '—' }}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        @endif
      </div>
    </div>

    {{-- ── Tabel Detail Angsuran ─────────────────────────────── --}}
    <div class="col-12">
      <div class="form-card p-0 overflow-hidden">
        <div class="d-flex align-items-center gap-2 px-4 py-3 border-bottom">
          <i class="bi bi-calendar-check-fill text-success"></i>
          <h6 class="fw-700 mb-0 is-5ca88772">Angsuran Dibayar</h6>
          <span class="badge bg-success ms-auto">{{ $periodeLabel }}</span>
        </div>
        @if($detailAngsuran->isEmpty())
          <div class="text-center py-5 text-muted">
            <i class="bi bi-inbox fs-2 d-block mb-2"></i> Tidak ada data angsuran pada periode ini.
          </div>
        @else
        <div class="table-responsive">
          <table class="table tabel-detail mb-0">
            <thead>
              <tr>
                <th>Tgl Bayar</th>
                <th>Anggota</th>
                <th>No. Pinjaman</th>
                <th class="text-center">Ke-</th>
                <th class="text-end">Pokok</th>
                <th class="text-end">Bunga</th>
                <th class="text-end">Total</th>
              </tr>
            </thead>
            <tbody>
              @foreach($detailAngsuran as $a)
              <tr>
                <td class="text-muted">{{ $a->tanggal_bayar->format('d M Y') }}</td>
                <td class="fw-600">{{ $a->pinjaman->anggota->nama }}</td>
                <td class="text-muted small">{{ $a->pinjaman->nomor_pinjaman }}</td>
                <td class="text-center">{{ $a->ke }}</td>
                <td class="text-end">Rp {{ number_format($a->pokok, 0, ',', '.') }}</td>
                <td class="text-end text-danger">Rp {{ number_format($a->bunga, 0, ',', '.') }}</td>
                <td class="text-end fw-700 text-success">Rp {{ number_format($a->jumlah_angsuran, 0, ',', '.') }}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        @endif
      </div>
    </div>

    {{-- ── Tabel Detail Pinjaman Dicairkan ──────────────────── --}}
    <div class="col-12">
      <div class="form-card p-0 overflow-hidden">
        <div class="d-flex align-items-center gap-2 px-4 py-3 border-bottom">
          <i class="bi bi-cash-stack text-warning"></i>
          <h6 class="fw-700 mb-0 is-5ca88772">Pinjaman Dicairkan</h6>
          <span class="badge bg-warning text-dark ms-auto">{{ $periodeLabel }}</span>
        </div>
        @if($detailPinjaman->isEmpty())
          <div class="text-center py-5 text-muted">
            <i class="bi bi-inbox fs-2 d-block mb-2"></i> Tidak ada pinjaman dicairkan pada periode ini.
          </div>
        @else
        <div class="table-responsive">
          <table class="table tabel-detail mb-0">
            <thead>
              <tr>
                <th>Tgl Persetujuan</th>
                <th>No. Pinjaman</th>
                <th>Anggota</th>
                <th class="text-end">Jumlah</th>
                <th class="text-center">Jangka</th>
                <th class="text-end">Bunga/Bln</th>
                <th class="text-end">Cicilan/Bln</th>
              </tr>
            </thead>
            <tbody>
              @foreach($detailPinjaman as $p)
              <tr>
                <td class="text-muted">{{ $p->tanggal_persetujuan?->format('d M Y') ?? '—' }}</td>
                <td class="small text-muted">{{ $p->nomor_pinjaman }}</td>
                <td class="fw-600">{{ $p->anggota->nama }}</td>
                <td class="text-end fw-700 text-danger">Rp {{ number_format($p->jumlah_pinjaman, 0, ',', '.') }}</td>
                <td class="text-center">{{ $p->jangka_waktu }} bln</td>
                <td class="text-end text-muted">{{ $p->bunga }}%</td>
                <td class="text-end">Rp {{ number_format($p->cicilan_per_bulan ?? 0, 0, ',', '.') }}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        @endif
      </div>
    </div>

  </div>{{-- /row --}}

</div>{{-- /col-12 --}}
</div>{{-- /row --}}

@endsection