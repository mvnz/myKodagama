<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitKerja extends Model
{
    use HasFactory;

    protected $table = 'unit_kerja';

    protected $fillable = [
        'kode', 'nama', 'singkatan', 'keterangan', 'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    public function anggota()
    {
        return $this->hasMany(Anggota::class, 'unit_kerja_id');
    }
}
