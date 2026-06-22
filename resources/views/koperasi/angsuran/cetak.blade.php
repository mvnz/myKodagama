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
    $labelStatus  = ['sudah' => 'Lunas', 'belum' => 'Belum'];
    $labelJatuhTempo = ['hari_ini' => 'Hari Ini', 'bulan_ini' => 'Bulan Ini', 'terlambat' => 'Terlambat'];
    $filterParts = [];
    if ($status) $filterParts[] = 'Status: ' . ($labelStatus[$status] ?? $status);
    if ($jatuhTempo) $filterParts[] = 'Jatuh Tempo: ' . ($labelJatuhTempo[$jatuhTempo] ?? $jatuhTempo);
    if ($cari) $filterParts[] = 'Cari: "' . $cari . '"';
    $filterLabel = $filterParts ? implode(' | ', $filterParts) : 'Semua Angsuran';
  @endphp
  <title>Laporan Angsuran – {{ $namaKoperasi }}</title>
  <link rel="stylesheet" href="{{ asset('css/pages/cetak.css') }}">
  
  <link rel="stylesheet" href="{{ asset('css/pages/inline-extracted.css') }}">
</head>
<body>

  <div class="no-print">
    <button class="btn-print" onclick="window.print()">&#128438; Cetak / Simpan PDF</button>
    <a class="btn-back" href="{{ route('angsuran.index', array_filter(['cari' => $cari, 'status' => $status, 'jatuh_tempo' => $jatuhTempo])) }}">&#8592; Kembali</a>
    <span class="is-53f43043">Laporan Angsuran &mdash; {{ $filterLabel }}</span>
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
      <h2>Laporan Angsuran Pinjaman</h2>
      <p>{{ $filterLabel }}</p>
    </div>

    <div class="info-dokumen">
      <table>
        <tr><td>Tanggal Cetak</td><td>:</td><td>{{ now()->translatedFormat('d F Y') }}</td></tr>
        <tr><td>Jumlah Data</td><td>:</td><td>{{ $angsuran->count() }} angsuran</td></tr>
        <tr><td>Total Sudah Dibayar</td><td>:</td><td>Rp {{ number_format($totalBayar, 0, ',', '.') }}</td></tr>
      </table>
    </div>

    <table class="tabel-laporan">
      <thead>
        <tr>
          <th class="td-no">No.</th>
          <th>No. Pinjaman</th>
          <th>Anggota</th>
          <th class="is-342efe44">Ke-</th>
          <th class="td-uang">Pokok (Rp)</th>
          <th class="td-uang">Bunga (Rp)</th>
          <th class="td-uang">Denda (Rp)</th>
          <th class="td-uang">Total (Rp)</th>
          <th>Jatuh Tempo</th>
          <th>Tgl Bayar</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        @forelse($angsuran as $i => $row)
        @php
          $terlambat = $row->status === 'belum' && $row->tanggal_jatuh_tempo && $row->tanggal_jatuh_tempo->lt(today());
        @endphp
        <tr>
          <td class="td-no">{{ $i + 1 }}</td>
          <td class="is-a0e8d5e8">{{ $row->pinjaman->nomor_pinjaman ?? '-' }}</td>
          <td>{{ $row->pinjaman->anggota->nama ?? '-' }}</td>
          <td class="is-13076d95">{{ $row->ke }}</td>
          <td class="td-uang">{{ number_format($row->pokok, 0, ',', '.') }}</td>
          <td class="td-uang">{{ number_format($row->bunga, 0, ',', '.') }}</td>
          <td class="td-uang">{{ $row->denda ? number_format($row->denda, 0, ',', '.') : '-' }}</td>
          <td class="td-uang">{{ number_format($row->jumlah_angsuran, 0, ',', '.') }}</td>
          <td>{{ $row->tanggal_jatuh_tempo ? $row->tanggal_jatuh_tempo->translatedFormat('d M Y') : '-' }}</td>
          <td>{{ $row->tanggal_bayar ? \Illuminate\Support\Carbon::parse($row->tanggal_bayar)->translatedFormat('d M Y') : '-' }}</td>
          <td>
            @if($terlambat)
              <span class="badge-s badge-terlambat">Terlambat</span>
            @else
              <span class="badge-s badge-{{ $row->status }}">{{ $row->status === 'sudah' ? 'Lunas' : 'Belum' }}</span>
            @endif
          </td>
        </tr>
        @empty
        <tr><td colspan="11" class="is-4ab8a655">Tidak ada data</td></tr>
        @endforelse
        <tr class="tr-total">
          <td colspan="4" class="is-c4077ec5">Total</td>
          <td class="td-uang">{{ number_format($totalPokok, 0, ',', '.') }}</td>
          <td class="td-uang">{{ number_format($totalBunga, 0, ',', '.') }}</td>
          <td class="td-uang">{{ number_format($totalDenda, 0, ',', '.') }}</td>
          <td class="td-uang">{{ number_format($totalPokok + $totalBunga + $totalDenda, 0, ',', '.') }}</td>
          <td colspan="3"></td>
        </tr>
      </tbody>
    </table>

    <div class="footer-cetak">
      <span>{{ $namaKoperasi }} &mdash; Dicetak {{ now()->translatedFormat('d F Y, H:i') }}</span>
      <span>Laporan Angsuran | {{ $filterLabel }}</span>
    </div>

  </div>

</body>
</html>