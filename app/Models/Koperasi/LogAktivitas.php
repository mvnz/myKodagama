<?php

namespace App\Models\Koperasi;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogAktivitas extends Model
{
    public $timestamps = false;

    protected $table = 'log_aktivitas';

    protected $fillable = [
        'user_id', 'aksi', 'modul', 'model_id', 'label',
        'keterangan', 'data_lama', 'data_baru', 'ip_address',
    ];

    protected $casts = [
        'data_lama'  => 'array',
        'data_baru'  => 'array',
        'created_at' => 'datetime',
    ];

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Warna badge Bootstrap berdasarkan aksi */
    public function warnaBadge(): string
    {
        return match ($this->aksi) {
            'tambah'  => 'success',
            'ubah'    => 'warning',
            'hapus'   => 'danger',
            'bayar'   => 'info',
            'setujui' => 'primary',
            'tolak'   => 'secondary',
            default   => 'dark',
        };
    }

    /** Ikon Bootstrap Icons berdasarkan aksi */
    public function ikonAksi(): string
    {
        return match ($this->aksi) {
            'tambah'  => 'bi-plus-circle-fill',
            'ubah'    => 'bi-pencil-fill',
            'hapus'   => 'bi-trash-fill',
            'bayar'   => 'bi-cash-coin',
            'setujui' => 'bi-check-circle-fill',
            'tolak'   => 'bi-x-circle-fill',
            default   => 'bi-activity',
        };
    }
}
