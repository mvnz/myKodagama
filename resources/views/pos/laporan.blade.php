@extends('layouts.pos')
@section('judul','Laporan Penjualan')
@section('breadcrumb')<li class="breadcrumb-item active">Laporan</li>@endsection

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
})();
</script>
@endpush
