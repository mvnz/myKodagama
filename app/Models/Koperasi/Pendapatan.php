<?php

namespace App\Models\Koperasi;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pendapatan extends Model
{
    use HasFactory;

    protected $table = 'pendapatan';

    protected $fillable = [
        'tanggal', 'judul', 'kategori', 'jumlah', 'keterangan', 'user_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jumlah'  => 'decimal:2',
    ];

    public static function labelKategori(): array
    {
        return [
            'jasa_admin'     => 'Jasa Administrasi',
            'provisi'        => 'Provisi Pinjaman',
            'bunga_pinjaman' => 'Bunga Pinjaman',
            'bunga_simpanan' => 'Bunga Simpanan',
            'denda'          => 'Denda Keterlambatan',
            'hibah'          => 'Hibah / Donasi',
            'iuran_anggota'  => 'Iuran Anggota',
            'lain_lain'      => 'Lain-lain',
        ];
    }

    public function getLabelKategoriAttribute(): string
    {
        return static::labelKategori()[$this->kategori] ?? $this->kategori;
    }

    public function pencatat()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
