<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Angsuran extends Model
{
    use HasFactory;

    protected $table = 'angsuran';

    protected $fillable = [
        'pinjaman_id', 'ke', 'jumlah_angsuran', 'pokok', 'bunga', 'denda', 'hari_terlambat',
        'tanggal_jatuh_tempo', 'tanggal_bayar', 'status',
    ];

    protected $casts = [
        'tanggal_jatuh_tempo' => 'date',
        'tanggal_bayar' => 'date',
        'jumlah_angsuran' => 'decimal:2',
        'pokok' => 'decimal:2',
        'bunga' => 'decimal:2',
        'denda' => 'decimal:2',
    ];

    /** Total yang harus dibayar termasuk denda. */
    public function getTotalTagihanAttribute(): float
    {
        return (float) $this->jumlah_angsuran + (float) $this->denda;
    }

    /** Hitung denda keterlambatan berdasarkan tanggal bayar. */
    public function hitungDenda(?string $tanggalBayar = null): float
    {
        $bayar = $tanggalBayar ? \Illuminate\Support\Carbon::parse($tanggalBayar) : now();
        if ($this->status === 'sudah' || $bayar->lte($this->tanggal_jatuh_tempo)) {
            return 0;
        }
        $hari        = $this->tanggal_jatuh_tempo->diffInDays($bayar);
        $bungaBulanan = (float) \App\Models\Pengaturan::get('sp_bunga_keterlambatan', 2);
        // Konversi % per bulan → per hari, kalikan dengan hari terlambat
        $dendaHarian = ($bungaBulanan / 100 / 30) * (float) $this->jumlah_angsuran;
        return round($dendaHarian * $hari, 2);
    }

    public function pinjaman()
    {
        return $this->belongsTo(Pinjaman::class);
    }
}
