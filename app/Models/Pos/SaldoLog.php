<?php

namespace App\Models\Pos;

use Illuminate\Database\Eloquent\Model;

class SaldoLog extends Model
{
    protected $table = 'pos_saldo_log';
    public $timestamps = false;

    protected $fillable = [
        'toko_id', 'transaksi_id', 'jumlah',
        'saldo_sebelum', 'saldo_sesudah', 'keterangan',
    ];

    protected $casts = ['created_at' => 'datetime'];

    public function toko()
    {
        return $this->belongsTo(Toko::class, 'toko_id');
    }

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'transaksi_id');
    }
}
