<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  @php
    $namaBulan   = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $namaKoperasi = $profil['nama_koperasi'] ?? 'Koperasi';
    $subNama      = $profil['sub_nama']      ?? 'Simpan Pinjam';
    $alamat       = $profil['alamat']        ?? '';
    $telepon      = $profil['telepon']       ?? '';
    $email        = $profil['email']         ?? '';
    $namaKetua    = $profil['pgrs_ketua_nama']      ?? '';
    $sekretarisJson = $profil['pgrs_sekretaris'] ?? '[]';
    $sekretarisArr  = json_decode($sekretarisJson, true);
    $namaSekretaris = !empty($sekretarisArr[0]['nama']) ? $sekretarisArr[0]['nama'] : ($profil['pgrs_sekretaris_nama'] ?? '');
    $bendaharaJson  = $profil['pgrs_bendahara'] ?? '[]';
    $bendaharaArr   = json_decode($bendaharaJson, true);
    $namaBendahara  = !empty($bendaharaArr[0]['nama']) ? $bendaharaArr[0]['nama'] : ($profil['pgrs_bendahara_nama'] ?? '');
    $nomorLpj = 'LPJ/' . $tahun . '/' . now()->format('m');
    $rp = fn($v) => number_format($v, 2, ',', '.');
    $rp0 = fn($v) => number_format($v, 0, ',', '.');
  @endphp
  <title>LPJ {{ $namaKoperasi }} Tahun {{ $tahun }}</title>
  <link rel="stylesheet" href="{{ asset('css/pages/cetak.css') }}">
  
  <link rel="stylesheet" href="{{ asset('css/pages/inline-extracted.css') }}">
</head>
<body>

  {{-- Toolbar --}}
  <div class="no-print">
    <button class="btn-print" onclick="window.print()">🖨 Cetak / Simpan PDF</button>
    <a class="btn-back" href="{{ route('laporan.lpj', ['tahun' => $tahun]) }}">← Kembali</a>
    <span class="is-53f43043">LPJ – Tahun {{ $tahun }}</span>
  </div>

  <div class="page">

    {{-- KOP SURAT --}}
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

    {{-- JUDUL --}}
    <div class="judul-dokumen">
      <h2>Laporan Pertanggungjawaban Pengurus</h2>
      <p>Periode Tahun {{ $tahun }}</p>
    </div>

    {{-- INFO DOKUMEN --}}
    <div class="info-dokumen">
      <table>
        <tr><td>Nomor</td><td>:</td><td>{{ $nomorLpj }}</td></tr>
        <tr><td>Tanggal Cetak</td><td>:</td><td>{{ now()->translatedFormat('d F Y') }}</td></tr>
        <tr><td>Periode Laporan</td><td>:</td><td>1 Januari {{ $tahun }} – 31 Desember {{ $tahun }}</td></tr>
      </table>
    </div>

    {{-- ════ I. PENDAHULUAN ════════════════════════════════════════ --}}
    <div class="seksi-judul"><span>I.</span> Pendahuluan</div>
    <p class="is-479f9fe8">
      Pengurus {{ $namaKoperasi }} dengan ini menyampaikan Laporan Pertanggungjawaban atas
      pelaksanaan tugas dan kegiatan koperasi selama periode 1 Januari {{ $tahun }} sampai dengan
      31 Desember {{ $tahun }}. Laporan ini disusun sebagai pertanggungjawaban pengurus kepada
      seluruh anggota dalam Rapat Anggota Tahunan (RAT).
    </p>

    {{-- ════ II. PERKEMBANGAN KEANGGOTAAN ════════════════════════ --}}
    <div class="seksi-judul"><span>II.</span> Perkembangan Keanggotaan</div>
    <table class="tabel-lpj">
      <thead>
        <tr><th>No.</th><th class="is-200a92a0">Keterangan</th><th class="is-729192c0">Jumlah</th></tr>
      </thead>
      <tbody>
        <tr><td class="td-no">1</td><td>Anggota awal tahun {{ $tahun }}</td><td class="td-uang">{{ $rp0($anggotaAwalTahun) }} orang</td></tr>
        <tr><td class="td-no">2</td><td>Anggota baru masuk</td><td class="td-uang">{{ $rp0($anggotaBaru) }} orang</td></tr>
        <tr class="tr-total"><td></td><td>Anggota akhir tahun {{ $tahun }}</td><td class="td-uang">{{ $rp0($totalAnggota) }} orang</td></tr>
        <tr><td class="td-no"></td><td class="indent">a. Aktif</td><td class="td-uang">{{ $rp0($anggotaAktif) }} orang</td></tr>
        <tr><td class="td-no"></td><td class="indent">b. Tidak Aktif / Keluar</td><td class="td-uang">{{ $rp0($anggotaTidakAktif) }} orang</td></tr>
      </tbody>
    </table>

    {{-- ════ III. PERKEMBANGAN SIMPANAN ════════════════════════════ --}}
    <div class="seksi-judul"><span>III.</span> Perkembangan Permodalan & Simpanan</div>
    <table class="tabel-lpj">
      <thead>
        <tr><th>No.</th><th class="is-200a92a0">Jenis Simpanan</th><th class="is-1ac3ecac">Jumlah (Rp)</th></tr>
      </thead>
      <tbody>
        <tr><td class="td-no">1</td><td>Simpanan Pokok</td><td class="td-uang">{{ $rp($simpananPokok) }}</td></tr>
        <tr><td class="td-no">2</td><td>Simpanan Wajib</td><td class="td-uang">{{ $rp($simpananWajib) }}</td></tr>
        <tr><td class="td-no">3</td><td>Simpanan Sukarela</td><td class="td-uang">{{ $rp($simpananSukarela) }}</td></tr>
        <tr class="tr-total"><td></td><td class="td-bold">Jumlah Simpanan Tahun {{ $tahun }}</td><td class="td-uang">{{ $rp($totalSimpananTahun) }}</td></tr>
        <tr class="tr-pos"><td></td><td class="td-bold">Total Simpanan Kumulatif (s.d. 31 Des {{ $tahun }})</td><td class="td-uang">{{ $rp($totalSimpananKumulatif) }}</td></tr>
      </tbody>
    </table>

    {{-- ════ IV. PERKEMBANGAN PINJAMAN ════════════════════════════ --}}
    <div class="seksi-judul"><span>IV.</span> Perkembangan Usaha Simpan Pinjam</div>

    <p class="is-789ea1b2">A. Pinjaman</p>
    <table class="tabel-lpj">
      <thead>
        <tr><th>No.</th><th class="is-200a92a0">Keterangan</th><th class="is-729192c0">Jumlah</th><th class="is-1ac3ecac">Nilai (Rp)</th></tr>
      </thead>
      <tbody>
        <tr><td class="td-no">1</td><td>Pinjaman diajukan tahun {{ $tahun }}</td><td class="td-uang">{{ $rp0($jumlahPinjamanDiajukan) }}</td><td class="td-uang">—</td></tr>
        <tr><td class="td-no">2</td><td>Pinjaman disetujui & dicairkan</td><td class="td-uang">{{ $rp0($jumlahPinjamanDisetujui) }}</td><td class="td-uang">{{ $rp($nilaiPinjamanDicairkan) }}</td></tr>
        <tr><td class="td-no">3</td><td>Pinjaman aktif (posisi akhir tahun)</td><td class="td-uang">{{ $rp0($jumlahPinjamanAktif) }}</td><td class="td-uang">{{ $rp($nilaiPinjamanAktif) }}</td></tr>
      </tbody>
    </table>

    <p class="is-99457d26">B. Angsuran Diterima Tahun {{ $tahun }}</p>
    <table class="tabel-lpj">
      <thead>
        <tr><th>No.</th><th class="is-200a92a0">Keterangan</th><th class="is-c981ed17">Jumlah (Rp)</th></tr>
      </thead>
      <tbody>
        <tr><td class="td-no">1</td><td>Angsuran Pokok Diterima</td><td class="td-uang">{{ $rp($totalPokokDiterima) }}</td></tr>
        <tr><td class="td-no">2</td><td>Pendapatan Bunga Diterima</td><td class="td-uang">{{ $rp($totalBungaDiterima) }}</td></tr>
        <tr class="tr-total"><td></td><td>Jumlah Angsuran Diterima</td><td class="td-uang">{{ $rp($totalAngsuranDiterima) }}</td></tr>
        <tr><td class="td-no"></td><td>Angsuran Tertunggak (aktif)</td><td class="td-uang">{{ $rp($totalAngsuranTunggak) }}</td></tr>
      </tbody>
    </table>

    {{-- ════ V. LAPORAN ARUS KAS ════════════════════════════════════ --}}
    <div class="seksi-judul"><span>V.</span> Laporan Arus Kas Tahun {{ $tahun }}</div>
    <table class="tabel-lpj">
      <thead>
        <tr><th>No.</th><th class="is-200a92a0">Uraian</th><th class="is-c981ed17">Jumlah (Rp)</th></tr>
      </thead>
      <tbody>
        <tr class="tr-sub"><td></td><td colspan="2">A. Penerimaan</td></tr>
        <tr><td class="td-no">1</td><td class="indent">Simpanan Anggota</td><td class="td-uang">{{ $rp($totalSimpananTahun) }}</td></tr>
        <tr><td class="td-no">2</td><td class="indent">Angsuran Pinjaman</td><td class="td-uang">{{ $rp($totalAngsuranDiterima) }}</td></tr>
        <tr class="tr-total"><td></td><td>Jumlah Penerimaan</td><td class="td-uang">{{ $rp($totalPemasukan) }}</td></tr>

        <tr class="tr-sub"><td></td><td colspan="2">B. Pengeluaran</td></tr>
        <tr><td class="td-no">1</td><td class="indent">Pencairan Pinjaman Anggota</td><td class="td-uang">{{ $rp($totalPengeluaran) }}</td></tr>
        <tr class="tr-total"><td></td><td>Jumlah Pengeluaran</td><td class="td-uang">{{ $rp($totalPengeluaran) }}</td></tr>

        <tr class="{{ $arusKasBersih >= 0 ? 'tr-pos' : 'tr-neg' }}">
          <td></td>
          <td>{{ $arusKasBersih >= 0 ? 'SURPLUS' : 'DEFISIT' }} ARUS KAS</td>
          <td class="td-uang">
            {{ $arusKasBersih >= 0 ? '' : '(' }}{{ $rp(abs($arusKasBersih)) }}{{ $arusKasBersih >= 0 ? '' : ')' }}
          </td>
        </tr>
      </tbody>
    </table>

    {{-- ════ VI. LABA RUGI & SHU ══════════════════════════════════ --}}
    <div class="seksi-judul"><span>VI.</span> Laporan Laba Rugi & Sisa Hasil Usaha Tahun {{ $tahun }}</div>
    <table class="tabel-lpj">
      <thead>
        <tr><th>No.</th><th class="is-200a92a0">Uraian</th><th class="is-c981ed17">Jumlah (Rp)</th></tr>
      </thead>
      <tbody>
        <tr class="tr-sub"><td></td><td colspan="2">A. Pendapatan</td></tr>
        <tr><td class="td-no">1</td><td class="indent">Pendapatan Bunga Pinjaman</td><td class="td-uang">{{ $rp($bungaMasuk) }}</td></tr>
        <tr><td class="td-no">2</td><td class="indent">Pendapatan Lainnya</td><td class="td-uang">{{ $rp($pendapatanLain) }}</td></tr>
        <tr class="tr-total"><td></td><td>Jumlah Pendapatan</td><td class="td-uang">{{ $rp($totalPendapatan) }}</td></tr>

        <tr class="tr-sub"><td></td><td colspan="2">B. Biaya Operasional</td></tr>
        <tr class="tr-total"><td></td><td>Jumlah Biaya</td><td class="td-uang">{{ $rp($totalBiayaOps) }}</td></tr>

        <tr class="{{ $shuBersih >= 0 ? 'tr-pos' : 'tr-neg' }}">
          <td></td>
          <td>SISA HASIL USAHA (SHU)</td>
          <td class="td-uang">
            {{ $shuBersih >= 0 ? '' : '(' }}{{ $rp(abs($shuBersih)) }}{{ $shuBersih >= 0 ? '' : ')' }}
          </td>
        </tr>

        @if($shuRecord && $shuRecord->total_shu != $shuBersih)
        <tr class="is-86ba6195">
          <td></td>
          <td>SHU ditetapkan dalam RAT</td>
          <td class="td-uang">{{ $rp($shuRecord->total_shu) }}</td>
        </tr>
        @endif
      </tbody>
    </table>

    {{-- ════ VII. PENUTUP ══════════════════════════════════════════ --}}
    <div class="seksi-judul"><span>VII.</span> Penutup</div>
    <p class="is-641d8aaf">
      Demikian Laporan Pertanggungjawaban Pengurus {{ $namaKoperasi }} untuk periode
      Tahun {{ $tahun }} ini kami sampaikan. Semoga laporan ini dapat memberikan gambaran
      yang jelas mengenai perkembangan dan kondisi koperasi, serta menjadi bahan evaluasi
      bagi seluruh anggota dalam Rapat Anggota Tahunan.
    </p>

    {{-- TANDA TANGAN --}}
    <div class="is-60012b3c">
      <div class="is-a4cec079">
        @php $kota = $alamat ? trim(substr($alamat, strrpos($alamat, ',') + 1)) : ''; @endphp
        {{ $kota ? $kota . ', ' : '' }}{{ now()->translatedFormat('d F Y') }}
      </div>
    </div>
    <div class="ttd-area">
      <div class="ttd-box">
        <div>Sekretaris</div>
        <div class="ttd-garis">{{ $namaSekretaris ?: '...........................' }}</div>
      </div>
      <div class="ttd-box">
        <div>Ketua</div>
        <div class="ttd-garis">{{ $namaKetua ?: '...........................' }}</div>
      </div>
      <div class="ttd-box">
        <div>Bendahara</div>
        <div class="ttd-garis">{{ $namaBendahara ?: '...........................' }}</div>
      </div>
    </div>

    {{-- Footer --}}
    <div class="footer-cetak is-42f3251c">
      <span>{{ $namaKoperasi }} — LPJ Tahun {{ $tahun }}</span>
      <span>Dicetak: {{ now()->translatedFormat('d F Y, H:i') }}</span>
    </div>

  </div>
</body>
</html>
