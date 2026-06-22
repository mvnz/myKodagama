<?php

namespace App\Models\Koperasi;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shu extends Model
{
    use HasFactory;

    protected $table = 'shu';

    protected $fillable = [
        'tahun', 'total_pendapatan', 'total_biaya', 'total_shu',
        'persentase_jasa_modal', 'persentase_jasa_usaha',
        'status', 'keterangan', 'user_id',
    ];

    protected $casts = [
        'total_pendapatan'       => 'decimal:2',
        'total_biaya'            => 'decimal:2',
        'total_shu'              => 'decimal:2',
        'persentase_jasa_modal'  => 'decimal:2',
        'persentase_jasa_usaha'  => 'decimal:2',
    ];

    public function anggota()
    {
        return $this->hasMany(ShuAnggota::class, 'shu_id');
    }

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
