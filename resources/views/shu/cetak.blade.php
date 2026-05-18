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
  <style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: "Times New Roman", Times, serif; font-size: 11pt; color: #000; background: #fff; }
    .page { width:210mm; min-height:297mm; margin:0 auto; padding:20mm 20mm 25mm 25mm; }
    .kop { display:flex; align-items:center; gap:14px; padding-bottom:8px; }
    .kop-logo { width:65px; height:65px; border:3px solid #000; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:22pt; font-weight:900; flex-shrink:0; }
    .kop-teks { flex:1; }
    .kop-teks .nama-koperasi { font-size:17pt; font-weight:900; text-transform:uppercase; letter-spacing:1px; line-height:1.1; }
    .kop-teks .sub-nama { font-size:11pt; font-weight:700; text-transform:uppercase; letter-spacing:.5px; margin-top:1px; }
    .kop-teks .kop-kontak { font-size:9pt; margin-top:4px; color:#222; }
    .garis-kop { border:none; border-top:3px solid #000; margin-top:6px; }
    .garis-kop-tipis { border:none; border-top:1.5px solid #000; margin-top:2px; }
    .judul-dokumen { text-align:center; margin:14px 0 4px; }
    .judul-dokumen h2 { font-size:13pt; font-weight:900; text-transform:uppercase; letter-spacing:1.5px; text-decoration:underline; }
    .judul-dokumen p { font-size:10.5pt; margin-top:3px; }
    .info-dokumen { margin:12px 0 16px; font-size:10pt; }
    .info-dokumen table { border-collapse:collapse; }
    .info-dokumen td { padding:1px 0; }
    .info-dokumen td:first-child { width:140px; }
    .info-dokumen td:nth-child(2) { padding:0 8px; }
    .tabel-laporan { width:100%; border-collapse:collapse; margin-bottom:10px; font-size:10pt; }
    .tabel-laporan th { background:#e8e8e8; border:1px solid #000; padding:5px 8px; text-align:center; font-weight:700; }
    .tabel-laporan td { border:1px solid #555; padding:4px 8px; vertical-align:top; }
    .td-no { width:32px; text-align:center; }
    .td-uang { text-align:right; white-space:nowrap; }
    .tr-total td { background:#e8e8e8; font-weight:700; border-top:2px solid #000; }
    .seksi-judul { font-size:10.5pt; font-weight:700; text-transform:uppercase; margin:18px 0 6px; border-bottom:1px solid #000; padding-bottom:3px; }
    .ttd-area { margin-top:30px; display:flex; justify-content:space-between; }
    .ttd-box { width:200px; text-align:center; font-size:10pt; }
    .ttd-box .ttd-garis { margin-top:55px; padding-top:4px; font-weight:700; }
    .no-print { font-family:Arial,sans-serif; padding:10px 20px; background:#f3f4f6; border-bottom:1px solid #ddd; display:flex; gap:10px; align-items:center; }
    .no-print button, .no-print a { padding:6px 18px; border:none; border-radius:4px; cursor:pointer; font-size:13px; text-decoration:none; display:inline-flex; align-items:center; gap:6px; }
    .btn-print { background:#175cdd; color:#fff; }
    .btn-back { background:#e5e7eb; color:#374151; }
    @media print {
      .no-print { display:none !important; }
      .page { padding:15mm 15mm 20mm 20mm; }
      @page { size: A4 portrait; margin:0; }
    }
  </style>
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
        {{ $alamat }}@if($telepon) &nbsp;|&nbsp; Telp. {{ $telepon }}@endif@if($email) &nbsp;|&nbsp; {{ $email }}@endif
      </div>
    </div>
  </div>
  <hr class="garis-kop">
  <hr class="garis-kop-tipis" style="margin-top:3px">

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
        <th style="text-align:left">Keterangan</th>
        <th style="width:180px">Jumlah</th>
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
        <th style="text-align:left">Komponen</th>
        <th style="width:80px">%</th>
        <th style="width:180px">Jumlah</th>
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
        <th style="text-align:left">Nama Anggota</th>
        <th style="width:130px">Total Simpanan</th>
        <th style="width:130px">Total Pinjaman</th>
        <th style="width:110px">Jasa Modal</th>
        <th style="width:110px">Jasa Usaha</th>
        <th style="width:120px">Total SHU</th>
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
        <td colspan="5" style="text-align:right">Total SHU Didistribusikan:</td>
        <td class="td-uang">Rp {{ number_format($totalDidistribusi, 0, ',', '.') }}</td>
      </tr>
    </tfoot>
  </table>

  @if($shu->keterangan)
  <p style="margin-top:12px;font-size:10pt"><strong>Keterangan:</strong> {{ $shu->keterangan }}</p>
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
