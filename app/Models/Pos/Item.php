<?php

namespace App\Models\Pos;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $table = 'pos_item';
    public $timestamps = false;
    protected $fillable = ['transaksi_id','produk_id','nama_produk','kode_produk','harga','harga_pokok','qty','subtotal'];

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'transaksi_id');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }
}
