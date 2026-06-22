<?php

namespace App\Models\Pos;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $table = 'pos_transaksi';
    protected $fillable = ['nomor','kasir_id','subtotal','diskon','total','bayar','kembalian','metode_bayar','catatan','tanggal'];

    protected $casts = ['tanggal' => 'date'];

    public static function generateNomor(): string
    {
        $hari = now()->format('Ymd');
        $prefix = 'TRX-' . $hari . '-';
        $max = static::where('nomor', 'like', $prefix . '%')
            ->selectRaw('MAX(CAST(SUBSTRING(nomor, ' . (strlen($prefix) + 1) . ') AS UNSIGNED)) as maks')
            ->value('maks');
        return $prefix . str_pad(($max ?? 0) + 1, 4, '0', STR_PAD_LEFT);
    }

    public function kasir()
    {
        return $this->belongsTo(User::class, 'kasir_id');
    }

    public function items()
    {
        return $this->hasMany(Item::class, 'transaksi_id');
    }
}
