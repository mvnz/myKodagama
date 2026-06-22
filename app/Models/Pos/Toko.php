<?php

namespace App\Models\Pos;

use Illuminate\Database\Eloquent\Model;

class Toko extends Model
{
    protected $table = 'pos_toko';
    protected $fillable = [
        'nama', 'pemilik', 'telepon', 'email',
        'alamat', 'kota', 'deskripsi', 'logo', 'saldo',
    ];

    public function produk()
    {
        return $this->hasMany(Produk::class, 'supplier_id');
    }

    public function saldoLog()
    {
        return $this->hasMany(SaldoLog::class, 'toko_id');
    }

    public function pencairan()
    {
        return $this->hasMany(Pencairan::class, 'toko_id');
    }
}
