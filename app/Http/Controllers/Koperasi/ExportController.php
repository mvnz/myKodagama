<?php

namespace App\Http\Controllers\Koperasi;

use App\Http\Controllers\Controller;

use App\Models\Koperasi\Anggota;
use App\Models\Koperasi\Simpanan;
use App\Models\Koperasi\Pinjaman;
use App\Models\Koperasi\Angsuran;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function anggota(Request $request): StreamedResponse
    {
        $this->authorize('anggota.lihat');

        $query = Anggota::with('unitKerja')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest();

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            // BOM agar Excel bisa baca UTF-8
            fputs($handle, "\xEF\xBB\xBF");
            fputcsv($handle, [
                'No', 'Nomor Anggota', 'Nama', 'NIK', 'Jenis Anggota',
                'Unit Kerja', 'Telepon', 'Email', 'Tanggal Bergabung', 'Status',
            ]);
            $no = 1;
            $query->each(function ($row) use ($handle, &$no) {
                fputcsv($handle, [
                    $no++,
                    $row->nomor_anggota,
                    $row->nama,
                    $row->nik,
                    $row->jenis_anggota,
                    $row->unitKerja->nama ?? '-',
                    $row->telepon,
                    $row->email,
                    $row->tanggal_bergabung?->format('d/m/Y'),
                    $row->status,
                ]);
            });
            fclose($handle);
        }, 'anggota_' . now()->format('Ymd_His') . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function simpanan(Request $request): StreamedResponse
    {
        $this->authorize('simpanan.lihat');

        $query = Simpanan::with('anggota')
            ->when($request->tahun, fn($q) => $q->whereYear('tanggal', $request->tahun))
            ->when($request->bulan, fn($q) => $q->whereMonth('tanggal', $request->bulan))
            ->latest('tanggal');

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");
            fputcsv($handle, [
                'No', 'Tanggal', 'Nomor Anggota', 'Nama Anggota',
                'Jenis Simpanan', 'Jumlah', 'Keterangan',
            ]);
            $no = 1;
            $query->each(function ($row) use ($handle, &$no) {
                fputcsv($handle, [
                    $no++,
                    $row->tanggal?->format('d/m/Y'),
                    $row->anggota->nomor_anggota ?? '-',
                    $row->anggota->nama ?? '-',
                    $row->jenis_simpanan,
                    number_format($row->jumlah, 0, ',', '.'),
                    $row->keterangan,
                ]);
            });
            fclose($handle);
        }, 'simpanan_' . now()->format('Ymd_His') . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function pinjaman(Request $request): StreamedResponse
    {
        $this->authorize('pinjaman.lihat');

        $query = Pinjaman::with('anggota')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->tahun, fn($q) => $q->whereYear('tanggal_pengajuan', $request->tahun))
            ->latest('tanggal_pengajuan');

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");
            fputcsv($handle, [
                'No', 'Nomor Pinjaman', 'Tanggal Pengajuan', 'Nomor Anggota', 'Nama Anggota',
                'Jumlah Pinjaman', 'Jangka (Bulan)', 'Bunga (%)', 'Cicilan/Bulan',
                'Jenis Pinjaman', 'Status',
            ]);
            $no = 1;
            $query->each(function ($row) use ($handle, &$no) {
                fputcsv($handle, [
                    $no++,
                    $row->nomor_pinjaman,
                    $row->tanggal_pengajuan?->format('d/m/Y'),
                    $row->anggota->nomor_anggota ?? '-',
                    $row->anggota->nama ?? '-',
                    number_format($row->jumlah_pinjaman, 0, ',', '.'),
                    $row->jangka_waktu,
                    $row->bunga,
                    number_format($row->cicilan_per_bulan, 0, ',', '.'),
                    $row->jenis_pinjaman,
                    $row->status,
                ]);
            });
            fclose($handle);
        }, 'pinjaman_' . now()->format('Ymd_His') . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function angsuran(Request $request): StreamedResponse
    {
        $this->authorize('angsuran.lihat');

        $query = Angsuran::with(['pinjaman.anggota'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->tahun, fn($q) => $q->whereYear('tanggal_jatuh_tempo', $request->tahun))
            ->when($request->bulan, fn($q) => $q->whereMonth('tanggal_jatuh_tempo', $request->bulan))
            ->orderBy('tanggal_jatuh_tempo');

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");
            fputcsv($handle, [
                'No', 'Nomor Pinjaman', 'Nama Anggota', 'Ke-', 'Jatuh Tempo',
                'Tanggal Bayar', 'Jumlah Angsuran', 'Pokok', 'Bunga', 'Denda', 'Status',
            ]);
            $no = 1;
            $query->each(function ($row) use ($handle, &$no) {
                fputcsv($handle, [
                    $no++,
                    $row->pinjaman->nomor_pinjaman ?? '-',
                    $row->pinjaman->anggota->nama ?? '-',
                    $row->ke,
                    $row->tanggal_jatuh_tempo?->format('d/m/Y'),
                    $row->tanggal_bayar?->format('d/m/Y'),
                    number_format($row->jumlah_angsuran, 0, ',', '.'),
                    number_format($row->pokok, 0, ',', '.'),
                    number_format($row->bunga, 0, ',', '.'),
                    number_format($row->denda, 0, ',', '.'),
                    $row->status,
                ]);
            });
            fclose($handle);
        }, 'angsuran_' . now()->format('Ymd_His') . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function templateAnggota(): StreamedResponse
    {
        return $this->templateCsv('template_import_anggota.csv',
            ['nomor_anggota', 'nama', 'nik', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir', 'alamat', 'telepon', 'tanggal_bergabung', 'jenis_anggota', 'email', 'status'],
            [
                ['A-0001', 'Budi Santoso', '3201010101900001', 'Laki-laki', 'Bandung', '1990-01-01', 'Jl. Merdeka No. 1', '081234567890', '2024-01-15', 'Biasa', 'budi@example.com', 'aktif'],
                ['A-0002', 'Siti Aminah', '3201010202920002', 'Perempuan', 'Jakarta', '1992-02-02', 'Jl. Sudirman No. 2', '081298765432', '2024-02-01', 'Biasa', 'siti@example.com', 'aktif'],
            ]);
    }

    public function templateSimpanan(): StreamedResponse
    {
        return $this->templateCsv('template_import_simpanan.csv',
            ['nomor_anggota', 'jenis_simpanan', 'jumlah', 'tanggal', 'keterangan'],
            [
                ['A-0001', 'pokok', '100000', '2024-01-15', 'Simpanan pokok awal'],
                ['A-0001', 'wajib', '50000', '2024-02-15', 'Simpanan wajib bulanan'],
            ]);
    }

    public function templateUnitKerja(): StreamedResponse
    {
        return $this->templateCsv('template_import_unit_kerja.csv',
            ['kode', 'nama', 'singkatan', 'keterangan'],
            [
                ['UK-01', 'Dinas Pendidikan', 'DISDIK', 'Contoh keterangan'],
                ['UK-02', 'Dinas Kesehatan', 'DINKES', ''],
            ]);
    }

    public function templatePinjaman(): StreamedResponse
    {
        return $this->templateCsv('template_import_pinjaman.csv',
            ['nomor_anggota', 'nomor_pinjaman', 'jenis_pinjaman', 'jumlah_pinjaman', 'jangka_waktu', 'bunga', 'tujuan', 'tanggal_pengajuan', 'tanggal_persetujuan', 'status', 'angsuran_terbayar'],
            [
                ['A-0001', 'PIN-202401-0001', 'Konsumtif', '5000000', '12', '1.5', 'Renovasi rumah', '2024-01-10', '2024-01-12', 'disetujui', '3'],
                ['A-0002', '', 'Produktif', '3000000', '6', '1.5', 'Modal usaha', '2023-06-01', '2023-06-03', 'lunas', ''],
            ]);
    }

    public function templateAngsuran(): StreamedResponse
    {
        return $this->templateCsv('template_import_angsuran.csv',
            ['nomor_pinjaman', 'ke', 'tanggal_bayar', 'denda'],
            [
                ['PIN-202401-0001', '1', '2024-02-12', ''],
                ['PIN-202401-0001', '2', '2024-03-15', '5000'],
            ]);
    }

    private function templateCsv(string $nama, array $header, array $contoh): StreamedResponse
    {
        return response()->streamDownload(function () use ($header, $contoh) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $header, ';');
            foreach ($contoh as $row) {
                fputcsv($handle, $row, ';');
            }
            fclose($handle);
        }, $nama, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function importErrors(Request $request, string $jenis): StreamedResponse
    {
        $identityFields = match ($jenis) {
            'anggota' => ['nomor', 'nama'],
            'simpanan', 'pinjaman' => ['nomor'],
            'unit-kerja' => ['kode'],
            'angsuran' => ['nomor', 'ke'],
        };
        $errors = $request->session()->pull('import_errors.' . $jenis);
        abort_if (! is_array($errors) || $errors === [], 404);

        $headers = [
            'anggota' => ['Baris', 'Nomor Anggota', 'Nama', 'Pesan Error'],
            'simpanan', 'pinjaman' => ['Baris', 'Nomor Anggota', 'Pesan Error'],
            'unit-kerja' => ['Baris', 'Kode Unit Kerja', 'Pesan Error'],
            'angsuran' => ['Baris', 'Nomor Pinjaman', 'Angsuran Ke', 'Pesan Error'],
        ][$jenis];

        return response()->streamDownload(function () use ($errors, $identityFields, $headers) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $headers);

            foreach ($errors as $row) {
                $values = [$row['baris'] ?? ''];
                foreach ($identityFields as $field) {
                    $values[] = $row[$field] ?? '';
                }
                $values[] = $row['pesan'] ?? '';

                $values = array_map(static function ($value) {
                    $value = (string) $value;

                    return preg_match('/^[\s]*[=+\-@]/u', $value) ? "'" . $value : $value;
                }, $values);

                fputcsv($handle, $values);
            }

            fclose($handle);
        }, 'error_import_' . $jenis . '_' . now()->format('Ymd_His') . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
