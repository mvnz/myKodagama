<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  @php
    $namaKoperasi  = $profil['nama_koperasi'] ?? 'Koperasi';
    $subNama       = $profil['sub_nama']      ?? 'Simpan Pinjam';
    $alamat        = $profil['alamat']        ?? '';
    $telepon       = $profil['telepon']       ?? '';
    $email         = $profil['email']         ?? '';
    $kota          = $alamat ? trim(substr($alamat, strrpos($alamat, ',') + 1)) : '';
    $namaKetua     = $profil['pgrs_ketua_nama'] ?? '';
    $bendaharaArr  = json_decode($profil['pgrs_bendahara'] ?? '[]', true);
    $namaBendahara = !empty($bendaharaArr[0]['nama']) ? $bendaharaArr[0]['nama'] : '';
    $totalDidistribusi = $shu->anggota->sum('total_shu_anggota');
    $nomorDok      = 'SHU/' . $shu->tahun . '/' . now()->format('d');
  @endphp
  <title>Laporan SHU – {{ $shu->tahun }}</title>
  <link rel="stylesheet" href="{{ asset('css/pages/cetak.css') }}">
  
  <link rel="stylesheet" href="{{ asset('css/pages/inline-extracted.css') }}">
</head>
<body>

<div class="no-print">
  <button class="btn-print" onclick="window.print()">&#128438; Cetak</button>
  <a class="btn-back" href="{{ route('shu.show', $shu) }}">&#8592; Kembali</a>
</div>

<div class="page">
  {{-- Kop Surat --}}
  <div class="kop">
    <div class="kop-logo">K</div>
    <div class="kop-teks">
      <div class="nama-koperasi">{{ $namaKoperasi }}</div>
      <div class="sub-nama">{{ $subNama }}</div>
      <div class="kop-kontak">
        {{ $alamat }}
        @if($telepon) &nbsp;|&nbsp; Telp. {{ $telepon }} @endif
        @if($email) &nbsp;|&nbsp; {{ $email }} @endif
      </div>
    </div>
  </div>
  <hr class="garis-kop">
  <hr class="garis-kop-tipis is-1d1dc4d1">

  {{-- Judul --}}
  <div class="judul-dokumen">
    <h2>Laporan Sisa Hasil Usaha (SHU)</h2>
    <p>Tahun {{ $shu->tahun }}</p>
  </div>

  {{-- Info Dokumen --}}
  <div class="info-dokumen">
    <table>
      <tr><td>Nomor Dokumen</td><td>:</td><td>{{ $nomorDok }}</td></tr>
      <tr><td>Tahun Buku</td><td>:</td><td>{{ $shu->tahun }}</td></tr>
      <tr><td>Status</td><td>:</td><td>{{ $shu->status === 'final' ? 'Final' : 'Draft' }}</td></tr>
      <tr><td>Tanggal Cetak</td><td>:</td><td>{{ now()->translatedFormat('d F Y') }}</td></tr>
    </table>
  </div>

  {{-- Ringkasan --}}
  <div class="seksi-judul">I. Ringkasan Keuangan</div>
  <table class="tabel-laporan">
    <thead>
      <tr>
        <th class="td-no">No</th>
        <th class="is-200a92a0">Keterangan</th>
        <th class="is-1dfda96a">Jumlah</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td class="td-no">1</td>
        <td>Total Pendapatan</td>
        <td class="td-uang">Rp {{ number_format($shu->total_pendapatan, 0, ',', '.') }}</td>
      </tr>
      <tr>
        <td class="td-no">2</td>
        <td>Total Biaya Operasional</td>
        <td class="td-uang">Rp {{ number_format($shu->total_biaya, 0, ',', '.') }}</td>
      </tr>
      <tr class="tr-total">
        <td class="td-no"></td>
        <td>Sisa Hasil Usaha (SHU)</td>
        <td class="td-uang">Rp {{ number_format($shu->total_shu, 0, ',', '.') }}</td>
      </tr>
    </tbody>
  </table>

  {{-- Alokasi --}}
  <div class="seksi-judul">II. Alokasi SHU</div>
  @php
    $shuModal = $shu->total_shu * ($shu->persentase_jasa_modal / 100);
    $shuUsaha = $shu->total_shu * ($shu->persentase_jasa_usaha / 100);
    $shuLain  = $shu->total_shu - $shuModal - $shuUsaha;
  @endphp
  <table class="tabel-laporan">
    <thead>
      <tr>
        <th class="td-no">No</th>
        <th class="is-200a92a0">Komponen</th>
        <th class="is-8db8d854">%</th>
        <th class="is-1dfda96a">Jumlah</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td class="td-no">1</td>
        <td>Jasa Modal (berdasarkan simpanan)</td>
        <td class="td-uang">{{ $shu->persentase_jasa_modal }}%</td>
        <td class="td-uang">Rp {{ number_format($shuModal, 0, ',', '.') }}</td>
      </tr>
      <tr>
        <td class="td-no">2</td>
        <td>Jasa Usaha (berdasarkan pinjaman)</td>
        <td class="td-uang">{{ $shu->persentase_jasa_usaha }}%</td>
        <td class="td-uang">Rp {{ number_format($shuUsaha, 0, ',', '.') }}</td>
      </tr>
      @if($shuLain > 0)
      <tr>
        <td class="td-no">3</td>
        <td>Cadangan / Dana Lain</td>
        <td class="td-uang">{{ 100 - $shu->persentase_jasa_modal - $shu->persentase_jasa_usaha }}%</td>
        <td class="td-uang">Rp {{ number_format($shuLain, 0, ',', '.') }}</td>
      </tr>
      @endif
      <tr class="tr-total">
        <td class="td-no"></td>
        <td colspan="2">Total Dialokasikan ke Anggota</td>
        <td class="td-uang">Rp {{ number_format($totalDidistribusi, 0, ',', '.') }}</td>
      </tr>
    </tbody>
  </table>

  {{-- Rincian Per Anggota --}}
  <div class="seksi-judul">III. Rincian SHU per Anggota</div>
  <table class="tabel-laporan">
    <thead>
      <tr>
        <th class="td-no">No</th>
        <th class="is-200a92a0">Nama Anggota</th>
        <th class="is-729192c0">Total Simpanan</th>
        <th class="is-729192c0">Total Pinjaman</th>
        <th class="is-1a30558f">Jasa Modal</th>
        <th class="is-1a30558f">Jasa Usaha</th>
        <th class="is-088cf005">Total SHU</th>
      </tr>
    </thead>
    <tbody>
      @foreach($shu->anggota->sortByDesc('total_shu_anggota') as $i => $item)
        <tr>
          <td class="td-no">{{ $i + 1 }}</td>
          <td>{{ $item->anggota->nama ?? '-' }}</td>
          <td class="td-uang">Rp {{ number_format($item->total_simpanan, 0, ',', '.') }}</td>
          <td class="td-uang">Rp {{ number_format($item->total_pinjaman, 0, ',', '.') }}</td>
          <td class="td-uang">Rp {{ number_format($item->jasa_modal, 0, ',', '.') }}</td>
          <td class="td-uang">Rp {{ number_format($item->jasa_usaha, 0, ',', '.') }}</td>
          <td class="td-uang"><strong>Rp {{ number_format($item->total_shu_anggota, 0, ',', '.') }}</strong></td>
        </tr>
      @endforeach
    </tbody>
    <tfoot>
      <tr class="tr-total">
        <td class="td-no"></td>
        <td colspan="5" class="is-f6e3d7fe">Total SHU Didistribusikan:</td>
        <td class="td-uang">Rp {{ number_format($totalDidistribusi, 0, ',', '.') }}</td>
      </tr>
    </tfoot>
  </table>

  @if($shu->keterangan)
  <p class="is-c6fb47f0"><strong>Keterangan:</strong> {{ $shu->keterangan }}</p>
  @endif

  {{-- Tanda Tangan --}}
  <div class="ttd-area">
    <div class="ttd-box">
      <div>Mengetahui,</div>
      <div>Ketua Koperasi</div>
      <div class="ttd-garis">
        @if($namaKetua)( {{ $namaKetua }} )@else&nbsp;@endif
      </div>
    </div>
    <div class="ttd-box">
      <div>{{ $kota }}, {{ now()->translatedFormat('d F Y') }}</div>
      <div>Bendahara</div>
      <div class="ttd-garis">
        @if($namaBendahara)( {{ $namaBendahara }} )@else&nbsp;@endif
      </div>
    </div>
  </div>

</div>
</body>
</html>