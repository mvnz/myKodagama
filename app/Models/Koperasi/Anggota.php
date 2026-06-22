<?php

namespace App\Models\Koperasi;

use App\Models\User;
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

    public static function generateNomor($tanggalBergabung = null): string
    {
        $tanggal = $tanggalBergabung
            ? Carbon::parse($tanggalBergabung)
            : Carbon::now();

        $tahun  = $tanggal->format('Y');
        $prefix = 'KOP-' . $tahun . '-';

        // Urutan belakang bersifat global (tidak reset per tahun).
        $last = static::whereRaw('nomor_anggota REGEXP ?', ['^KOP-[0-9]{4}-[0-9]{4}$'])
            ->orderByRaw('CAST(SUBSTRING(nomor_anggota, 10, 4) AS UNSIGNED) DESC')
            ->value('nomor_anggota');

        $urutan = 1;
        if ($last) {
            $urutan = (int) substr($last, -4) + 1;
        }

        return $prefix . str_pad($urutan, 4, '0', STR_PAD_LEFT);
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
        return $this->hasOne(User::class, 'anggota_id');
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
