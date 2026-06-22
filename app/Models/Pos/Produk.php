<?php

namespace App\Models\Pos;

use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    protected $table = 'pos_produk';
    protected $fillable = ['kategori_id','supplier_id','kode','barcode','nama','deskripsi','harga_beli','harga_jual','stok','satuan','is_aktif'];

    protected $casts = ['is_aktif' => 'boolean'];

    public static function generateKode(): string
    {
        $max = static::where('kode', 'regexp', '^PRD-[0-9]+$')
            ->selectRaw('MAX(CAST(SUBSTRING(kode,5) AS UNSIGNED)) as maks')
            ->value('maks');
        return 'PRD-' . str_pad(($max ?? 0) + 1, 4, '0', STR_PAD_LEFT);
    }

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Toko::class, 'supplier_id');
    }

    public function items()
    {
        return $this->hasMany(Item::class, 'produk_id');
    }
}
