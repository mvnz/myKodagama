<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  @php
    $namaBulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $periodeLabel = !empty($bulan) ? ($namaBulan[(int)$bulan] . ' ' . $tahun) : ('Tahun ' . $tahun);
    $namaKoperasi = $profil['nama_koperasi'] ?? 'Koperasi';
    $subNama      = $profil['sub_nama']      ?? 'Simpan Pinjam';
    $alamat       = $profil['alamat']        ?? '';
    $telepon      = $profil['telepon']       ?? '';
    $email        = $profil['email']         ?? '';
    // Ambil nama kota saja (teks setelah koma terakhir)
    $kota = $alamat ? trim(substr($alamat, strrpos($alamat, ',') + 1)) : '';
    $nomorLaporan = 'LAP-KEU/' . $tahun . (!empty($bulan) ? '/' . str_pad($bulan, 2, '0', STR_PAD_LEFT) : '') . '/' . now()->format('d');
    // Ambil nama pengurus dari profil
    $namaKetua = $profil['pgrs_ketua_nama'] ?? '';
    $bendaharaJson = $profil['pgrs_bendahara'] ?? '[]';
    $bendaharaArr  = json_decode($bendaharaJson, true);
    $namaBendahara = !empty($bendaharaArr[0]['nama']) ? $bendaharaArr[0]['nama'] : '';
  @endphp
  <title>Laporan Keuangan – {{ $periodeLabel }}</title>
  <style>
    /* ── Reset & Base ─────────────────────────────── */
    * { margin:0; padding:0; box-sizing:border-box; }
    body {
      font-family: "Times New Roman", Times, serif;
      font-size: 11pt;
      color: #000;
      background: #fff;
    }
    .page {
      width: 210mm;
      min-height: 297mm;
      margin: 0 auto;
      padding: 20mm 20mm 25mm 25mm;
    }

    /* ── Kop Surat ────────────────────────────────── */
    .kop {
      display: flex;
      align-items: center;
      gap: 14px;
      padding-bottom: 8px;
    }
    .kop-logo {
      width: 65px;
      height: 65px;
      border: 3px solid #000;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 22pt;
      font-weight: 900;
      flex-shrink: 0;
      letter-spacing: -1px;
    }
    .kop-teks { flex: 1; }
    .kop-teks .nama-koperasi {
      font-size: 17pt;
      font-weight: 900;
      text-transform: uppercase;
      letter-spacing: 1px;
      line-height: 1.1;
    }
    .kop-teks .sub-nama {
      font-size: 11pt;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .5px;
      margin-top: 1px;
    }
    .kop-teks .kop-kontak {
      font-size: 9pt;
      margin-top: 4px;
      color: #222;
    }
    .garis-kop {
      border: none;
      border-top: 3px solid #000;
      margin-top: 6px;
    }
    .garis-kop-tipis {
      border: none;
      border-top: 1.5px solid #000;
      margin-top: 2px;
    }

    /* ── Judul Dokumen ────────────────────────────── */
    .judul-dokumen {
      text-align: center;
      margin: 14px 0 4px;
    }
    .judul-dokumen h2 {
      font-size: 13pt;
      font-weight: 900;
      text-transform: uppercase;
      letter-spacing: 1.5px;
      text-decoration: underline;
    }
    .judul-dokumen p {
      font-size: 10.5pt;
      margin-top: 3px;
    }

    /* ── Info Dokumen ─────────────────────────────── */
    .info-dokumen {
      margin: 12px 0 16px;
      font-size: 10pt;
    }
    .info-dokumen table { border-collapse: collapse; }
    .info-dokumen td { padding: 1px 0; }
    .info-dokumen td:first-child { width: 140px; }
    .info-dokumen td:nth-child(2) { padding: 0 8px; }

    /* ── Tabel Laporan ────────────────────────────── */
    .tabel-laporan {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 10px;
      font-size: 10.5pt;
    }
    .tabel-laporan th {
      background: #e8e8e8;
      border: 1px solid #000;
      padding: 5px 8px;
      text-align: center;
      font-weight: 700;
      font-size: 10pt;
    }
    .tabel-laporan td {
      border: 1px solid #555;
      padding: 4px 8px;
      vertical-align: top;
    }
    .tabel-laporan .td-no    { width: 32px;  text-align: center; }
    .tabel-laporan .td-uang  { text-align: right; white-space: nowrap; }
    .tabel-laporan .td-bold  { font-weight: 700; }
    .tabel-laporan .tr-sub th {
      background: #f5f5f5;
      font-weight: 600;
      font-size: 10pt;
      text-align: left;
      padding-left: 10px;
    }
    .tabel-laporan .tr-total td {
      background: #e8e8e8;
      font-weight: 700;
      border-top: 2px solid #000;
    }
    .tabel-laporan .tr-surplus td {
      background: #d0e8d0;
      font-weight: 700;
      border-top: 2px solid #000;
    }
    .tabel-laporan .tr-defisit td {
      background: #f5d0d0;
      font-weight: 700;
      border-top: 2px solid #000;
    }
    .tabel-laporan .indent { padding-left: 24px; }
    .tabel-laporan .indent2 { padding-left: 40px; }

    /* ── Seksi Judul ──────────────────────────────── */
    .seksi-judul {
      font-size: 10.5pt;
      font-weight: 700;
      text-transform: uppercase;
      margin: 18px 0 6px;
      border-bottom: 1px solid #000;
      padding-bottom: 3px;
    }
    .seksi-judul span {
      display: inline-block;
      width: 24px;
    }

    /* ── Tanda Tangan ─────────────────────────────── */
    .ttd-area {
      margin-top: 30px;
      display: flex;
      justify-content: space-between;
    }
    .ttd-box {
      width: 200px;
      text-align: center;
      font-size: 10pt;
    }
    .ttd-box .ttd-garis {
      margin-top: 55px;
      padding-top: 4px;
      font-weight: 700;
    }

    /* ── Footer ────────────────────────────────────── */
    .footer-cetak {
      display: none;
    }

    /* ── Tombol layar (disembunyikan saat print) ── */
    .no-print {
      font-family: Arial, sans-serif;
      padding: 10px 20px;
      background: #f3f4f6;
      border-bottom: 1px solid #ddd;
      display: flex;
      gap: 10px;
      align-items: center;
    }
    .no-print button, .no-print a {
      padding: 6px 18px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 13px;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }
    .btn-print { background:#175cdd; color:#fff; }
    .btn-back  { background:#6b7280; color:#fff; }

    @media print {
      .no-print { display: none !important; }
      .page { padding: 15mm 15mm 20mm 20mm; }
      body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
      .footer-cetak {
        display: flex;
        justify-content: space-between;
        font-size: 8.5pt;
        color: #444;
        border-top: 1px solid #999;
        padding-top: 5px;
        margin-top: 24px;
      }
    }
    @page {
      size: A4 portrait;
      margin: 18mm 15mm 20mm 20mm;
    }
    @media print {
      .page {
        width: auto;
        padding: 0;
        margin: 0;
      }
      .footer-cetak {
        display: flex !important;
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        justify-content: space-between;
        font-size: 8.5pt;
        color: #444;
        border-top: 1px solid #999;
        padding: 3px 0 0 0;
        margin-top: 0;
        background: #fff;
      }
    }
  </style>
</head>
<body>

  {{-- Toolbar (hanya tampil di layar) --}}
  <div class="no-print">
    <button class="btn-print" onclick="window.print()">🖨 Cetak / Simpan PDF</button>
    <a class="btn-back" href="{{ route('laporan.keuangan', ['tahun' => $tahun, 'bulan' => $bulan]) }}">← Kembali</a>
    <span style="color:#555;font-size:12px">Laporan Keuangan — {{ $periodeLabel }}</span>
  </div>

  <div class="page">

    {{-- ── KOP SURAT ──────────────────────────────────────────── --}}
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

    {{-- ── JUDUL ──────────────────────────────────────────────── --}}
    <div class="judul-dokumen">
      <h2>Laporan Keuangan</h2>
      <p>Periode: {{ $periodeLabel }}</p>
    </div>

    {{-- ── INFO DOKUMEN ────────────────────────────────────────── --}}
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

    {{-- ════════════════════════════════════════════════════════════
         BAGIAN I  – LAPORAN ARUS KAS (RINGKASAN)
    ════════════════════════════════════════════════════════════ --}}
    <div class="seksi-judul"><span>I.</span> Laporan Arus Kas</div>

    <table class="tabel-laporan">
      <thead>
        <tr>
          <th class="td-no">No.</th>
          <th style="text-align:left">Uraian</th>
          <th style="width:180px">Jumlah (Rp)</th>
        </tr>
      </thead>
      <tbody>

        {{-- A. PENERIMAAN --}}
        <tr><th colspan="3" class="tr-sub" style="text-align:left;padding-left:8px">A. Penerimaan</th></tr>

        <tr>
          <td class="td-no">1</td>
          <td class="indent">Simpanan Pokok Anggota</td>
          <td class="td-uang">{{ number_format($simpananPokok, 2, ',', '.') }}</td>
        </tr>
        <tr>
          <td class="td-no">2</td>
          <td class="indent">Simpanan Wajib Anggota</td>
          <td class="td-uang">{{ number_format($simpananWajib, 2, ',', '.') }}</td>
        </tr>
        <tr>
          <td class="td-no">3</td>
          <td class="indent">Simpanan Sukarela Anggota</td>
          <td class="td-uang">{{ number_format($simpananSukarela, 2, ',', '.') }}</td>
        </tr>
        <tr>
          <td class="td-no">4</td>
          <td class="indent">Angsuran Pokok Pinjaman</td>
          <td class="td-uang">{{ number_format($totalPokokDiterima, 2, ',', '.') }}</td>
        </tr>
        <tr>
          <td class="td-no">5</td>
          <td class="indent">Pendapatan Bunga Pinjaman</td>
          <td class="td-uang">{{ number_format($totalBungaDiterima, 2, ',', '.') }}</td>
        </tr>
        <tr class="tr-total">
          <td class="td-no"></td>
          <td class="indent td-bold">Jumlah Penerimaan</td>
          <td class="td-uang td-bold">{{ number_format($totalPemasukan, 2, ',', '.') }}</td>
        </tr>

        {{-- B. PENGELUARAN --}}
        <tr><th colspan="3" class="tr-sub" style="text-align:left;padding-left:8px;background:#f5f5f5">B. Pengeluaran</th></tr>

        <tr>
          <td class="td-no">1</td>
          <td class="indent">Pencairan Pinjaman Anggota</td>
          <td class="td-uang">{{ number_format($pinjamanDicairkan, 2, ',', '.') }}</td>
        </tr>
        <tr class="tr-total">
          <td class="td-no"></td>
          <td class="indent td-bold">Jumlah Pengeluaran</td>
          <td class="td-uang td-bold">{{ number_format($totalPengeluaran, 2, ',', '.') }}</td>
        </tr>

        {{-- C. SURPLUS / DEFISIT --}}
        <tr class="{{ $arusKasBersih >= 0 ? 'tr-surplus' : 'tr-defisit' }}">
          <td class="td-no"></td>
          <td class="td-bold">{{ $arusKasBersih >= 0 ? 'SURPLUS' : 'DEFISIT' }} PERIODE</td>
          <td class="td-uang td-bold">
            {{ $arusKasBersih >= 0 ? '' : '(' }}{{ number_format(abs($arusKasBersih), 2, ',', '.') }}{{ $arusKasBersih >= 0 ? '' : ')' }}
          </td>
        </tr>

      </tbody>
    </table>

    {{-- ════════════════════════════════════════════════════════════
         BAGIAN II – RINCIAN TRANSAKSI SIMPANAN
    ════════════════════════════════════════════════════════════ --}}
    @if($detailSimpanan->isNotEmpty())
    <div class="seksi-judul"><span>II.</span> Rincian Transaksi Simpanan</div>

    <table class="tabel-laporan">
      <thead>
        <tr>
          <th class="td-no">No.</th>
          <th>Tanggal</th>
          <th>Nama Anggota</th>
          <th>Jenis Simpanan</th>
          <th style="width:160px">Jumlah (Rp)</th>
          <th>Keterangan</th>
        </tr>
      </thead>
      <tbody>
        @foreach($detailSimpanan as $i => $s)
        <tr>
          <td class="td-no">{{ $i + 1 }}</td>
          <td style="white-space:nowrap">{{ $s->tanggal->format('d/m/Y') }}</td>
          <td>{{ $s->anggota->nama }}</td>
          <td style="text-align:center;text-transform:capitalize">{{ $s->jenis_simpanan }}</td>
          <td class="td-uang">{{ number_format($s->jumlah, 2, ',', '.') }}</td>
          <td>{{ $s->keterangan ?? '—' }}</td>
        </tr>
        @endforeach
        <tr class="tr-total">
          <td colspan="4" class="td-bold" style="text-align:right">Total Simpanan</td>
          <td class="td-uang td-bold">{{ number_format($totalSimpananMasuk, 2, ',', '.') }}</td>
          <td></td>
        </tr>
      </tbody>
    </table>
    @endif

    {{-- ════════════════════════════════════════════════════════════
         BAGIAN III – RINCIAN ANGSURAN DITERIMA
    ════════════════════════════════════════════════════════════ --}}
    @if($detailAngsuran->isNotEmpty())
    <div class="seksi-judul"><span>III.</span> Rincian Angsuran Diterima</div>

    <table class="tabel-laporan">
      <thead>
        <tr>
          <th class="td-no">No.</th>
          <th>Tgl. Bayar</th>
          <th>Nama Anggota</th>
          <th>No. Pinjaman</th>
          <th class="td-no">Ke-</th>
          <th style="width:120px">Pokok (Rp)</th>
          <th style="width:120px">Bunga (Rp)</th>
          <th style="width:130px">Total (Rp)</th>
        </tr>
      </thead>
      <tbody>
        @foreach($detailAngsuran as $i => $a)
        <tr>
          <td class="td-no">{{ $i + 1 }}</td>
          <td style="white-space:nowrap">{{ $a->tanggal_bayar->format('d/m/Y') }}</td>
          <td>{{ $a->pinjaman->anggota->nama }}</td>
          <td style="font-size:9pt">{{ $a->pinjaman->nomor_pinjaman }}</td>
          <td class="td-no">{{ $a->ke }}</td>
          <td class="td-uang">{{ number_format($a->pokok, 2, ',', '.') }}</td>
          <td class="td-uang">{{ number_format($a->bunga, 2, ',', '.') }}</td>
          <td class="td-uang">{{ number_format($a->jumlah_angsuran, 2, ',', '.') }}</td>
        </tr>
        @endforeach
        <tr class="tr-total">
          <td colspan="5" class="td-bold" style="text-align:right">Total Angsuran</td>
          <td class="td-uang td-bold">{{ number_format($totalPokokDiterima, 2, ',', '.') }}</td>
          <td class="td-uang td-bold">{{ number_format($totalBungaDiterima, 2, ',', '.') }}</td>
          <td class="td-uang td-bold">{{ number_format($totalAngsuranDiterima, 2, ',', '.') }}</td>
        </tr>
      </tbody>
    </table>
    @endif

    {{-- ════════════════════════════════════════════════════════════
         BAGIAN IV – RINCIAN PINJAMAN DICAIRKAN
    ════════════════════════════════════════════════════════════ --}}
    @if($detailPinjaman->isNotEmpty())
    <div class="seksi-judul"><span>IV.</span> Rincian Pinjaman Dicairkan</div>

    <table class="tabel-laporan">
      <thead>
        <tr>
          <th class="td-no">No.</th>
          <th>Tgl. Persetujuan</th>
          <th>No. Pinjaman</th>
          <th>Nama Anggota</th>
          <th style="width:145px">Jumlah Pinjaman (Rp)</th>
          <th>Jangka</th>
          <th>Bunga</th>
        </tr>
      </thead>
      <tbody>
        @foreach($detailPinjaman as $i => $p)
        <tr>
          <td class="td-no">{{ $i + 1 }}</td>
          <td style="white-space:nowrap">{{ $p->tanggal_persetujuan?->format('d/m/Y') ?? '—' }}</td>
          <td style="font-size:9pt">{{ $p->nomor_pinjaman }}</td>
          <td>{{ $p->anggota->nama }}</td>
          <td class="td-uang">{{ number_format($p->jumlah_pinjaman, 2, ',', '.') }}</td>
          <td style="text-align:center">{{ $p->jangka_waktu }} bln</td>
          <td style="text-align:center">{{ $p->bunga }}%</td>
        </tr>
        @endforeach
        <tr class="tr-total">
          <td colspan="4" class="td-bold" style="text-align:right">Total Pinjaman Dicairkan</td>
          <td class="td-uang td-bold">{{ number_format($pinjamanDicairkan, 2, ',', '.') }}</td>
          <td colspan="2"></td>
        </tr>
      </tbody>
    </table>
    @endif

    {{-- ── TANDA TANGAN ────────────────────────────────────────── --}}
    <div class="ttd-area">
      <div class="ttd-box">
        <div>Mengetahui,</div>
        <div>Ketua Koperasi</div>
        <div class="ttd-garis">( {{ $namaKetua ?: '_______________________ ' }} )</div>
      </div>
      <div class="ttd-box">
        <div>{{ $kota ?: '-' }}</div>
        <div>Bendahara</div>
        <div class="ttd-garis">( {{ $namaBendahara ?: '_______________________ ' }} )</div>
      </div>
    </div>

    {{-- Footer (print only, normal flow di akhir halaman) --}}
    <div class="footer-cetak">
      <span>{{ $namaKoperasi }} — Laporan Keuangan Periode {{ $periodeLabel }}</span>
      <span>Dicetak: {{ now()->format('d/m/Y H:i') }}</span>
    </div>

  </div>{{-- /page --}}

</body>
</html>
