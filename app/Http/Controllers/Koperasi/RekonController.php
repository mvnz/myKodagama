<?php

namespace App\Http\Controllers\Koperasi;

use App\Http\Controllers\Controller;

use App\Http\Controllers\Concerns\CatatAktivitas;
use App\Models\Koperasi\Anggota;
use App\Models\Koperasi\Simpanan;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RekonController extends Controller
{
    use CatatAktivitas;

    public function index()
    {
        $this->authorize('simpanan.rekon');
        return view('koperasi.rekon.index');
    }

    public function template()
    {
        $this->authorize('simpanan.rekon');

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['Posting Date', 'Remark', 'Credit'], ';');
            fputcsv($handle, [now()->format('Y-m-d'), 'WAJIB-KOP-2026-0001', '50000'], ';');
            fputcsv($handle, [now()->format('Y-m-d'), 'POKOK-KOP-2026-0007', '100000'], ';');
            fputcsv($handle, [now()->format('Y-m-d'), 'SUKARELA-KOP-2026-0010', '75000'], ';');

            fclose($handle);
        }, 'template_rekon_simpanan.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function preview(Request $request)
    {
        $this->authorize('simpanan.rekon');

        $request->validate([
            'file_csv'     => 'required|file|mimes:csv,txt|max:5120',
            'kol_tanggal'  => 'required|integer|min:0',
            'kol_berita'   => 'required|integer|min:0',
            'kol_nominal'  => 'required|integer|min:0',
            'kata_wajib'   => 'required|string|max:100',
            'kata_pokok'   => 'required|string|max:100',
            'kata_sukarela'=> 'required|string|max:100',
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

        $kataWajib    = strtolower(trim($request->kata_wajib));
        $kataPokok    = strtolower(trim($request->kata_pokok));
        $kataSukarela = strtolower(trim($request->kata_sukarela));

        // Load all active anggota
        $anggotaList = Anggota::where('status', 'aktif')
            ->select('id', 'nomor_anggota', 'nama', 'jenis_anggota')
            ->get();

        // Load simpanan yang sudah ada (untuk cek duplikat per anggota+jenis+bulan)
        $simpananAda = Simpanan::select('anggota_id', 'jenis_simpanan', 'tanggal')
            ->get()
            ->map(function ($s) {
                $thn = (int) date('Y', strtotime((string) $s->tanggal));
                $bln = (int) date('n', strtotime((string) $s->tanggal));

                return $s->anggota_id . '|' . $s->jenis_simpanan . '|' . $thn . '-' . $bln;
            })
            ->flip();

        $hasil = [];
        foreach ($lines as $i => $line) {
            if ($i < $barisHeader) continue; // skip header rows

            $row = str_getcsv(trim($line), $delimiter);

            $tanggal = trim($row[$kolTanggal] ?? '');
            $berita  = trim($row[$kolBerita] ?? '');
            $rawNominal = preg_replace('/[^0-9]/', '', $row[$kolNominal] ?? '');
            $nominal = (int) $rawNominal;

            if (empty($berita) && $nominal === 0) continue;

            $beritaLower = strtolower($berita);

            // Detect jenis_simpanan AND extract nomor_anggota from format: {kata_kunci}-{nomor_anggota}
            $jenis          = null;
            $nomorDiBerita  = null;

            $pasangan = [
                'wajib'    => $kataWajib,
                'pokok'    => $kataPokok,
                'sukarela' => $kataSukarela,
            ];

            foreach ($pasangan as $tipe => $kata) {
                if ($kata === '') continue;
                $pos = strpos($beritaLower, $kata);
                if ($pos !== false) {
                    $jenis = $tipe;
                    // Ambil nomor anggota setelah kata_kunci + "-"
                    $setelahKata = substr($berita, $pos + strlen($kata));
                    if (str_starts_with($setelahKata, '-')) {
                        $setelahKata    = substr($setelahKata, 1); // buang "-"
                        // Ambil token sampai spasi atau akhir string
                        $token          = preg_split('/\s+/', trim($setelahKata))[0] ?? '';
                        $nomorDiBerita  = $token !== '' ? $token : null;
                    }
                    break;
                }
            }

            // Match anggota berdasarkan nomor yang diekstrak dari berita
            $anggotaMatch = null;
            if ($nomorDiBerita) {
                $anggotaMatch = $anggotaList->first(
                    fn($a) => $a->nomor_anggota && strtolower($a->nomor_anggota) === strtolower($nomorDiBerita)
                );
            }

            // Determine status
            $alasan = null;
            if (!$jenis) {
                $alasan = 'Jenis simpanan tidak terdeteksi dari berita';
            } elseif (!$nomorDiBerita) {
                $alasan = 'Format berita tidak sesuai (kata_kunci-NOMOR_ANGGOTA)';
            } elseif (!$anggotaMatch) {
                $alasan = "Nomor anggota '$nomorDiBerita' tidak ditemukan";
            } elseif ($nominal <= 0) {
                $alasan = 'Nominal tidak valid';
            } else {
                // Cek duplikat: sudah ada simpanan jenis ini bulan/tahun yang sama?
                $tglParsed = $this->parseDate($tanggal);
                $thn = (int) date('Y', strtotime($tglParsed));
                $bln = (int) date('n', strtotime($tglParsed));
                $dupKey = $anggotaMatch->id . '|' . $jenis . '|' . $thn . '-' . $bln;
                if (isset($simpananAda[$dupKey])) {
                    $alasan = 'Sudah ada simpanan ' . $jenis . ' bulan ' . $bln . '/' . $thn;
                }
            }

            $hasil[] = [
                'tanggal'        => $tanggal,
                'berita'         => $berita,
                'nominal'        => $nominal,
                'jenis_simpanan' => $jenis,
                'anggota_id'     => $anggotaMatch?->id,
                'nomor_anggota'  => $anggotaMatch?->nomor_anggota,
                'nama_anggota'   => $anggotaMatch?->nama,
                'jenis_anggota'  => $anggotaMatch?->jenis_anggota,
                'status'         => $alasan === null ? 'ok' : 'gagal',
                'alasan'         => $alasan,
            ];
        }

        $request->session()->put('rekon_hasil', $hasil);

        return view('koperasi.rekon.preview', compact('hasil'));
    }

    public function import(Request $request)
    {
        $this->authorize('simpanan.rekon');

        $hasil   = $request->session()->get('rekon_hasil', []);
        $dipilih = $request->input('dipilih', []);

        if (empty($dipilih)) {
            return back()->with('gagal', 'Tidak ada baris yang dipilih untuk diimpor.');
        }

        $berhasil = 0;
        $gagal    = 0;

        DB::transaction(function () use ($hasil, $dipilih, &$berhasil, &$gagal) {
            // Track duplikat dalam satu batch import
            $dupBatch = [];

            foreach ($dipilih as $idx) {
                $row = $hasil[(int) $idx] ?? null;
                if (!$row || $row['status'] !== 'ok') {
                    $gagal++;
                    continue;
                }

                $tglParsed = $this->parseDate($row['tanggal']);
                $thn = (int) date('Y', strtotime($tglParsed));
                $bln = (int) date('n', strtotime($tglParsed));
                $dupKey = $row['anggota_id'] . '|' . $row['jenis_simpanan'] . '|' . $thn . '-' . $bln;

                // Skip jika duplikat dalam batch ini
                if (isset($dupBatch[$dupKey])) {
                    $gagal++;
                    continue;
                }
                $dupBatch[$dupKey] = true;

                $simpanan = Simpanan::create([
                    'anggota_id'     => $row['anggota_id'],
                    'jenis_anggota'  => $row['jenis_anggota'],
                    'jenis_simpanan' => $row['jenis_simpanan'],
                    'jumlah'         => $row['nominal'],
                    'tanggal'        => $tglParsed,
                    'keterangan'     => 'Rekon: ' . $row['berita'],
                    'user_id'        => Auth::id(),
                ]);

                $this->catatLog(
                    'tambah', 'Simpanan', $simpanan->id,
                    $row['nama_anggota'],
                    'Import rekon: simpanan ' . $row['jenis_simpanan'] . ' Rp ' . number_format($row['nominal'], 0, ',', '.'),
                    null,
                    $simpanan->toArray()
                );

                $berhasil++;
            }
        });

        $request->session()->forget('rekon_hasil');

        $pesan = "{$berhasil} data simpanan berhasil diimpor.";
        if ($gagal) $pesan .= " {$gagal} baris dilewati.";

        return redirect()->route('simpanan.index')->with('sukses', $pesan);
    }

    private function parseDate(string $tanggal): string
    {
        foreach (['d/m/Y', 'd-m-Y', 'Y-m-d', 'd/m/y', 'Y/m/d', 'm/d/Y', 'd M Y', 'd-M-Y'] as $format) {
            $dt = DateTime::createFromFormat($format, trim($tanggal));
            if ($dt) return $dt->format('Y-m-d');
        }
        return now()->format('Y-m-d');
    }
}
