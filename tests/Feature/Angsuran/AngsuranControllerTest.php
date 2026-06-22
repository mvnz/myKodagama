<?php

namespace Tests\Feature\Angsuran;

use App\Models\Koperasi\Angsuran;
use App\Models\Koperasi\Pinjaman;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AngsuranControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    // ─── Index ───────────────────────────────────────────────────

    public function test_index_butuh_autentikasi(): void
    {
        $this->get(route('angsuran.index'))->assertRedirect('/masuk');
    }

    public function test_index_menampilkan_daftar_angsuran(): void
    {
        Angsuran::factory()->count(3)->create();

        $this->actingAs($this->admin)
            ->get(route('angsuran.index'))
            ->assertStatus(200)
            ->assertViewHas('angsuran');
    }

    public function test_index_filter_status_belum_bayar(): void
    {
        Angsuran::factory()->count(2)->create(['status' => 'belum']);
        Angsuran::factory()->sudahBayar()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('angsuran.index', ['status' => 'belum']));

        $this->assertEquals(2, $response->viewData('angsuran')->total());
    }

    // ─── Show ────────────────────────────────────────────────────

    public function test_show_menampilkan_detail_angsuran(): void
    {
        $angsuran = Angsuran::factory()->create();

        $this->actingAs($this->admin)
            ->get(route('angsuran.show', $angsuran))
            ->assertStatus(200)
            ->assertViewHas('angsuran');
    }

    // ─── Bayar ───────────────────────────────────────────────────

    public function test_bayar_angsuran_berhasil(): void
    {
        $angsuran = Angsuran::factory()->create(['status' => 'belum']);

        $response = $this->actingAs($this->admin)->post(route('angsuran.bayar', $angsuran), [
            'tanggal_bayar' => today()->format('Y-m-d'),
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('sukses');
        $this->assertEquals('sudah', $angsuran->fresh()->status);
        $this->assertNotNull($angsuran->fresh()->tanggal_bayar);
    }

    public function test_bayar_tolak_jika_sudah_dibayar(): void
    {
        $angsuran = Angsuran::factory()->sudahBayar()->create();

        $response = $this->actingAs($this->admin)->post(route('angsuran.bayar', $angsuran));

        $response->assertSessionHas('galat');
    }

    public function test_bayar_angsuran_terakhir_mengubah_pinjaman_jadi_lunas(): void
    {
        $pinjaman = Pinjaman::factory()->disetujui()->create();

        // Buat 2 angsuran, 1 sudah dibayar, 1 belum
        Angsuran::factory()->sudahBayar()->create(['pinjaman_id' => $pinjaman->id, 'ke' => 1]);
        $terakhir = Angsuran::factory()->create([
            'pinjaman_id' => $pinjaman->id,
            'ke'          => 2,
            'status'      => 'belum',
        ]);

        $this->actingAs($this->admin)->post(route('angsuran.bayar', $terakhir));

        $this->assertEquals('lunas', $pinjaman->fresh()->status);
    }

    public function test_bayar_tidak_mengubah_status_pinjaman_jika_masih_ada_yang_belum_bayar(): void
    {
        $pinjaman = Pinjaman::factory()->disetujui()->create();

        $dibayar = Angsuran::factory()->create(['pinjaman_id' => $pinjaman->id, 'ke' => 1, 'status' => 'belum']);
        Angsuran::factory()->create(['pinjaman_id' => $pinjaman->id, 'ke' => 2, 'status' => 'belum']);

        $this->actingAs($this->admin)->post(route('angsuran.bayar', $dibayar));

        $this->assertNotEquals('lunas', $pinjaman->fresh()->status);
    }
}
