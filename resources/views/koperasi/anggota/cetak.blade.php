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
    $labelStatus  = ['aktif' => 'Aktif', 'tidak_aktif' => 'Tidak Aktif', 'menunggu' => 'Menunggu'];
    $filterLabel  = $status ? ('Status: ' . ($labelStatus[$status] ?? $status)) : 'Semua Status';
    if ($cari) $filterLabel .= ' | Cari: "' . $cari . '"';
  @endphp
  <title>Daftar Anggota – {{ $namaKoperasi }}</title>
  <link rel="stylesheet" href="{{ asset('css/pages/cetak.css') }}">
  
  <link rel="stylesheet" href="{{ asset('css/pages/inline-extracted.css') }}">
</head>
<body>

  <div class="no-print">
    <button class="btn-print" onclick="window.print()">&#128438; Cetak / Simpan PDF</button>
    <a class="btn-back" href="{{ route('anggota.index', array_filter(['cari' => $cari, 'status' => $status])) }}">&#8592; Kembali</a>
    <span class="is-53f43043">Daftar Anggota &mdash; {{ $filterLabel }}</span>
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
      <h2>Daftar Anggota</h2>
      <p>{{ $filterLabel }}</p>
    </div>

    <div class="info-dokumen">
      <table>
        <tr><td>Tanggal Cetak</td><td>:</td><td>{{ now()->translatedFormat('d F Y') }}</td></tr>
        <tr><td>Jumlah Anggota</td><td>:</td><td>{{ $anggota->count() }} orang</td></tr>
        <tr><td>Filter</td><td>:</td><td>{{ $filterLabel }}</td></tr>
      </table>
    </div>

    <table class="tabel-laporan">
      <thead>
        <tr>
          <th class="td-no">No.</th>
          <th>No. Anggota</th>
          <th>Nama</th>
          <th>NIK</th>
          <th>Jenis Kelamin</th>
          <th>Telepon</th>
          <th>Bergabung</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        @forelse($anggota as $i => $row)
        <tr>
          <td class="td-no">{{ $i + 1 }}</td>
          <td>{{ $row->nomor_anggota }}</td>
          <td>{{ $row->nama }}</td>
          <td class="is-24ddd604">{{ $row->nik }}</td>
          <td>{{ $row->jenis_kelamin }}</td>
          <td>{{ $row->telepon }}</td>
          <td>{{ $row->tanggal_bergabung ? \Illuminate\Support\Carbon::parse($row->tanggal_bergabung)->translatedFormat('d M Y') : '-' }}</td>
          <td><span class="badge-s badge-{{ str_replace('_', '-', $row->status) }}">{{ $labelStatus[$row->status] ?? $row->status }}</span></td>
        </tr>
        @empty
        <tr><td colspan="8" class="is-4ab8a655">Tidak ada data</td></tr>
        @endforelse
        <tr class="tr-total">
          <td colspan="7" class="is-a931e0d5">Total Anggota</td>
          <td class="is-96f6ea3d">{{ $anggota->count() }} orang</td>
        </tr>
      </tbody>
    </table>

    <div class="footer-cetak">
      <span>{{ $namaKoperasi }} &mdash; Dicetak {{ now()->translatedFormat('d F Y, H:i') }}</span>
      <span>Daftar Anggota | {{ $filterLabel }}</span>
    </div>

  </div>

</body>
</html>