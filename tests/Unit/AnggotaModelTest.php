<?php

namespace Tests\Unit;

use App\Models\Anggota;
use App\Models\Pinjaman;
use App\Models\Simpanan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnggotaModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_anggota_dapat_dibuat(): void
    {
        $anggota = Anggota::factory()->create(['nama' => 'Budi Santoso']);
        $this->assertDatabaseHas('anggota', ['nama' => 'Budi Santoso']);
    }

    public function test_anggota_memiliki_relasi_simpanan(): void
    {
        $anggota = Anggota::factory()->create();
        Simpanan::factory()->count(3)->create(['anggota_id' => $anggota->id]);

        $this->assertCount(3, $anggota->fresh()->simpanan);
    }

    public function test_anggota_memiliki_relasi_pinjaman(): void
    {
        $anggota = Anggota::factory()->create();
        Pinjaman::factory()->count(2)->create(['anggota_id' => $anggota->id]);

        $this->assertCount(2, $anggota->fresh()->pinjaman);
    }

    public function test_anggota_memiliki_relasi_user(): void
    {
        $anggota = Anggota::factory()->create();
        $user = User::factory()->create(['anggota_id' => $anggota->id]);

        $this->assertEquals($user->id, $anggota->fresh()->user->id);
    }

    public function test_anggota_status_default_aktif(): void
    {
        $anggota = Anggota::factory()->create();
        $this->assertEquals('aktif', $anggota->status);
    }

    public function test_anggota_factory_tidak_aktif(): void
    {
        $anggota = Anggota::factory()->tidakAktif()->create();
        $this->assertEquals('tidak_aktif', $anggota->status);
    }

    public function test_nik_harus_16_digit(): void
    {
        $anggota = Anggota::factory()->create(['nik' => '1234567890123456']);
        $this->assertEquals(16, strlen($anggota->nik));
    }

    public function test_nomor_anggota_unik(): void
    {
        Anggota::factory()->create(['nomor_anggota' => 'KOP-UNIK-001']);
        $this->expectException(\Illuminate\Database\UniqueConstraintViolationException::class);
        Anggota::factory()->create(['nomor_anggota' => 'KOP-UNIK-001']);
    }
}
