<?php

namespace App\Models\Pos;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $table = 'pos_transaksi';
    protected $fillable = ['nomor','kasir_id','subtotal','diskon','total','bayar','kembalian','metode_bayar','catatan','tanggal'];

    protected $casts = ['tanggal' => 'date'];

    public static function generateNomor(string $tanggal = null): string
    {
        $hari = $tanggal
            ? \Illuminate\Support\Carbon::parse($tanggal)->format('Ymd')
            : now()->format('Ymd');

        $prefix = 'TRX-' . $hari . '-';

        $max = static::query()
            ->where('nomor', 'like', $prefix . '%')
            ->pluck('nomor')
            ->filter(fn($nomor) => preg_match('/^' . preg_quote($prefix, '/') . '\\d+$/', (string) $nomor) === 1)
            ->map(fn($nomor) => (int) substr((string) $nomor, strlen($prefix)))
            ->max();

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
