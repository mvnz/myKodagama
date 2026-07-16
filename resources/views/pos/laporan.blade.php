@extends('layouts.pos')
@section('judul','Laporan Penjualan')
@section('breadcrumb')<li class="breadcrumb-item active">Laporan</li>@endsection

@push('css')
<style>
  .supplier-toggle-btn {
    background: transparent;
    border: 0;
    color: #1f2937;
    font-weight: 700;
    font-size: 0.95rem;
    padding: 2px 6px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    line-height: 1.2;
  }

  .supplier-toggle-btn:hover {
    background: #eef2f7;
    color: #111827;
  }

  .supplier-toggle-btn:focus-visible {
    outline: 2px solid #7aa2ff;
    outline-offset: 1px;
  }

  .supplier-badge-items {
    background: #475569 !important;
    color: #fff !important;
  }

  .supplier-badge-share {
    background: #0ea5e9 !important;
    color: #fff !important;
  }
</style>
@endpush

@section('konten')
{{-- Page Banner --}}
<div class="dashboard-welcome mb-4 is-5d93e480">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
      <div class="welcome-label mb-1">Manajemen POS</div>
      <h4 class="mb-1 fw-700">Laporan Penjualan</h4>
      <p class="mb-0 welcome-date"><i class="bi bi-calendar3 me-1"></i>{{ now()->translatedFormat('l, d F Y') }}</p>
    </div>
    <div class="welcome-icon-wrap"><i class="bi bi-bar-chart-fill"></i></div>
  </div>
</div>

{{-- Filter --}}
<div class="form-card mb-4">
  <form method="GET" action="{{ url()->current() }}" class="row g-3 align-items-end" id="laporanForm">
    <div class="col-12">
      <label class="form-label">Periode</label>
      <div class="d-flex gap-2">
        @foreach(['hari' => 'Hari', 'minggu' => 'Minggu', 'bulan' => 'Bulan', 'tahun' => 'Tahun'] as $m => $lbl)
        <button type="button"
          class="btn btn-sm {{ $mode === $m ? 'btn-warning text-white fw-semibold' : 'btn-outline-secondary' }} mode-btn"
          data-mode="{{ $m }}">{{ $lbl }}</button>
        @endforeach
      </div>
      <input type="hidden" name="mode" id="modeInput" value="{{ $mode }}">
    </div>
    <div class="col-md-4" id="inputHari" style="{{ $mode === 'hari' ? '' : 'display:none;' }}">
      <label class="form-label">Tanggal</label>
      <input type="date" name="tanggal" value="{{ $tanggal }}" class="form-control">
    </div>
    <div class="col-md-4" id="inputMinggu" style="{{ $mode === 'minggu' ? '' : 'display:none;' }}">
      <label class="form-label">Minggu</label>
      <input type="week" name="minggu" value="{{ $minggu }}" class="form-control">
    </div>
    <div class="col-md-4" id="inputBulan" style="{{ $mode === 'bulan' ? '' : 'display:none;' }}">
      <label class="form-label">Bulan</label>
      <input type="month" name="bulan" value="{{ $bulan }}" class="form-control">
    </div>
    <div class="col-md-4" id="inputTahun" style="{{ $mode === 'tahun' ? '' : 'display:none;' }}">
      <label class="form-label">Tahun</label>
      <select name="tahun" class="form-select">
        @for($y = now()->year; $y >= now()->year - 5; $y--)
        <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
        @endfor
      </select>
    </div>
    <div class="col-md-auto d-flex gap-2 align-items-end">
      <button type="submit" class="btn btn-primary">
        <i class="bi bi-funnel me-1"></i> Tampilkan
      </button>
      <a id="btnCetak" href="#" target="_blank" class="btn btn-outline-secondary">
        <i class="bi bi-printer me-1"></i> Cetak / PDF
      </a>
    </div>
  </form>
</div>

{{-- Stat Cards --}}
<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="kstat-card kstat-amber">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <i class="kstat-icon bi bi-receipt-cutoff"></i>
      <div class="kstat-value kstat-value-sm">{{ number_format($totalTransaksi) }}</div>
      <div class="kstat-label">Total Transaksi</div>
      <div class="kstat-trend"><i class="bi bi-receipt me-1"></i> Periode ini</div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="kstat-card kstat-green">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <i class="kstat-icon bi bi-cash-coin"></i>
      <div class="kstat-value kstat-value-sm">Rp {{ number_format($totalOmzet, 0, ',', '.') }}</div>
      <div class="kstat-label">Total Omzet</div>
      <div class="kstat-trend"><i class="bi bi-graph-up me-1"></i> Pendapatan kotor</div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="kstat-card kstat-teal">
      <div class="kstat-deco"></div><div class="kstat-deco2"></div>
      <i class="kstat-icon bi bi-graph-up"></i>
      <div class="kstat-value kstat-value-sm">Rp {{ number_format($rataRata, 0, ',', '.') }}</div>
      <div class="kstat-label">Rata-rata / {{ $rataRataLabel == 'Per Hari' ? 'Hari' : ($rataRataLabel == 'Per Bulan' ? 'Bulan' : 'Transaksi') }}</div>
      <div class="kstat-trend"><i class="bi bi-calendar-day me-1"></i> {{ $rataRataLabel }}</div>
    </div>
  </div>
</div>

<div class="row g-4">
  {{-- Grafik harian --}}
  <div class="col-lg-8">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center">
        <h6 class="mb-0 fw-700 is-feb6723d"><i class="bi bi-bar-chart me-2 is-f571fe9b"></i>{{ $grafikLabel }}</h6>
      </div>
      <div class="card-body">
        @if($harian->isEmpty())
        <div class="text-center text-muted py-4"><i class="bi bi-inbox is-a4ea9cc4"></i><div class="mt-2">Tidak ada data transaksi bulan ini</div></div>
        @else
        <div class="pos-chart-scroll">
          <div class="pos-chart-list">
          @php
            $maxOmzet = $harian->max('omzet') ?: 1;
            $fmtTgl = fn($d) => $mode === 'tahun'
              ? \Carbon\Carbon::parse($d . '-01')->translatedFormat('M Y')
              : \Carbon\Carbon::parse($d)->translatedFormat('d M');
          @endphp
            @foreach($harian as $h)
            <div class="d-flex align-items-center gap-2 mb-2">
              <span class="pos-chart-date">{{ $fmtTgl($h->tanggal) }}</span>
              <div class="flex-fill pos-chart-bar-bg">
                <div class="pos-chart-bar-fill" style="width: {{ round(($h->omzet / $maxOmzet) * 100, 2) }}%"></div>
              </div>
              <span class="pos-chart-omzet">{{ number_format($h->omzet,0,',','.') }}</span>
              <span class="pos-chart-count">({{ $h->jumlah }})</span>
            </div>
            @endforeach
          </div>
        </div>
        @endif
      </div>
    </div>
  </div>

  {{-- Top Produk --}}
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center">
        <h6 class="mb-0 fw-700 is-feb6723d"><i class="bi bi-trophy me-2 is-f571fe9b"></i>Top 10 Produk</h6>
      </div>
      <div class="table-responsive">
        <table class="table tabel-koperasi mb-0">
          <thead class="is-98f358ac">
            <tr><th>Produk</th><th class="text-center">Qty</th><th class="text-end">Omzet</th></tr>
          </thead>
          <tbody>
            @forelse($topProduk as $tp)
            <tr>
              <td class="is-9ee0bb07">{{ $tp->nama_produk }}</td>
              <td class="text-center"><span class="badge bg-primary-subtle text-primary">{{ $tp->total_qty }}</span></td>
              <td class="text-end fw-600 small is-feb6723d">{{ number_format($tp->total_omzet,0,',','.') }}</td>
            </tr>
            @empty
            <tr><td colspan="3" class="text-center text-muted py-3">Tidak ada data</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<div class="row g-4 mt-1">
  <div class="col-12">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center">
        <h6 class="mb-0 fw-700 is-feb6723d"><i class="bi bi-building me-2 is-f571fe9b"></i>Breakdown Penjualan per Supplier</h6>
      </div>
      <div class="table-responsive">
        <table class="table tabel-koperasi mb-0">
          <thead class="is-98f358ac">
            <tr>
              <th>Supplier</th>
              <th>Nama Barang</th>
              <th class="text-center">Jumlah</th>
              <th class="text-end">Harga Jual</th>
              <th class="text-end">Total</th>
              <th class="text-end">Pendapatan Supplier</th>
              <th class="text-end">Profit</th>
              <th class="text-end">Sudah Debet</th>
            </tr>
          </thead>
          @php
            $groupedSupplier = $supplierBreakdown->groupBy('supplier_id');
            $grandTotal = (float) $supplierBreakdown->sum('total');
            $grandDebet = $groupedSupplier->sum(function ($rows) {
              return (float) ($rows->first()->total_debet ?? 0);
            });
          @endphp
          @forelse($groupedSupplier as $supplierId => $rows)
            @php
              $supplierNama = $rows->first()->supplier_nama ?? 'Tanpa Supplier';
              $supplierQty = (float) $rows->sum('jumlah');
              $supplierTotal = (float) $rows->sum('total');
              $supplierProfit = (float) $rows->sum('profit');
              $supplierPendapatan = $supplierTotal - $supplierProfit;
              $supplierDebet = (float) ($rows->first()->total_debet ?? 0);
              $supplierMargin = $supplierTotal > 0 ? ($supplierProfit / $supplierTotal) * 100 : 0;
              $supplierKontribusi = $grandTotal > 0 ? ($supplierTotal / $grandTotal) * 100 : 0;
              $collapseId = 'supplierBreakdown' . $loop->iteration;
            @endphp
            <tbody>
              <tr class="table-light fw-semibold">
                <td colspan="2">
                  <button
                    class="supplier-toggle-btn"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#{{ $collapseId }}"
                    aria-expanded="true"
                    aria-controls="{{ $collapseId }}"
                  >
                    <i class="bi bi-chevron-down me-1"></i>{{ $supplierNama }}
                  </button>
                  <span class="badge supplier-badge-items ms-2">{{ number_format($rows->count()) }} barang</span>
                  <span class="badge supplier-badge-share ms-1">Kontribusi {{ number_format($supplierKontribusi, 1, ',', '.') }}%</span>
                </td>
                <td class="text-center">{{ number_format($supplierQty) }}</td>
                <td class="text-end">-</td>
                <td class="text-end">Rp {{ number_format($supplierTotal, 0, ',', '.') }}</td>
                <td class="text-end">Rp {{ number_format($supplierPendapatan, 0, ',', '.') }}</td>
                <td class="text-end">Rp {{ number_format($supplierProfit, 0, ',', '.') }} <span class="text-muted">({{ number_format($supplierMargin, 1, ',', '.') }}%)</span></td>
                <td class="text-end">Rp {{ number_format($supplierDebet, 0, ',', '.') }}</td>
              </tr>
            </tbody>
            <tbody id="{{ $collapseId }}" class="collapse show">
              @foreach($rows as $sb)
              <tr>
                <td class="text-muted">↳</td>
                <td class="small">{{ $sb->nama_barang }}</td>
                <td class="text-center">{{ number_format($sb->jumlah) }}</td>
                <td class="text-end">Rp {{ number_format($sb->harga_jual, 0, ',', '.') }}</td>
                <td class="text-end fw-600 is-feb6723d">Rp {{ number_format($sb->total, 0, ',', '.') }}</td>
                <td class="text-end">Rp {{ number_format($sb->total - $sb->profit, 0, ',', '.') }}</td>
                <td class="text-end">Rp {{ number_format($sb->profit, 0, ',', '.') }}</td>
                <td class="text-end">-</td>
              </tr>
              @endforeach
            </tbody>
          @empty
            <tbody>
              <tr><td colspan="8" class="text-center text-muted py-3">Tidak ada data supplier pada periode ini</td></tr>
            </tbody>
          @endforelse
          @if($supplierBreakdown->isNotEmpty())
          <tbody>
            <tr class="fw-bold">
              <td colspan="2">Total</td>
              <td class="text-center">{{ number_format($supplierBreakdown->sum('jumlah')) }}</td>
              <td class="text-end">-</td>
              <td class="text-end">Rp {{ number_format($supplierBreakdown->sum('total'), 0, ',', '.') }}</td>
              <td class="text-end">Rp {{ number_format($supplierBreakdown->sum('total') - $supplierBreakdown->sum('profit'), 0, ',', '.') }}</td>
              <td class="text-end">Rp {{ number_format($supplierBreakdown->sum('profit'), 0, ',', '.') }}</td>
              <td class="text-end">Rp {{ number_format($grandDebet, 0, ',', '.') }}</td>
            </tr>
          </tbody>
          @endif
        </table>
      </div>
    </div>
  </div>
</div>
@endsection

@push('js')
<script>
(function() {
  var cetakBase = '{{ route('pos.laporan.cetak') }}';

  function syncCetakUrl() {
    var form = document.getElementById('laporanForm');
    var params = new URLSearchParams(new FormData(form));
    document.getElementById('btnCetak').href = cetakBase + '?' + params.toString();
  }

  // Sync on page load
  syncCetakUrl();

  // Sync whenever any input changes
  document.getElementById('laporanForm').addEventListener('input', syncCetakUrl);

  document.querySelectorAll('.mode-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var mode = this.dataset.mode;
      document.getElementById('modeInput').value = mode;
      ['Hari','Minggu','Bulan','Tahun'].forEach(function(m) {
        var el = document.getElementById('input' + m);
        if (el) el.style.display = m.toLowerCase() === mode ? '' : 'none';
      });
      document.querySelectorAll('.mode-btn').forEach(function(b) {
        b.classList.remove('btn-warning','text-white','fw-semibold');
        b.classList.add('btn-outline-secondary');
      });
      this.classList.remove('btn-outline-secondary');
      this.classList.add('btn-warning','text-white','fw-semibold');
      syncCetakUrl();
    });
  });

  document.querySelectorAll('[id^="supplierBreakdown"]').forEach(function(el) {
    el.addEventListener('shown.bs.collapse', function() {
      var trigger = document.querySelector('[data-bs-target="#' + el.id + '"] i.bi');
      if (!trigger) return;
      trigger.classList.remove('bi-chevron-right');
      trigger.classList.add('bi-chevron-down');
    });

    el.addEventListener('hidden.bs.collapse', function() {
      var trigger = document.querySelector('[data-bs-target="#' + el.id + '"] i.bi');
      if (!trigger) return;
      trigger.classList.remove('bi-chevron-down');
      trigger.classList.add('bi-chevron-right');
    });
  });
})();
</script>
@endpush
