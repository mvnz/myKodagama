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
        $max = static::query()
            ->whereNotNull('kode')
            ->pluck('kode')
            ->filter(fn($kode) => preg_match('/^PRD-\d+$/', (string) $kode) === 1)
            ->map(fn($kode) => (int) substr((string) $kode, 4))
            ->max();

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
