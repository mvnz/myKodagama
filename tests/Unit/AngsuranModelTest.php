<?php

namespace Tests\Unit;

use App\Models\Angsuran;
use App\Models\Pinjaman;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AngsuranModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_angsuran_status_default_belum(): void
    {
        $angsuran = Angsuran::factory()->create();
        $this->assertEquals('belum', $angsuran->status);
    }

    public function test_angsuran_sudah_bayar_state(): void
    {
        $angsuran = Angsuran::factory()->sudahBayar()->create();
        $this->assertEquals('sudah', $angsuran->status);
        $this->assertNotNull($angsuran->tanggal_bayar);
    }

    public function test_angsuran_memiliki_relasi_pinjaman(): void
    {
        $pinjaman = Pinjaman::factory()->disetujui()->create();
        $angsuran = Angsuran::factory()->create(['pinjaman_id' => $pinjaman->id]);

        $this->assertEquals($pinjaman->id, $angsuran->fresh()->pinjaman->id);
    }

    public function test_angsuran_tidak_terlambat_jika_belum_jatuh_tempo(): void
    {
        $angsuran = Angsuran::factory()->create([
            'tanggal_jatuh_tempo' => now()->addDays(10),
            'status'              => 'belum',
        ]);

        $denda = $angsuran->hitungDenda(now()->format('Y-m-d'));
        $this->assertEquals(0, $denda);
    }

    public function test_angsuran_dikenai_denda_jika_terlambat(): void
    {
        $angsuran = Angsuran::factory()->create([
            'tanggal_jatuh_tempo' => now()->subDays(5),
            'jumlah_angsuran'     => 300_000,
            'status'              => 'belum',
        ]);

        $denda = $angsuran->hitungDenda(now()->format('Y-m-d'));
        $this->assertGreaterThan(0, $denda);
    }

    public function test_angsuran_terlambat_query(): void
    {
        Angsuran::factory()->create([
            'tanggal_jatuh_tempo' => now()->subDays(3),
            'status'              => 'belum',
        ]);
        Angsuran::factory()->create([
            'tanggal_jatuh_tempo' => now()->addDays(5),
            'status'              => 'belum',
        ]);

        $terlambat = Angsuran::where('status', 'belum')
            ->where('tanggal_jatuh_tempo', '<', today())
            ->count();

        $this->assertEquals(1, $terlambat);
    }
}
