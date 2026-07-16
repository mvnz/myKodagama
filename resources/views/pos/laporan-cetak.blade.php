<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  @php
    $namaUsaha = $profil['nama_koperasi'] ?? config('app.name');
    $subNama   = 'Point of Sale';
    $alamat    = $profil['alamat']   ?? '';
    $telepon   = $profil['telepon']  ?? '';
    $email     = $profil['email']    ?? '';
    $nomorLap  = 'LAP-POS/' . now()->format('Y/m/d');
  @endphp
  <title>Laporan Penjualan POS – {{ $periodeLabel }}</title>
  <link rel="stylesheet" href="{{ asset('css/pages/cetak.css') }}">
  
  <link rel="stylesheet" href="{{ asset('css/pages/inline-extracted.css') }}">
  <style>
    /* Compact mode khusus tabel breakdown supplier pada cetak laporan POS */
    .tabel-breakdown-compact {
      font-size: 9pt;
      table-layout: auto;
    }

    .tabel-breakdown-compact th,
    .tabel-breakdown-compact td {
      padding: 2px 4px;
      line-height: 1.2;
      vertical-align: top;
    }

    .tabel-breakdown-compact th {
      white-space: normal;
      word-break: break-word;
      font-size: 8.5pt;
      line-height: 1.15;
      text-align: center;
    }

    .tabel-breakdown-compact .td-no {
      width: 22px;
    }

    .tabel-breakdown-compact .col-supplier {
      width: 18%;
    }

    .tabel-breakdown-compact .col-nama {
      width: 22%;
      word-break: break-word;
    }

    .tabel-breakdown-compact .td-uang {
      white-space: nowrap;
      font-size: 8.5pt;
    }

    .tabel-breakdown-compact .supplier-summary-main {
      font-weight: 700;
      line-height: 1.2;
    }

    .tabel-breakdown-compact .supplier-summary-meta {
      font-size: 8pt;
      font-weight: 600;
      line-height: 1.15;
      margin-top: 1px;
      color: #222;
    }
  </style>
</head>
<body>

{{-- Toolbar (layar saja) --}}
<div class="no-print">
  <button class="btn-print" onclick="window.print()">&#128438; Cetak / Simpan PDF</button>
  <a class="btn-back" href="{{ route('pos.laporan', array_filter(['mode' => $mode, 'bulan' => $bulan, 'tahun' => $tahun, 'tanggal' => $tanggal, 'minggu' => $minggu])) }}">&#8592; Kembali</a>
  <span class="is-53f43043">Laporan Penjualan POS &mdash; {{ $periodeLabel }}</span>
</div>

<div class="page">

  {{-- KOP --}}
  <div class="kop">
    <div class="kop-logo">{{ strtoupper(substr($namaUsaha, 0, 2)) }}</div>
    <div class="kop-teks">
      <div class="nama-koperasi">{{ $namaUsaha }}</div>
      <div class="sub-nama">{{ $subNama }}</div>
      <div class="kop-kontak">
        @if($alamat){{ $alamat }}@endif
        @if($telepon) &nbsp;|&nbsp; Telp: {{ $telepon }}@endif
        @if($email) &nbsp;|&nbsp; Email: {{ $email }}@endif
      </div>
    </div>
  </div>
  <hr class="garis-kop">
  <hr class="garis-kop-tipis">

  {{-- JUDUL --}}
  <div class="judul-dokumen">
    <h2>Laporan Penjualan</h2>
    <p>Periode: {{ $periodeLabel }}</p>
  </div>

  {{-- INFO DOKUMEN --}}
  <div class="info-dokumen">
    <table>
      <tr><td>Nomor Laporan</td><td>:</td><td>{{ $nomorLap }}</td></tr>
      <tr><td>Tanggal Cetak</td><td>:</td><td>{{ now()->translatedFormat('d F Y, H:i') }}</td></tr>
      <tr><td>Periode</td><td>:</td><td>{{ $periodeLabel }} ({{ $dari }} s.d. {{ $sampai }})</td></tr>
    </table>
  </div>

  {{-- RINGKASAN --}}
  <div class="seksi-judul"><span>I.</span> Ringkasan Penjualan</div>
  <table class="tabel-laporan">
    <thead>
      <tr>
        <th class="td-no">No.</th>
        <th>Uraian</th>
        <th class="td-uang">Nilai</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td class="td-no">1</td>
        <td>Total Transaksi</td>
        <td class="td-uang">{{ number_format($totalTransaksi) }}</td>
      </tr>
      <tr>
        <td class="td-no">2</td>
        <td>Total Omzet</td>
        <td class="td-uang">Rp {{ number_format($totalOmzet, 0, ',', '.') }}</td>
      </tr>
      <tr>
        <td class="td-no">3</td>
        <td>Rata-rata / Periode</td>
        <td class="td-uang">Rp {{ number_format($rataRata, 0, ',', '.') }}</td>
      </tr>
    </tbody>
  </table>

  {{-- PERKEMBANGAN OMZET --}}
  <div class="seksi-judul"><span>II.</span> Perkembangan Omzet</div>
  <table class="tabel-laporan">
    <thead>
      <tr>
        <th class="td-no">No.</th>
        <th>Tanggal</th>
        <th class="td-uang">Jumlah Transaksi</th>
        <th class="td-uang">Omzet (Rp)</th>
      </tr>
    </thead>
    <tbody>
      @forelse($harian as $h)
      @php
        $fmtTgl = $mode === 'tahun'
          ? \Carbon\Carbon::parse($h->tanggal . '-01')->translatedFormat('M Y')
          : \Carbon\Carbon::parse($h->tanggal)->translatedFormat('d M Y');
      @endphp
      <tr>
        <td class="td-no">{{ $loop->iteration }}</td>
        <td>{{ $fmtTgl }}</td>
        <td class="td-uang">{{ number_format($h->jumlah) }}</td>
        <td class="td-uang">{{ number_format($h->omzet, 0, ',', '.') }}</td>
      </tr>
      @empty
      <tr><td colspan="4" class="is-2cef7be8">Tidak ada data pada periode ini.</td></tr>
      @endforelse
      @if($harian->isNotEmpty())
      <tr class="tr-total">
        <td colspan="2" class="is-ca22da47">Total</td>
        <td class="td-uang">{{ number_format($harian->sum('jumlah')) }}</td>
        <td class="td-uang">{{ number_format($harian->sum('omzet'), 0, ',', '.') }}</td>
      </tr>
      @endif
    </tbody>
  </table>

  {{-- TOP PRODUK --}}
  <div class="seksi-judul"><span>III.</span> Top 10 Produk Terlaris</div>
  <table class="tabel-laporan">
    <thead>
      <tr>
        <th class="td-no">No.</th>
        <th>Nama Produk</th>
        <th class="is-b82fad01">Qty Terjual</th>
        <th class="is-df7d1005">Omzet (Rp)</th>
      </tr>
    </thead>
    <tbody>
      @forelse($topProduk as $i => $tp)
      <tr>
        <td class="td-no">{{ $i + 1 }}</td>
        <td>{{ $tp->nama_produk }}</td>
        <td class="is-13076d95">{{ number_format($tp->total_qty) }}</td>
        <td class="td-uang">{{ number_format($tp->total_omzet, 0, ',', '.') }}</td>
      </tr>
      @empty
      <tr><td colspan="4" class="is-2cef7be8">Tidak ada data</td></tr>
      @endforelse
      @if($topProduk->isNotEmpty())
      <tr class="tr-total">
        <td colspan="2" class="is-ca22da47">Total (Top 10)</td>
        <td class="is-13076d95">{{ number_format($topProduk->sum('total_qty')) }}</td>
        <td class="td-uang">{{ number_format($topProduk->sum('total_omzet'), 0, ',', '.') }}</td>
      </tr>
      @endif
    </tbody>
  </table>

  {{-- BREAKDOWN SUPPLIER --}}
  <div class="seksi-judul"><span>IV.</span> Breakdown Penjualan per Supplier</div>
  <table class="tabel-laporan tabel-breakdown-compact">
    <colgroup>
      <col class="col-no">
      <col class="col-supplier">
      <col class="col-nama">
      <col>
      <col>
      <col>
      <col>
      <col>
      <col>
    </colgroup>
    <thead>
      <tr>
        <th class="td-no">No.</th>
        <th>Supplier</th>
        <th>Nama Barang</th>
        <th class="td-uang">Jumlah</th>
        <th class="td-uang">Harga Jual</th>
        <th class="td-uang">Total</th>
        <th class="td-uang">Pendapatan</th>
        <th class="td-uang">Profit</th>
        <th class="td-uang">Debet</th>
      </tr>
    </thead>
    <tbody>
      @php
        $groupedSupplier = $supplierBreakdown->groupBy('supplier_id');
        $grandTotal = (float) $supplierBreakdown->sum('total');
        $grandDebet = $groupedSupplier->sum(function ($rows) {
          return (float) ($rows->first()->total_debet ?? 0);
        });
        $no = 1;
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
        @endphp
        <tr class="tr-total">
          <td colspan="3" class="is-ca22da47">
            <div class="supplier-summary-main">{{ $supplierNama }}</div>
            <div class="supplier-summary-meta">{{ number_format($rows->count()) }} barang, kontribusi {{ number_format($supplierKontribusi, 1, ',', '.') }}%</div>
          </td>
          <td class="td-uang">{{ number_format($supplierQty) }}</td>
          <td class="td-uang">-</td>
          <td class="td-uang">{{ number_format($supplierTotal, 0, ',', '.') }}</td>
          <td class="td-uang">{{ number_format($supplierPendapatan, 0, ',', '.') }}</td>
          <td class="td-uang">{{ number_format($supplierProfit, 0, ',', '.') }} ({{ number_format($supplierMargin, 1, ',', '.') }}%)</td>
          <td class="td-uang">{{ number_format($supplierDebet, 0, ',', '.') }}</td>
        </tr>
        @foreach($rows as $sb)
        <tr>
          <td class="td-no">{{ $no }}</td>
          <td>-</td>
          <td>{{ $sb->nama_barang }}</td>
          <td class="td-uang">{{ number_format($sb->jumlah) }}</td>
          <td class="td-uang">{{ number_format($sb->harga_jual, 0, ',', '.') }}</td>
          <td class="td-uang">{{ number_format($sb->total, 0, ',', '.') }}</td>
          <td class="td-uang">{{ number_format($sb->total - $sb->profit, 0, ',', '.') }}</td>
          <td class="td-uang">{{ number_format($sb->profit, 0, ',', '.') }}</td>
          <td class="td-uang">-</td>
        </tr>
        @php $no++; @endphp
        @endforeach
      @empty
      <tr><td colspan="9" class="is-2cef7be8">Tidak ada data supplier</td></tr>
      @endforelse
      @if($supplierBreakdown->isNotEmpty())
      <tr class="tr-total">
        <td colspan="3" class="is-ca22da47">Total</td>
        <td class="td-uang">{{ number_format($supplierBreakdown->sum('jumlah')) }}</td>
        <td class="td-uang">-</td>
        <td class="td-uang">{{ number_format($supplierBreakdown->sum('total'), 0, ',', '.') }}</td>
        <td class="td-uang">{{ number_format($supplierBreakdown->sum('total') - $supplierBreakdown->sum('profit'), 0, ',', '.') }}</td>
        <td class="td-uang">{{ number_format($supplierBreakdown->sum('profit'), 0, ',', '.') }}</td>
        <td class="td-uang">{{ number_format($grandDebet, 0, ',', '.') }}</td>
      </tr>
      @endif
    </tbody>
  </table>

  {{-- DETAIL TRANSAKSI --}}
  <div class="seksi-judul"><span>V.</span> Detail Transaksi</div>
  <table class="tabel-laporan">
    <thead>
      <tr>
        <th class="td-no">No.</th>
        <th class="is-a0e8d5e8">Nomor</th>
        <th>Tanggal</th>
        <th>Kasir</th>
        <th>Metode</th>
        <th>Diskon (Rp)</th>
        <th>Total (Rp)</th>
      </tr>
    </thead>
    <tbody>
      @forelse($transaksiDetail as $i => $t)
      <tr>
        <td class="td-no">{{ $i + 1 }}</td>
        <td class="is-a0e8d5e8">{{ $t->nomor }}</td>
        <td>{{ $t->tanggal->translatedFormat('d M Y') }}</td>
        <td>{{ $t->kasir?->name ?? '-' }}</td>
        <td class="is-13076d95">{{ strtoupper($t->metode_bayar) }}</td>
        <td class="td-uang">{{ $t->diskon > 0 ? number_format($t->diskon, 0, ',', '.') : '-' }}</td>
        <td class="td-uang">{{ number_format($t->total, 0, ',', '.') }}</td>
      </tr>
      @empty
      <tr><td colspan="7" class="is-2cef7be8">Tidak ada transaksi</td></tr>
      @endforelse
      @if($transaksiDetail->isNotEmpty())
      <tr class="tr-total">
        <td colspan="5" class="is-ca22da47">Total</td>
        <td class="td-uang">{{ number_format($transaksiDetail->sum('diskon'), 0, ',', '.') }}</td>
        <td class="td-uang">{{ number_format($transaksiDetail->sum('total'), 0, ',', '.') }}</td>
      </tr>
      @endif
    </tbody>
  </table>

  {{-- TANDA TANGAN --}}
  <div class="ttd-area is-062d4a88">
    <div class="ttd-box">
      <div>Mengetahui,</div>
      <div>Pimpinan</div>
      <div class="ttd-garis">( _______________________ )</div>
    </div>
    <div class="ttd-box">
      <div>{{ now()->translatedFormat('d F Y') }}</div>
      <div>Operator POS</div>
      <div class="ttd-garis">( {{ auth()->user()->name }} )</div>
    </div>
  </div>

  {{-- Footer --}}
  <div class="footer-cetak is-42f3251c">
    <span>{{ $namaUsaha }} — Laporan Penjualan Periode {{ $periodeLabel }}</span>
    <span>Dicetak: {{ now()->translatedFormat('d F Y, H:i') }}</span>
  </div>

</div>{{-- /page --}}
</body>
</html>
