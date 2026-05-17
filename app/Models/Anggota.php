<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Anggota extends Model
{
    use HasFactory;

    protected $table = 'anggota';

    protected $fillable = [
        'nomor_anggota', 'nama', 'jenis_anggota', 'nik', 'tempat_lahir', 'tanggal_lahir',
        'jenis_kelamin', 'alamat', 'telepon', 'unit_kerja_id', 'email',
        'status', 'tanggal_bergabung', 'foto',
    ];

    public static function generateNomor(): string
    {
        $tahun  = Carbon::now()->format('Y');
        $bulan  = Carbon::now()->format('m');
        $urutan = static::whereYear('tanggal_bergabung', $tahun)->count() + 1;
        return 'KOP-' . $tahun . $bulan . '-' . str_pad($urutan, 4, '0', STR_PAD_LEFT);
    }

    protected $casts = [
        'tanggal_lahir' => 'date',
        'tanggal_bergabung' => 'date',
    ];

    public function simpanan()
    {
        return $this->hasMany(Simpanan::class);
    }

    public function pinjaman()
    {
        return $this->hasMany(Pinjaman::class);
    }

    public function user()
    {
        return $this->hasOne(\App\Models\User::class, 'anggota_id');
    }

    public function unitKerja()
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_id');
    }

    public function totalSimpanan()
    {
        return $this->simpanan()->sum('jumlah');
    }

    public function totalPinjamanAktif()
    {
        return $this->pinjaman()
            ->whereIn('status', ['disetujui'])
            ->sum('jumlah_pinjaman');
    }
}
