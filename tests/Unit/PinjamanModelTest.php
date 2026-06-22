<?php

namespace Tests\Unit;

use App\Models\Koperasi\Anggota;
use App\Models\Koperasi\Angsuran;
use App\Models\Koperasi\Pinjaman;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PinjamanModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_pinjaman_status_default_diajukan(): void
    {
        $pinjaman = Pinjaman::factory()->create();
        $this->assertEquals('diajukan', $pinjaman->status);
    }

    public function test_pinjaman_cicilan_dihitung_dengan_benar(): void
    {
        $jumlah  = 1_200_000;
        $jangka  = 12;
        $bunga   = 1.5;
        $pokok   = $jumlah / $jangka;
        $bungaNom = $jumlah * $bunga / 100;
        $expected = round($pokok + $bungaNom, 2);

        $pinjaman = Pinjaman::factory()->create([
            'jumlah_pinjaman'   => $jumlah,
            'jangka_waktu'      => $jangka,
            'bunga'             => $bunga,
            'cicilan_per_bulan' => $expected,
        ]);

        $this->assertEquals($expected, $pinjaman->cicilan_per_bulan);
    }

    public function test_pinjaman_memiliki_relasi_angsuran(): void
    {
        $pinjaman = Pinjaman::factory()->disetujui()->create();
        Angsuran::factory()->count(12)->create(['pinjaman_id' => $pinjaman->id]);

        $this->assertCount(12, $pinjaman->fresh()->angsuran);
    }

    public function test_pinjaman_memiliki_relasi_anggota(): void
    {
        $anggota  = Anggota::factory()->create();
        $pinjaman = Pinjaman::factory()->create(['anggota_id' => $anggota->id]);

        $this->assertEquals($anggota->id, $pinjaman->fresh()->anggota->id);
    }

    public function test_sisa_angsuran_belum_bayar(): void
    {
        $pinjaman = Pinjaman::factory()->disetujui()->create();
        Angsuran::factory()->count(5)->create(['pinjaman_id' => $pinjaman->id, 'status' => 'belum']);
        Angsuran::factory()->count(3)->sudahBayar()->create(['pinjaman_id' => $pinjaman->id]);

        $this->assertEquals(5, $pinjaman->fresh()->sisaAngsuran());
    }

    public function test_total_terbayar(): void
    {
        $pinjaman = Pinjaman::factory()->disetujui()->create();
        Angsuran::factory()->count(4)->sudahBayar()->create([
            'pinjaman_id'    => $pinjaman->id,
            'jumlah_angsuran'=> 300_000,
        ]);

        $this->assertEquals(1_200_000, $pinjaman->fresh()->totalTerbayar());
    }

    public function test_pinjaman_factory_state_disetujui(): void
    {
        $pinjaman = Pinjaman::factory()->disetujui()->create();
        $this->assertEquals('disetujui', $pinjaman->status);
        $this->assertNotNull($pinjaman->tanggal_persetujuan);
    }

    public function test_pinjaman_factory_state_lunas(): void
    {
        $pinjaman = Pinjaman::factory()->lunas()->create();
        $this->assertEquals('lunas', $pinjaman->status);
    }
}
