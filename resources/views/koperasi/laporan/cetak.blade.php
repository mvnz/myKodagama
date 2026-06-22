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
  <link rel="stylesheet" href="{{ asset('css/pages/cetak.css') }}">
  

  <link rel="stylesheet" href="{{ asset('css/pages/inline-extracted.css') }}">
</head>
<body>

  {{-- Toolbar (hanya tampil di layar) --}}
  <div class="no-print">
    <button class="btn-print" onclick="window.print()">🖨 Cetak / Simpan PDF</button>
    <a class="btn-back" href="{{ route('laporan.keuangan', ['tahun' => $tahun, 'bulan' => $bulan]) }}">← Kembali</a>
    <span class="is-53f43043">Laporan Keuangan — {{ $periodeLabel }}</span>
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
          <th class="is-200a92a0">Uraian</th>
          <th class="is-1dfda96a">Jumlah (Rp)</th>
        </tr>
      </thead>
      <tbody>

        {{-- A. PENERIMAAN --}}
        <tr><th colspan="3" class="tr-sub is-a248343f">A. Penerimaan</th></tr>

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
        <tr><th colspan="3" class="tr-sub is-bb781092">B. Pengeluaran</th></tr>

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
          <th class="is-df7d1005">Jumlah (Rp)</th>
          <th>Keterangan</th>
        </tr>
      </thead>
      <tbody>
        @foreach($detailSimpanan as $i => $s)
        <tr>
          <td class="td-no">{{ $i + 1 }}</td>
          <td class="is-4ddbe905">{{ $s->tanggal->format('d/m/Y') }}</td>
          <td>{{ $s->anggota->nama }}</td>
          <td class="is-2f4cc832">{{ $s->jenis_simpanan }}</td>
          <td class="td-uang">{{ number_format($s->jumlah, 2, ',', '.') }}</td>
          <td>{{ $s->keterangan ?? '—' }}</td>
        </tr>
        @endforeach
        <tr class="tr-total">
          <td colspan="4" class="td-bold is-f6e3d7fe">Total Simpanan</td>
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
          <th class="is-088cf005">Pokok (Rp)</th>
          <th class="is-088cf005">Bunga (Rp)</th>
          <th class="is-729192c0">Total (Rp)</th>
        </tr>
      </thead>
      <tbody>
        @foreach($detailAngsuran as $i => $a)
        <tr>
          <td class="td-no">{{ $i + 1 }}</td>
          <td class="is-4ddbe905">{{ $a->tanggal_bayar->format('d/m/Y') }}</td>
          <td>{{ $a->pinjaman->anggota->nama }}</td>
          <td class="is-24ddd604">{{ $a->pinjaman->nomor_pinjaman }}</td>
          <td class="td-no">{{ $a->ke }}</td>
          <td class="td-uang">{{ number_format($a->pokok, 2, ',', '.') }}</td>
          <td class="td-uang">{{ number_format($a->bunga, 2, ',', '.') }}</td>
          <td class="td-uang">{{ number_format($a->jumlah_angsuran, 2, ',', '.') }}</td>
        </tr>
        @endforeach
        <tr class="tr-total">
          <td colspan="5" class="td-bold is-f6e3d7fe">Total Angsuran</td>
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
          <th class="is-2daf62cd">Jumlah Pinjaman (Rp)</th>
          <th>Jangka</th>
          <th>Bunga</th>
        </tr>
      </thead>
      <tbody>
        @foreach($detailPinjaman as $i => $p)
        <tr>
          <td class="td-no">{{ $i + 1 }}</td>
          <td class="is-4ddbe905">{{ $p->tanggal_persetujuan?->format('d/m/Y') ?? '—' }}</td>
          <td class="is-24ddd604">{{ $p->nomor_pinjaman }}</td>
          <td>{{ $p->anggota->nama }}</td>
          <td class="td-uang">{{ number_format($p->jumlah_pinjaman, 2, ',', '.') }}</td>
          <td class="is-13076d95">{{ $p->jangka_waktu }} bln</td>
          <td class="is-13076d95">{{ $p->bunga }}%</td>
        </tr>
        @endforeach
        <tr class="tr-total">
          <td colspan="4" class="td-bold is-f6e3d7fe">Total Pinjaman Dicairkan</td>
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
        <div>{{ $kota ?: '-' }}, {{ now()->translatedFormat('d F Y') }}</div>
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