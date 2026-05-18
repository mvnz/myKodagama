<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShuAnggota extends Model
{
    protected $table = 'shu_anggota';

    protected $fillable = [
        'shu_id', 'anggota_id', 'total_simpanan', 'total_pinjaman',
        'jasa_modal', 'jasa_usaha', 'total_shu_anggota', 'keterangan',
    ];

    protected $casts = [
        'total_simpanan'   => 'decimal:2',
        'total_pinjaman'   => 'decimal:2',
        'jasa_modal'       => 'decimal:2',
        'jasa_usaha'       => 'decimal:2',
        'total_shu_anggota'=> 'decimal:2',
    ];

    public function shu()
    {
        return $this->belongsTo(Shu::class);
    }

    public function anggota()
    {
        return $this->belongsTo(Anggota::class);
    }
}
