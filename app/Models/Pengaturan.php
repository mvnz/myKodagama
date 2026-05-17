<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengaturan extends Model
{
    use HasFactory;

    protected $table = 'pengaturan';

    protected $fillable = ['kunci', 'nilai', 'grup', 'label', 'tipe'];

    /** Ambil nilai setting berdasarkan kunci. */
    public static function get(string $kunci, mixed $default = null): mixed
    {
        return static::where('kunci', $kunci)->value('nilai') ?? $default;
    }

    /** Simpan atau perbarui nilai setting. */
    public static function set(string $kunci, mixed $nilai, string $grup = 'umum'): void
    {
        static::updateOrCreate(
            ['kunci' => $kunci],
            ['nilai' => $nilai, 'grup' => $grup]
        );
    }

    /** Kembalikan semua setting sebagai array kunci => nilai. */
    public static function semuaNilai(): array
    {
        return static::pluck('nilai', 'kunci')->toArray();
    }
}
