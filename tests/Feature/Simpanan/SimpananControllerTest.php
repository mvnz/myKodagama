<?php

namespace Tests\Feature\Simpanan;

use App\Models\Koperasi\Anggota;
use App\Models\Koperasi\Simpanan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SimpananControllerTest extends TestCase
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
        $this->get(route('simpanan.index'))->assertRedirect('/masuk');
    }

    public function test_index_menampilkan_daftar_simpanan(): void
    {
        Simpanan::factory()->count(3)->create();

        $this->actingAs($this->admin)
            ->get(route('simpanan.index'))
            ->assertStatus(200)
            ->assertViewHas('simpanan');
    }

    public function test_index_filter_berdasarkan_jenis_simpanan(): void
    {
        Simpanan::factory()->count(2)->create(['jenis_simpanan' => 'pokok']);
        Simpanan::factory()->create(['jenis_simpanan' => 'wajib']);

        $response = $this->actingAs($this->admin)
            ->get(route('simpanan.index', ['jenis' => 'pokok']));

        $this->assertEquals(2, $response->viewData('simpanan')->total());
    }

    // ─── Create ──────────────────────────────────────────────────

    public function test_form_tambah_simpanan_dapat_diakses(): void
    {
        $this->actingAs($this->admin)
            ->get(route('simpanan.create'))
            ->assertStatus(200)
            ->assertViewHas('anggota');
    }

    // ─── Store ───────────────────────────────────────────────────

    private function dataSimpananValid(array $override = []): array
    {
        $anggota = Anggota::factory()->create();
        return array_merge([
            'anggota_id'     => $anggota->id,
            'jenis_simpanan' => 'pokok',
            'jumlah'         => 100000,
            'tanggal'        => '2024-01-01',
            'keterangan'     => 'Simpanan pokok awal',
        ], $override);
    }

    public function test_store_berhasil(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('simpanan.store'), $this->dataSimpananValid());

        $response->assertRedirect(route('simpanan.index'));
        $response->assertSessionHas('sukses');
        $this->assertDatabaseHas('simpanan', ['jenis_simpanan' => 'pokok', 'jumlah' => 100000]);
    }

    public function test_store_user_id_otomatis_diisi(): void
    {
        $this->actingAs($this->admin)
            ->post(route('simpanan.store'), $this->dataSimpananValid());

        $this->assertDatabaseHas('simpanan', ['user_id' => $this->admin->id]);
    }

    public function test_store_gagal_tanpa_anggota(): void
    {
        $anggota = Anggota::factory()->create();
        $response = $this->actingAs($this->admin)->post(route('simpanan.store'), [
            'anggota_id'     => null,
            'jenis_simpanan' => 'pokok',
            'jumlah'         => 100000,
            'tanggal'        => '2024-01-01',
        ]);

        $response->assertSessionHasErrors('anggota_id');
    }

    public function test_store_gagal_jumlah_di_bawah_minimum(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('simpanan.store'), $this->dataSimpananValid(['jumlah' => 500]));

        $response->assertSessionHasErrors('jumlah');
    }

    public function test_store_gagal_jenis_simpanan_tidak_valid(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('simpanan.store'), $this->dataSimpananValid(['jenis_simpanan' => 'investasi']));

        $response->assertSessionHasErrors('jenis_simpanan');
    }

    public function test_store_gagal_anggota_tidak_ada_di_database(): void
    {
        $response = $this->actingAs($this->admin)->post(route('simpanan.store'), [
            'anggota_id'     => 9999,
            'jenis_simpanan' => 'pokok',
            'jumlah'         => 100000,
            'tanggal'        => '2024-01-01',
        ]);

        $response->assertSessionHasErrors('anggota_id');
    }

    // ─── Update ──────────────────────────────────────────────────

    public function test_update_berhasil(): void
    {
        $simpanan = Simpanan::factory()->create(['jumlah' => 100000]);
        $anggota  = Anggota::factory()->create();

        $response = $this->actingAs($this->admin)->put(route('simpanan.update', $simpanan), [
            'anggota_id'     => $anggota->id,
            'jenis_simpanan' => 'wajib',
            'jumlah'         => 150000,
            'tanggal'        => '2024-06-01',
        ]);

        $response->assertRedirect(route('simpanan.index'));
        $this->assertDatabaseHas('simpanan', ['id' => $simpanan->id, 'jumlah' => 150000]);
    }

    // ─── Destroy ─────────────────────────────────────────────────

    public function test_destroy_berhasil(): void
    {
        $simpanan = Simpanan::factory()->create();

        $response = $this->actingAs($this->admin)->delete(route('simpanan.destroy', $simpanan));

        $response->assertRedirect(route('simpanan.index'));
        $this->assertDatabaseMissing('simpanan', ['id' => $simpanan->id]);
    }
}
