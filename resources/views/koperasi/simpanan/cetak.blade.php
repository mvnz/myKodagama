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
    $labelJenis   = ['pokok' => 'Pokok', 'wajib' => 'Wajib', 'sukarela' => 'Sukarela'];
    $filterLabel  = $jenis ? ('Jenis: ' . ($labelJenis[$jenis] ?? $jenis)) : 'Semua Jenis';
    if ($cari) $filterLabel .= ' | Cari: "' . $cari . '"';
  @endphp
  <title>Laporan Simpanan – {{ $namaKoperasi }}</title>
  <link rel="stylesheet" href="{{ asset('css/pages/cetak.css') }}">
  <link rel="stylesheet" href="{{ asset('css/pages/inline-extracted.css') }}">
</head>
<body>

  <div class="no-print">
    <button class="btn-print" onclick="window.print()">&#128438; Cetak / Simpan PDF</button>
    <a class="btn-back" href="{{ route('simpanan.index', array_filter(['cari' => $cari, 'jenis' => $jenis])) }}">&#8592; Kembali</a>
    <span class="is-53f43043">Laporan Simpanan &mdash; {{ $filterLabel }}</span>
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
      <h2>Laporan Simpanan</h2>
      <p>{{ $filterLabel }}</p>
    </div>

    <div class="info-dokumen">
      <table>
        <tr><td>Tanggal Cetak</td><td>:</td><td>{{ now()->translatedFormat('d F Y') }}</td></tr>
        <tr><td>Jumlah Transaksi</td><td>:</td><td>{{ $simpanan->count() }} transaksi</td></tr>
        <tr><td>Total</td><td>:</td><td>Rp {{ number_format($total, 0, ',', '.') }}</td></tr>
      </table>
    </div>

    <table class="tabel-laporan">
      <thead>
        <tr>
          <th class="td-no">No.</th>
          <th>Tanggal</th>
          <th>No. Anggota</th>
          <th>Nama Anggota</th>
          <th>Jenis Simpanan</th>
          <th>Transaksi</th>
          <th class="td-uang">Jumlah (Rp)</th>
          <th>Keterangan</th>
        </tr>
      </thead>
      <tbody>
        @forelse($simpanan as $i => $row)
        <tr>
          <td class="td-no">{{ $i + 1 }}</td>
          <td>{{ $row->tanggal ? \Illuminate\Support\Carbon::parse($row->tanggal)->translatedFormat('d M Y') : '-' }}</td>
          <td>{{ $row->anggota->nomor_anggota ?? '-' }}</td>
          <td>{{ $row->anggota->nama ?? '-' }}</td>
          <td>{{ ucfirst($row->jenis_simpanan) }}</td>
          <td>
            @if(($row->jenis_transaksi ?? 'setor') === 'tarik')
              <span class="badge-s badge-tarik">Penarikan</span>
            @else
              <span class="badge-s badge-setor">Setoran</span>
            @endif
          </td>
          <td class="td-uang">{{ number_format($row->jumlah, 0, ',', '.') }}</td>
          <td class="is-24ddd604">{{ $row->keterangan ?? '-' }}</td>
        </tr>
        @empty
        <tr><td colspan="8" class="is-4ab8a655">Tidak ada data</td></tr>
        @endforelse
        <tr class="tr-total">
          <td colspan="6" class="is-a931e0d5">Total Simpanan</td>
          <td class="td-uang">{{ number_format($total, 0, ',', '.') }}</td>
          <td></td>
        </tr>
      </tbody>
    </table>

    <div class="footer-cetak">
      <span>{{ $namaKoperasi }} &mdash; Dicetak {{ now()->translatedFormat('d F Y, H:i') }}</span>
      <span>Laporan Simpanan | {{ $filterLabel }}</span>
    </div>

  </div>

</body>
</html>