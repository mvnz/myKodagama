<?php

namespace App\Models\Koperasi;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengeluaran extends Model
{
    use HasFactory;

    protected $table = 'pengeluaran';

    protected $fillable = [
        'tanggal', 'judul', 'kategori', 'jumlah', 'keterangan', 'bukti', 'user_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jumlah'  => 'decimal:2',
    ];

    public function pencatat()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function labelKategori(): array
    {
        return [
            'gaji_honor'       => 'Gaji / Honor Pengurus',
            'listrik_air'      => 'Listrik & Air',
            'sewa_gedung'      => 'Sewa Gedung / Kantor',
            'atk'              => 'ATK & Perlengkapan',
            'rapat_rat'        => 'Rapat & RAT',
            'sosial'           => 'Kegiatan Sosial',
            'penyusutan'       => 'Penyusutan Aset',
            'administrasi_bank'=> 'Administrasi Bank',
            'lain_lain'        => 'Lain-lain',
        ];
    }

    public function getLabelKategoriAttribute(): string
    {
        return static::labelKategori()[$this->kategori] ?? $this->kategori;
    }
}
