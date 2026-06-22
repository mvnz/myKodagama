<?php

namespace App\Models\Pos;

use Illuminate\Database\Eloquent\Model;

class Pencairan extends Model
{
    protected $table    = 'pos_pencairan';
    public    $timestamps = false;

    protected $fillable = [
        'toko_id', 'jumlah', 'metode',
        'nama_penerima', 'nama_bank', 'no_rekening',
        'keterangan', 'saldo_sebelum', 'saldo_sesudah',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function toko()
    {
        return $this->belongsTo(Toko::class, 'toko_id');
    }
}
