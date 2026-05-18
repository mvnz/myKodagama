<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\Simpanan;
use App\Models\Pinjaman;
use App\Models\Angsuran;
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
}
