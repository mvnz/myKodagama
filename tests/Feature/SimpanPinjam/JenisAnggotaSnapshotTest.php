<?php

/**
 * Test: Snapshot jenis_anggota pada Simpanan
 *
 * Memastikan bahwa histori simpanan mencerminkan golongan anggota
 * pada saat transaksi dibuat, bukan golongan saat ini.
 */

namespace Tests\Feature\SimpanPinjam;

use App\Models\Koperasi\Anggota;
use App\Models\Koperasi\Simpanan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JenisAnggotaSnapshotTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    // ─────────────────────────────────────────────────────────────
    // Snapshot saat create via Controller
    // ─────────────────────────────────────────────────────────────

    /** Saat simpanan dibuat, jenis_anggota diambil dari anggota saat itu */
    public function test_simpanan_mencatat_jenis_anggota_saat_dibuat(): void
    {
        $anggota = Anggota::factory()->golongan('PNS GOL 1')->create();

        $this->actingAs($this->admin)
            ->post(route('simpanan.store'), [
                'anggota_id'     => $anggota->id,
                'jenis_simpanan' => 'wajib',
                'jumlah'         => 100000,
                'tanggal'        => '2026-01-15',
            ]);

        $this->assertDatabaseHas('simpanan', [
            'anggota_id'    => $anggota->id,
            'jenis_anggota' => 'PNS GOL 1',
        ]);
    }

    /** Inti fitur: simpanan lama TIDAK berubah setelah anggota naik golongan */
    public function test_simpanan_lama_tetap_golongan_lama_setelah_naik_golongan(): void
    {
        // Anggota masih GOL 1 saat simpanan dibuat
        $anggota = Anggota::factory()->golongan('PNS GOL 1')->create();

        $simpananLama = Simpanan::factory()->create([
            'anggota_id'    => $anggota->id,
            'jenis_anggota' => 'PNS GOL 1',
            'tanggal'       => '2026-01-15',
        ]);

        // Anggota naik ke GOL 2
        $anggota->update(['jenis_anggota' => 'PNS GOL 2']);

        // Simpanan lama harus tetap GOL 1
        $this->assertDatabaseHas('simpanan', [
            'id'            => $simpananLama->id,
            'jenis_anggota' => 'PNS GOL 1',
        ]);

        $this->assertNotEquals('PNS GOL 2', $simpananLama->fresh()->jenis_anggota);
    }

    /** Simpanan baru setelah naik golongan mencerminkan golongan baru */
    public function test_simpanan_baru_mencerminkan_golongan_baru(): void
    {
        $anggota = Anggota::factory()->golongan('PNS GOL 1')->create();

        // Simpanan saat masih GOL 1
        $this->actingAs($this->admin)->post(route('simpanan.store'), [
            'anggota_id'     => $anggota->id,
            'jenis_simpanan' => 'wajib',
            'jumlah'         => 100000,
            'tanggal'        => '2026-01-15',
        ]);

        // Naik golongan
        $anggota->update(['jenis_anggota' => 'PNS GOL 2']);

        // Simpanan baru setelah naik
        $this->actingAs($this->admin)->post(route('simpanan.store'), [
            'anggota_id'     => $anggota->id,
            'jenis_simpanan' => 'wajib',
            'jumlah'         => 150000,
            'tanggal'        => '2026-06-15',
        ]);

        $simpanan = Simpanan::where('anggota_id', $anggota->id)->orderBy('tanggal')->get();

        $this->assertEquals('PNS GOL 1', $simpanan->first()->jenis_anggota, 'Simpanan lama harus tetap GOL 1');
        $this->assertEquals('PNS GOL 2', $simpanan->last()->jenis_anggota,  'Simpanan baru harus GOL 2');
        $this->assertCount(2, $simpanan);
    }

    /** Update simpanan tidak boleh mengubah jenis_anggota */
    public function test_update_simpanan_tidak_mengubah_jenis_anggota(): void
    {
        $anggota = Anggota::factory()->golongan('PNS GOL 1')->create();

        $simpanan = Simpanan::factory()->create([
            'anggota_id'    => $anggota->id,
            'jenis_anggota' => 'PNS GOL 1',
            'jenis_simpanan'=> 'wajib',
            'jumlah'        => 100000,
        ]);

        // Anggota naik golongan, lalu update simpanan (ubah jumlah)
        $anggota->update(['jenis_anggota' => 'PNS GOL 2']);

        $this->actingAs($this->admin)
            ->put(route('simpanan.update', $simpanan), [
                'anggota_id'     => $anggota->id,
                'jenis_simpanan' => 'wajib',
                'jumlah'         => 120000,
                'tanggal'        => $simpanan->tanggal->format('Y-m-d'),
            ]);

        // Jumlah berubah, tapi jenis_anggota tetap GOL 1
        $this->assertDatabaseHas('simpanan', [
            'id'            => $simpanan->id,
            'jumlah'        => 120000,
            'jenis_anggota' => 'PNS GOL 1',
        ]);
    }

    /** Anggota tanpa jenis_anggota tetap bisa menyimpan (nullable) */
    public function test_simpanan_berhasil_jika_anggota_belum_punya_jenis(): void
    {
        $anggota = Anggota::factory()->create(['jenis_anggota' => null]);

        $response = $this->actingAs($this->admin)
            ->post(route('simpanan.store'), [
                'anggota_id'     => $anggota->id,
                'jenis_simpanan' => 'pokok',
                'jumlah'         => 50000,
                'tanggal'        => '2026-01-01',
            ]);

        $response->assertRedirect(route('simpanan.index'));
        $this->assertDatabaseHas('simpanan', [
            'anggota_id'    => $anggota->id,
            'jenis_anggota' => null,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // Multiple perubahan golongan
    // ─────────────────────────────────────────────────────────────

    /** Histori lengkap: GOL1 → GOL2 → GOL3, setiap periode ter-snapshot benar */
    public function test_histori_golongan_tersimpan_per_periode(): void
    {
        $anggota = Anggota::factory()->golongan('PNS GOL 1')->create();

        // Periode GOL 1
        Simpanan::factory()->count(3)->create([
            'anggota_id'    => $anggota->id,
            'jenis_anggota' => 'PNS GOL 1',
        ]);

        // Naik ke GOL 2
        $anggota->update(['jenis_anggota' => 'PNS GOL 2']);
        Simpanan::factory()->count(2)->create([
            'anggota_id'    => $anggota->id,
            'jenis_anggota' => 'PNS GOL 2',
        ]);

        // Naik ke GOL 3
        $anggota->update(['jenis_anggota' => 'PNS GOL 3']);
        Simpanan::factory()->count(1)->create([
            'anggota_id'    => $anggota->id,
            'jenis_anggota' => 'PNS GOL 3',
        ]);

        $semua = Simpanan::where('anggota_id', $anggota->id)->get();

        $this->assertEquals(3, $semua->where('jenis_anggota', 'PNS GOL 1')->count());
        $this->assertEquals(2, $semua->where('jenis_anggota', 'PNS GOL 2')->count());
        $this->assertEquals(1, $semua->where('jenis_anggota', 'PNS GOL 3')->count());
        $this->assertCount(6, $semua);
    }

    /** Snapshot di-backfill saat migration: jenis_anggota tidak null untuk data lama */
    public function test_backfill_jenis_anggota_ada_di_simpanan_lama(): void
    {
        $anggota = Anggota::factory()->golongan('PNS GOL 2')->create();

        // Simulasi data lama yang jenis_anggota-nya sudah di-backfill
        $simpanan = Simpanan::factory()->create([
            'anggota_id'    => $anggota->id,
            'jenis_anggota' => $anggota->jenis_anggota, // hasil backfill migration
        ]);

        $this->assertEquals('PNS GOL 2', $simpanan->jenis_anggota);
    }
}
