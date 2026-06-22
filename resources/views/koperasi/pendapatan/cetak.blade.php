<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  @php
    $namaKoperasi = $profil['nama_koperasi'] ?? 'Koperasi';
    $subNama      = $profil['sub_nama']      ?? 'Simpan Pinjam';
    $alamat       = $profil['alamat']        ?? '';
    $telepon      = $profil['telepon']       ?? '';
    $email        = $profil['email']         ?? '';
    $namaBulan    = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $filterParts  = [];
    if ($tahun) $filterParts[] = 'Tahun: ' . $tahun;
    if ($bulan) $filterParts[] = 'Bulan: ' . ($namaBulan[(int)$bulan] ?? $bulan);
    if ($kategori) $filterParts[] = 'Kategori: ' . ($labelKategori[$kategori] ?? $kategori);
    $filterLabel = $filterParts ? implode(' | ', $filterParts) : 'Semua Pendapatan';
  @endphp
  <title>Laporan Pendapatan – {{ $namaKoperasi }}</title>
  <link rel="stylesheet" href="{{ asset('css/pages/cetak.css') }}">
  <link rel="stylesheet" href="{{ asset('css/pages/inline-extracted.css') }}">
</head>
<body>

  <div class="no-print">
    <button class="btn-print" onclick="window.print()">&#128438; Cetak / Simpan PDF</button>
    <a class="btn-back" href="{{ route('pendapatan.index', array_filter(['tahun' => $tahun, 'bulan' => $bulan, 'kategori' => $kategori])) }}">&#8592; Kembali</a>
    <span class="is-53f43043">Laporan Pendapatan &mdash; {{ $filterLabel }}</span>
  </div>

  <div class="page">

    <div class="kop">
      <div class="kop-logo">{{ strtoupper(substr($namaKoperasi, 0, 2)) }}</div>
      <div class="kop-teks">
        <div class="nama-koperasi">{{ $namaKoperasi }}</div>
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

    <div class="judul-dokumen">
      <h2>Laporan Pendapatan</h2>
      <p>{{ $filterLabel }}</p>
    </div>

    <div class="info-dokumen">
      <table>
        <tr><td>Tanggal Cetak</td><td>:</td><td>{{ now()->translatedFormat('d F Y') }}</td></tr>
        <tr><td>Jumlah Transaksi</td><td>:</td><td>{{ $pendapatan->count() }} transaksi</td></tr>
        <tr><td>Total Pendapatan</td><td>:</td><td>Rp {{ number_format($total, 0, ',', '.') }}</td></tr>
      </table>
    </div>

    <table class="tabel-laporan">
      <thead>
        <tr>
          <th class="td-no">No.</th>
          <th>Tanggal</th>
          <th>Judul</th>
          <th>Kategori</th>
          <th class="td-uang">Jumlah (Rp)</th>
          <th>Keterangan</th>
        </tr>
      </thead>
      <tbody>
        @forelse($pendapatan as $i => $row)
        <tr>
          <td class="td-no">{{ $i + 1 }}</td>
          <td>{{ \Illuminate\Support\Carbon::parse($row->tanggal)->translatedFormat('d M Y') }}</td>
          <td>{{ $row->judul }}</td>
          <td>{{ $labelKategori[$row->kategori] ?? $row->kategori }}</td>
          <td class="td-uang">{{ number_format($row->jumlah, 0, ',', '.') }}</td>
          <td class="is-24ddd604">{{ $row->keterangan ?? '-' }}</td>
        </tr>
        @empty
        <tr><td colspan="6" class="is-4ab8a655">Tidak ada data</td></tr>
        @endforelse
        <tr class="tr-total">
          <td colspan="4" class="is-a931e0d5">Total Pendapatan</td>
          <td class="td-uang">{{ number_format($total, 0, ',', '.') }}</td>
          <td></td>
        </tr>
      </tbody>
    </table>

    <div class="footer-cetak">
      <span>{{ $namaKoperasi }} &mdash; Dicetak {{ now()->translatedFormat('d F Y, H:i') }}</span>
      <span>Laporan Pendapatan | {{ $filterLabel }}</span>
    </div>

  </div>

</body>
</html>