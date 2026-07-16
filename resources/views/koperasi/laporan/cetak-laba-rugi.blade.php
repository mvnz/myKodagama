<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  @php
    $namaBulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $periodeLabel = !empty($bulan) ? ($namaBulan[(int) $bulan] . ' ' . $tahun) : ('Tahun ' . $tahun);
    $namaKoperasi = $profil['nama_koperasi'] ?? 'Koperasi';
    $subNama      = $profil['sub_nama'] ?? 'Simpan Pinjam';
    $alamat       = $profil['alamat'] ?? '';
    $telepon      = $profil['telepon'] ?? '';
    $email        = $profil['email'] ?? '';
    $kota = $alamat ? trim(substr($alamat, strrpos($alamat, ',') + 1)) : '';

    $nomorLaporan = 'LAP-LR/' . $tahun . (!empty($bulan) ? '/' . str_pad((string) $bulan, 2, '0', STR_PAD_LEFT) : '') . '/' . now()->format('d');

    $namaKetua = $profil['pgrs_ketua_nama'] ?? '';
    $bendaharaJson = $profil['pgrs_bendahara'] ?? '[]';
    $bendaharaArr = json_decode($bendaharaJson, true);
    $namaBendahara = !empty($bendaharaArr[0]['nama']) ? $bendaharaArr[0]['nama'] : '';
  @endphp
  <title>Laporan Laba Rugi - {{ $periodeLabel }}</title>
  <link rel="stylesheet" href="{{ asset('css/pages/cetak.css') }}">
</head>
<body>

  <div class="no-print">
    <button class="btn-print" onclick="window.print()">&#128438; Cetak / Simpan PDF</button>
    <a class="btn-back" href="{{ route('laporan.laba-rugi', ['tahun' => $tahun, 'bulan' => $bulan]) }}">&larr; Kembali</a>
    <span>Laporan Laba Rugi - {{ $periodeLabel }}</span>
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
      <h2>Laporan Laba Rugi</h2>
      <p>Periode: {{ $periodeLabel }}</p>
    </div>

    <div class="info-dokumen">
      <table>
        <tr>
          <td>Nomor Laporan</td><td>:</td>
          <td>{{ $nomorLaporan }}</td>
        </tr>
        <tr>
          <td>Tanggal Cetak</td><td>:</td>
          <td>{{ now()->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
          <td>Periode</td><td>:</td>
          <td>{{ $periodeLabel }}</td>
        </tr>
      </table>
    </div>

    <div class="seksi-judul"><span>I.</span> Ringkasan Laba Rugi</div>

    <table class="tabel-laporan">
      <thead>
        <tr>
          <th class="td-no">No.</th>
          <th>Uraian</th>
          <th class="td-uang">Jumlah (Rp)</th>
        </tr>
      </thead>
      <tbody>
        <tr><th colspan="3" class="tr-sub">A. Pendapatan</th></tr>

        <tr>
          <td class="td-no">1</td>
          <td class="indent">Bunga Pinjaman (Diterima)</td>
          <td class="td-uang">{{ number_format($bungaMasuk, 2, ',', '.') }}</td>
        </tr>

        @php $noPendapatan = 2; @endphp
        @foreach($pendapatanLain->sortByDesc(fn($nilai) => $nilai) as $kategori => $nilai)
          <tr>
            <td class="td-no">{{ $noPendapatan++ }}</td>
            <td class="indent">{{ $labelKategoriPendapatan[$kategori] ?? $kategori }}</td>
            <td class="td-uang">{{ number_format($nilai, 2, ',', '.') }}</td>
          </tr>
        @endforeach

        <tr class="tr-total">
          <td class="td-no"></td>
          <td class="td-bold">Jumlah Pendapatan</td>
          <td class="td-uang td-bold">{{ number_format($totalPendapatan, 2, ',', '.') }}</td>
        </tr>

        <tr><th colspan="3" class="tr-sub">B. Pengeluaran</th></tr>

        @forelse($pengeluaran->sortByDesc(fn($nilai) => $nilai) as $kategori => $nilai)
          <tr>
            <td class="td-no">{{ $loop->iteration }}</td>
            <td class="indent">{{ $labelKategoriPengeluaran[$kategori] ?? $kategori }}</td>
            <td class="td-uang">{{ number_format($nilai, 2, ',', '.') }}</td>
          </tr>
        @empty
          <tr>
            <td class="td-no">1</td>
            <td class="indent">Tidak ada pengeluaran pada periode ini</td>
            <td class="td-uang">0,00</td>
          </tr>
        @endforelse

        <tr class="tr-total">
          <td class="td-no"></td>
          <td class="td-bold">Jumlah Pengeluaran</td>
          <td class="td-uang td-bold">{{ number_format($totalPengeluaran, 2, ',', '.') }}</td>
        </tr>

        <tr class="{{ $shu >= 0 ? 'tr-surplus' : 'tr-defisit' }}">
          <td class="td-no"></td>
          <td class="td-bold">Sisa Hasil Usaha (SHU)</td>
          <td class="td-uang td-bold">
            {{ $shu >= 0 ? '' : '(' }}{{ number_format(abs($shu), 2, ',', '.') }}{{ $shu >= 0 ? '' : ')' }}
          </td>
        </tr>
      </tbody>
    </table>

    @if($detailPendapatan->isNotEmpty())
      <div class="seksi-judul"><span>II.</span> Rincian Pendapatan</div>
      <table class="tabel-laporan">
        <thead>
          <tr>
            <th class="td-no">No.</th>
            <th>Tanggal</th>
            <th>Kategori</th>
            <th>Keterangan</th>
            <th class="td-uang">Jumlah (Rp)</th>
          </tr>
        </thead>
        <tbody>
          @foreach($detailPendapatan as $item)
            <tr>
              <td class="td-no">{{ $loop->iteration }}</td>
              <td>{{ optional($item->tanggal)->format('d/m/Y') }}</td>
              <td>{{ $labelKategoriPendapatan[$item->kategori] ?? $item->kategori }}</td>
              <td>{{ $item->keterangan ?: '-' }}</td>
              <td class="td-uang">{{ number_format($item->jumlah, 2, ',', '.') }}</td>
            </tr>
          @endforeach
          <tr class="tr-total">
            <td colspan="4" class="td-bold">Total Rincian Pendapatan</td>
            <td class="td-uang td-bold">{{ number_format($detailPendapatan->sum('jumlah'), 2, ',', '.') }}</td>
          </tr>
        </tbody>
      </table>
    @endif

    @if($detailPengeluaran->isNotEmpty())
      <div class="seksi-judul"><span>III.</span> Rincian Pengeluaran</div>
      <table class="tabel-laporan">
        <thead>
          <tr>
            <th class="td-no">No.</th>
            <th>Tanggal</th>
            <th>Kategori</th>
            <th>Keterangan</th>
            <th class="td-uang">Jumlah (Rp)</th>
          </tr>
        </thead>
        <tbody>
          @foreach($detailPengeluaran as $item)
            <tr>
              <td class="td-no">{{ $loop->iteration }}</td>
              <td>{{ optional($item->tanggal)->format('d/m/Y') }}</td>
              <td>{{ $labelKategoriPengeluaran[$item->kategori] ?? $item->kategori }}</td>
              <td>{{ $item->keterangan ?: '-' }}</td>
              <td class="td-uang">{{ number_format($item->jumlah, 2, ',', '.') }}</td>
            </tr>
          @endforeach
          <tr class="tr-total">
            <td colspan="4" class="td-bold">Total Rincian Pengeluaran</td>
            <td class="td-uang td-bold">{{ number_format($detailPengeluaran->sum('jumlah'), 2, ',', '.') }}</td>
          </tr>
        </tbody>
      </table>
    @endif

    <div class="ttd-area">
      <div class="ttd-box">
        <div>Mengetahui,</div>
        <div>Ketua Koperasi</div>
        <div class="ttd-garis">( {{ $namaKetua ?: '_______________________' }} )</div>
      </div>
      <div class="ttd-box">
        <div>{{ $kota ?: '-' }}, {{ now()->translatedFormat('d F Y') }}</div>
        <div>Bendahara</div>
        <div class="ttd-garis">( {{ $namaBendahara ?: '_______________________' }} )</div>
      </div>
    </div>

    <div class="footer-cetak">
      <span>{{ $namaKoperasi }} - Laporan Laba Rugi Periode {{ $periodeLabel }}</span>
      <span>Dicetak: {{ now()->format('d/m/Y H:i') }}</span>
    </div>

  </div>
</body>
</html>
