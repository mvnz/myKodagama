<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Koperasi\LogAktivitas;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait CatatAktivitas
{
    /**
     * Catat satu entri log aktivitas.
     *
     * @param  string       $aksi      tambah|ubah|hapus|bayar|setujui|tolak
     * @param  string       $modul     Anggota|Simpanan|Pinjaman|Angsuran|Role|Pengaturan
     * @param  int|null     $modelId   Primary key record yang terpengaruh
     * @param  string|null  $label     Teks yang mudah dibaca (nama/nomor)
     * @param  string|null  $keterangan Deskripsi bebas
     * @param  array|null   $dataLama  Data sebelum diubah (untuk ubah/hapus)
     * @param  array|null   $dataBaru  Data sesudah diubah (untuk tambah/ubah)
     */
    protected function catatLog(
        string  $aksi,
        string  $modul,
        ?int    $modelId    = null,
        ?string $label      = null,
        ?string $keterangan = null,
        ?array  $dataLama   = null,
        ?array  $dataBaru   = null,
    ): void {
        LogAktivitas::create([
            'user_id'    => Auth::id(),
            'aksi'       => $aksi,
            'modul'      => $modul,
            'model_id'   => $modelId,
            'label'      => $label,
            'keterangan' => $keterangan,
            'data_lama'  => $dataLama,
            'data_baru'  => $dataBaru,
            'ip_address' => Request::ip(),
        ]);
    }
}
