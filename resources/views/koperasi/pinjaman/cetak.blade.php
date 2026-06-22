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
    $labelStatus  = ['diajukan' => 'Diajukan', 'diverifikasi' => 'Diverifikasi', 'disetujui' => 'Disetujui', 'lunas' => 'Lunas', 'ditolak' => 'Ditolak'];
    $filterLabel  = $status ? ('Status: ' . ($labelStatus[$status] ?? $status)) : 'Semua Status';
    if ($cari) $filterLabel .= ' | Cari: "' . $cari . '"';
  @endphp
  <title>Daftar Pinjaman – {{ $namaKoperasi }}</title>
  <link rel="stylesheet" href="{{ asset('css/pages/cetak.css') }}">
  
  <link rel="stylesheet" href="{{ asset('css/pages/inline-extracted.css') }}">
</head>
<body>

  <div class="no-print">
    <button class="btn-print" onclick="window.print()">&#128438; Cetak / Simpan PDF</button>
    <a class="btn-back" href="{{ route('pinjaman.index', array_filter(['cari' => $cari, 'status' => $status])) }}">&#8592; Kembali</a>
    <span class="is-53f43043">Daftar Pinjaman &mdash; {{ $filterLabel }}</span>
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
      <h2>Daftar Pinjaman Anggota</h2>
      <p>{{ $filterLabel }}</p>
    </div>

    <div class="info-dokumen">
      <table>
        <tr><td>Tanggal Cetak</td><td>:</td><td>{{ now()->translatedFormat('d F Y') }}</td></tr>
        <tr><td>Jumlah Pinjaman</td><td>:</td><td>{{ $pinjaman->count() }} pinjaman</td></tr>
        <tr><td>Total Nilai</td><td>:</td><td>Rp {{ number_format($totalPinjaman, 0, ',', '.') }}</td></tr>
      </table>
    </div>

    <table class="tabel-laporan">
      <thead>
        <tr>
          <th class="td-no">No.</th>
          <th>No. Pinjaman</th>
          <th>Nama Anggota</th>
          <th class="td-uang">Jumlah Pinjaman (Rp)</th>
          <th>Jangka</th>
          <th class="td-uang">Cicilan/Bln (Rp)</th>
          <th>Status</th>
          <th>Tanggal</th>
        </tr>
      </thead>
      <tbody>
        @forelse($pinjaman as $i => $row)
        <tr>
          <td class="td-no">{{ $i + 1 }}</td>
          <td class="is-24ddd604">{{ $row->nomor_pinjaman }}</td>
          <td>{{ $row->anggota->nama ?? '-' }}</td>
          <td class="td-uang">{{ number_format($row->jumlah_pinjaman, 0, ',', '.') }}</td>
          <td class="is-13076d95">{{ $row->jangka_waktu }} bln</td>
          <td class="td-uang">{{ $row->cicilan_per_bulan ? number_format($row->cicilan_per_bulan, 0, ',', '.') : '-' }}</td>
          <td><span class="badge-s badge-{{ $row->status }}">{{ $labelStatus[$row->status] ?? $row->status }}</span></td>
          <td>{{ $row->created_at ? $row->created_at->translatedFormat('d M Y') : '-' }}</td>
        </tr>
        @empty
        <tr><td colspan="8" class="is-4ab8a655">Tidak ada data</td></tr>
        @endforelse
        <tr class="tr-total">
          <td colspan="3" class="is-a931e0d5">Total Nilai Pinjaman</td>
          <td class="td-uang">{{ number_format($totalPinjaman, 0, ',', '.') }}</td>
          <td colspan="4"></td>
        </tr>
      </tbody>
    </table>

    <div class="footer-cetak">
      <span>{{ $namaKoperasi }} &mdash; Dicetak {{ now()->translatedFormat('d F Y, H:i') }}</span>
      <span>Daftar Pinjaman | {{ $filterLabel }}</span>
    </div>

  </div>

</body>
</html>