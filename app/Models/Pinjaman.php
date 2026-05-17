<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pinjaman extends Model
{
    use HasFactory;

    protected $table = 'pinjaman';

    protected $fillable = [
        'nomor_pinjaman', 'anggota_id', 'jumlah_pinjaman', 'jangka_waktu',
        'bunga', 'cicilan_per_bulan', 'tujuan', 'jenis_pinjaman', 'status',
        'tanggal_pengajuan', 'tanggal_verifikasi', 'verifikator_id',
        'tanggal_persetujuan', 'user_id',
    ];

    protected $casts = [
        'tanggal_pengajuan'   => 'date',
        'tanggal_verifikasi'  => 'date',
        'tanggal_persetujuan' => 'date',
        'jumlah_pinjaman'     => 'decimal:2',
        'bunga'               => 'decimal:2',
        'cicilan_per_bulan'   => 'decimal:2',
    ];

    public function anggota()
    {
        return $this->belongsTo(Anggota::class);
    }

    public function angsuran()
    {
        return $this->hasMany(Angsuran::class);
    }

    public function petugas()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sisaAngsuran()
    {
        return $this->angsuran()->where('status', 'belum')->count();
    }

    public function totalTerbayar()
    {
        return $this->angsuran()->where('status', 'sudah')->sum('jumlah_angsuran');
    }
}
