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
  <div class="seksi">I. Ringkasan Penjualan</div>
  <div class="summary-grid">
    <div class="summary-box">
      <div class="lbl">Total Transaksi</div>
      <div class="val">{{ number_format($totalTransaksi) }}</div>
    </div>
    <div class="summary-box">
      <div class="lbl">Total Omzet</div>
      <div class="val is-b7c56e9c">Rp {{ number_format($totalOmzet, 0, ',', '.') }}</div>
    </div>
    <div class="summary-box">
      <div class="lbl">Rata-rata / Periode</div>
      <div class="val is-b7c56e9c">Rp {{ number_format($rataRata, 0, ',', '.') }}</div>
    </div>
  </div>

  {{-- GRAFIK --}}
  <div class="seksi">II. Grafik Omzet</div>
  @if($harian->isEmpty())
    <p class="is-0fde3b4c">Tidak ada data pada periode ini.</p>
  @else
  @php
    $maxOmzet = $harian->max('omzet') ?: 1;
    $fmtTgl = fn($d) => $mode === 'tahun'
      ? \Carbon\Carbon::parse($d . '-01')->translatedFormat('M Y')
      : \Carbon\Carbon::parse($d)->translatedFormat('d M');
  @endphp
  <div class="grafik-bar-wrap">
    @foreach($harian as $h)
    <div class="grafik-row">
      <span class="lbl">{{ $fmtTgl($h->tanggal) }}</span>
      <div class="bar-bg"><div class="bar is-ef1fcb97"></div></div>
      <span class="val">{{ number_format($h->omzet, 0, ',', '.') }}</span>
      <span class="qty">({{ $h->jumlah }})</span>
    </div>
    @endforeach
  </div>
  @endif

  {{-- TOP PRODUK --}}
  <div class="seksi">III. Top 10 Produk Terlaris</div>
  <table class="tabel-lap">
    <thead>
      <tr>
        <th class="td-no">No.</th>
        <th class="is-200a92a0">Nama Produk</th>
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

  {{-- DETAIL TRANSAKSI --}}
  <div class="seksi">IV. Detail Transaksi</div>
  <table class="tabel-lap">
    <thead>
      <tr>
        <th class="td-no">No.</th>
        <th>Nomor</th>
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
