<?php

namespace App\Models\Pos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Kategori extends Model
{
    protected $table = 'pos_kategori';
    protected $fillable = ['nama', 'slug', 'urutan'];

    protected static function booted(): void
    {
        static::creating(function ($k) {
            if (empty($k->slug)) {
                $k->slug = Str::slug($k->nama);
            }
        });
    }

    public function produk()
    {
        return $this->hasMany(Produk::class, 'kategori_id');
    }
}
