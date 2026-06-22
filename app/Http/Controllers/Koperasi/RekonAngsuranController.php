<?php

namespace App\Http\Controllers\Koperasi;

use App\Http\Controllers\Controller;

use App\Http\Controllers\Concerns\CatatAktivitas;
use App\Models\Koperasi\Anggota;
use App\Models\Koperasi\Angsuran;
use App\Models\Koperasi\Pinjaman;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class RekonAngsuranController extends Controller
{
    use CatatAktivitas;

    public function index()
    {
        $this->authorize('angsuran.rekon');
        return view('koperasi.rekon-angsuran.index');
    }

    public function template()
    {
        $this->authorize('angsuran.rekon');

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['Posting Date', 'Remark', 'Credit'], ';');
            fputcsv($handle, [now()->format('Y-m-d'), 'angsuran-1-KOP-2026-0001', '250000'], ';');
            fputcsv($handle, [now()->format('Y-m-d'), 'angsuran-2-KOP-2026-0007', '300000'], ';');

            fclose($handle);
        }, 'template_rekon_angsuran.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function preview(Request $request)
    {
        $this->authorize('angsuran.rekon');

        $request->validate([
            'file_csv'     => 'required|file|mimes:csv,txt|max:5120',
            'kol_tanggal'  => 'required|integer|min:0',
            'kol_berita'   => 'required|integer|min:0',
            'kol_nominal'  => 'required|integer|min:0',
            'baris_header' => 'required|integer|min:0',
        ], [
            'file_csv.required' => 'File CSV wajib diunggah.',
            'file_csv.mimes'    => 'File harus berformat CSV.',
            'file_csv.max'      => 'Ukuran file maksimal 5 MB.',
        ]);

        $file    = $request->file('file_csv');
        $content = file_get_contents($file->getRealPath());

        // Detect BOM UTF-8
        if (str_starts_with($content, "\xEF\xBB\xBF")) {
            $content = substr($content, 3);
        }

        $lines = preg_split('/\r\n|\r|\n/', $content);
        $lines = array_values(array_filter($lines, fn($l) => trim($l) !== ''));

        // Detect delimiter
        $sampleLine = $lines[0] ?? '';
        $delimiter  = (substr_count($sampleLine, ';') > substr_count($sampleLine, ',')) ? ';' : ',';

        $kolTanggal  = (int) $request->kol_tanggal;
        $kolBerita   = (int) $request->kol_berita;
        $kolNominal  = (int) $request->kol_nominal;
        $barisHeader = (int) $request->baris_header;

        // Auto-map kolom berdasarkan nama header umum mutasi bank.
        // Prioritas: Posting Date -> tanggal, Remark -> berita, Credit -> nominal.
        if ($barisHeader > 0 && isset($lines[$barisHeader - 1])) {
            $headerRow = str_getcsv(trim($lines[$barisHeader - 1]), $delimiter);
            $headerNorm = array_map(fn($h) => $this->normalizeHeader((string) $h), $headerRow);

            $idxPostingDate = $this->findHeaderIndex($headerNorm, ['posting date', 'posting_date']);
            $idxRemark      = $this->findHeaderIndex($headerNorm, ['remark', 'remarks']);
            $idxCredit      = $this->findHeaderIndex($headerNorm, ['credit', 'cr']);

            if ($idxPostingDate !== null) {
                $kolTanggal = $idxPostingDate;
            }
            if ($idxRemark !== null) {
                $kolBerita = $idxRemark;
            }
            if ($idxCredit !== null) {
                $kolNominal = $idxCredit;
            }
        }

        // Load semua anggota aktif dan pinjaman aktif
        $anggotaList = Anggota::where('status', 'aktif')
            ->select('id', 'nomor_anggota', 'nama')
            ->get()
            ->keyBy(fn($a) => strtolower($a->nomor_anggota));

        // Pinjaman aktif beserta angsuran belum bayar, dikelompokkan per anggota_id
        $pinjamanAktif = Pinjaman::whereIn('status', ['disetujui'])
            ->with(['angsuran' => fn($q) => $q->where('status', 'belum')->orderBy('ke')])
            ->get()
            ->groupBy('anggota_id');

        $hasil = [];
        foreach ($lines as $i => $line) {
            if ($i < $barisHeader) continue;

            $row = str_getcsv(trim($line), $delimiter);

            $tanggal    = trim($row[$kolTanggal] ?? '');
            $berita     = trim($row[$kolBerita] ?? '');
            $rawNominal = preg_replace('/[^0-9]/', '', $row[$kolNominal] ?? '');
            $nominal    = (int) $rawNominal;

            if (empty($berita) && $nominal === 0) continue;

            // Parse format: angsuran-{ke}-{nomor_anggota}
            $beritaLower  = strtolower($berita);
            $anggotaMatch = null;
            $angsuranKe   = null;
            $nomorDiBerita = null;
            $alasan        = null;

            // Regex: kata "angsuran" diikuti "-{angka}-{nomor_anggota}"
            if (preg_match('/angsuran-(\d+)-(.+)/i', $berita, $m)) {
                $angsuranKe    = (int) $m[1];
                $nomorDiBerita = trim($m[2]);
                $anggotaMatch  = $anggotaList->get(strtolower($nomorDiBerita));
            } else {
                $alasan = 'Format berita tidak sesuai (angsuran-{ke}-{nomor_anggota})';
            }

            // Cari angsuran yang tepat — telusuri semua pinjaman aktif anggota
            $angsuranRecord = null;
            $pinjamanRecord = null;
            if ($anggotaMatch && $angsuranKe !== null) {
                $pinjamanList = $pinjamanAktif->get($anggotaMatch->id, collect());
                foreach ($pinjamanList as $p) {
                    $found = $p->angsuran->firstWhere('ke', $angsuranKe);
                    if ($found) {
                        $angsuranRecord = $found;
                        $pinjamanRecord = $p;
                        break;
                    }
                }
            }

            if (!$alasan) {
                if (!$anggotaMatch) {
                    $alasan = "Nomor anggota '$nomorDiBerita' tidak ditemukan";
                } elseif (!$pinjamanRecord) {
                    $alasan = 'Tidak ada pinjaman aktif untuk anggota ini';
                } elseif (!$angsuranRecord) {
                    $alasan = "Angsuran ke-{$angsuranKe} belum ada atau sudah lunas";
                } elseif ($nominal <= 0) {
                    $alasan = 'Nominal tidak valid';
                }
            }

            $hasil[] = [
                'tanggal'        => $tanggal,
                'berita'         => $berita,
                'nominal'        => $nominal,
                'angsuran_ke'    => $angsuranKe,
                'angsuran_id'    => $angsuranRecord?->id,
                'jumlah_tagihan' => $angsuranRecord ? (float) $angsuranRecord->jumlah_angsuran : null,
                'anggota_id'     => $anggotaMatch?->id,
                'nomor_anggota'  => $anggotaMatch?->nomor_anggota ?? $nomorDiBerita,
                'nama_anggota'   => $anggotaMatch?->nama,
                'nomor_pinjaman' => $pinjamanRecord?->nomor_pinjaman,
                'status'         => $alasan === null ? 'ok' : 'gagal',
                'alasan'         => $alasan,
            ];
        }

        $request->session()->put('rekon_angsuran_hasil', $hasil);

        return view('koperasi.rekon-angsuran.preview', compact('hasil'));
    }

    public function import(Request $request)
    {
        $this->authorize('angsuran.rekon');

        $hasil   = $request->session()->get('rekon_angsuran_hasil', []);
        $dipilih = $request->input('dipilih', []);

        if (empty($dipilih)) {
            return back()->with('gagal', 'Tidak ada baris yang dipilih untuk diimpor.');
        }

        $berhasil = 0;
        $gagal    = 0;

        DB::transaction(function () use ($hasil, $dipilih, &$berhasil, &$gagal) {
            foreach ($dipilih as $idx) {
                $row = $hasil[(int) $idx] ?? null;
                if (!$row || $row['status'] !== 'ok') {
                    $gagal++;
                    continue;
                }

                $angsuran = Angsuran::find($row['angsuran_id']);
                if (!$angsuran || $angsuran->status === 'sudah') {
                    $gagal++;
                    continue;
                }

                $tanggalBayar  = $this->parseDate($row['tanggal']);
                $denda         = $angsuran->hitungDenda($tanggalBayar);
                $hariTerlambat = 0;

                if (Carbon::parse($tanggalBayar)->gt($angsuran->tanggal_jatuh_tempo)) {
                    $hariTerlambat = $angsuran->tanggal_jatuh_tempo
                        ->diffInDays(Carbon::parse($tanggalBayar));
                }

                $angsuran->update([
                    'status'         => 'sudah',
                    'tanggal_bayar'  => $tanggalBayar,
                    'denda'          => $denda,
                    'hari_terlambat' => $hariTerlambat,
                ]);

                $angsuran->load('pinjaman.anggota');
                $pinjaman = $angsuran->pinjaman;

                if ($pinjaman && $pinjaman->angsuran()->where('status', 'belum')->count() === 0) {
                    $pinjaman->update(['status' => 'lunas']);
                }

                $ket = 'Rekon: angsuran ke-' . $angsuran->ke . ' pinjaman ' . ($pinjaman?->nomor_pinjaman ?? '-');
                if ($denda > 0) {
                    $ket .= ' (denda Rp ' . number_format($denda, 0, ',', '.') . ')';
                }

                $this->catatLog('bayar', 'Angsuran', $angsuran->id,
                    $row['nama_anggota'], $ket);

                $berhasil++;
            }
        });

        $request->session()->forget('rekon_angsuran_hasil');

        $pesan = "{$berhasil} angsuran berhasil dicatat.";
        if ($gagal) $pesan .= " {$gagal} baris dilewati.";

        return redirect()->route('angsuran.index')->with('sukses', $pesan);
    }

    private function parseDate(string $tanggal): string
    {
        foreach (['d/m/Y', 'd-m-Y', 'Y-m-d', 'd/m/y', 'Y/m/d', 'm/d/Y', 'd M Y', 'd-M-Y'] as $format) {
            $dt = DateTime::createFromFormat($format, trim($tanggal));
            if ($dt) return $dt->format('Y-m-d');
        }
        return now()->format('Y-m-d');
    }

    private function normalizeHeader(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/\s+/', ' ', $value);
        return str_replace(['_', '-', '.'], ' ', $value);
    }

    private function findHeaderIndex(array $headers, array $candidates): ?int
    {
        foreach ($headers as $idx => $header) {
            foreach ($candidates as $candidate) {
                if ($header === $this->normalizeHeader($candidate)) {
                    return $idx;
                }
            }
        }

        return null;
    }
}
