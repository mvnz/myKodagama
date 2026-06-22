<?php

namespace Tests\Feature\Pinjaman;

use App\Models\Koperasi\Anggota;
use App\Models\Koperasi\Angsuran;
use App\Models\Koperasi\Pinjaman;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PinjamanControllerTest extends TestCase
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
        $this->get(route('pinjaman.index'))->assertRedirect('/masuk');
    }

    public function test_index_menampilkan_daftar_pinjaman(): void
    {
        Pinjaman::factory()->count(3)->create();

        $this->actingAs($this->admin)
            ->get(route('pinjaman.index'))
            ->assertStatus(200)
            ->assertViewHas('pinjaman');
    }

    public function test_index_filter_berdasarkan_status(): void
    {
        Pinjaman::factory()->count(2)->create(['status' => 'diajukan']);
        Pinjaman::factory()->disetujui()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('pinjaman.index', ['status' => 'diajukan']));

        $this->assertEquals(2, $response->viewData('pinjaman')->total());
    }

    // ─── Create ──────────────────────────────────────────────────

    public function test_form_tambah_pinjaman_dapat_diakses(): void
    {
        $this->actingAs($this->admin)
            ->get(route('pinjaman.create'))
            ->assertStatus(200)
            ->assertViewHas('anggota');
    }

    // ─── Store ───────────────────────────────────────────────────

    private function dataPinjamanValid(array $override = []): array
    {
        $anggota = Anggota::factory()->create();
        return array_merge([
            'anggota_id'      => $anggota->id,
            'jumlah_pinjaman' => 1000000,
            'jangka_waktu'    => 12,
            'bunga'           => 1.5,
            'tujuan'          => 'Modal usaha',
        ], $override);
    }

    public function test_store_berhasil_dengan_data_valid(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('pinjaman.store'), $this->dataPinjamanValid());

        $response->assertRedirect(route('pinjaman.index'));
        $response->assertSessionHas('sukses');
        $this->assertDatabaseHas('pinjaman', ['status' => 'diajukan', 'jumlah_pinjaman' => 1000000]);
    }

    public function test_store_otomatis_hitung_cicilan_per_bulan(): void
    {
        $this->actingAs($this->admin)->post(route('pinjaman.store'), $this->dataPinjamanValid([
            'jumlah_pinjaman' => 1200000,
            'jangka_waktu'    => 12,
            'bunga'           => 1.5,
        ]));

        // pokok = 1.200.000/12 = 100.000, bunga = 1.200.000*1.5/100 = 18.000 → cicilan = 118.000
        $this->assertDatabaseHas('pinjaman', ['cicilan_per_bulan' => 118000]);
    }

    public function test_store_gagal_tanpa_anggota(): void
    {
        $response = $this->actingAs($this->admin)->post(route('pinjaman.store'), [
            'anggota_id'      => null,
            'jumlah_pinjaman' => 1000000,
            'jangka_waktu'    => 12,
            'bunga'           => 1.5,
            'tujuan'          => 'Modal',
        ]);

        $response->assertSessionHasErrors('anggota_id');
    }

    public function test_store_gagal_jumlah_pinjaman_di_bawah_minimum(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('pinjaman.store'), $this->dataPinjamanValid(['jumlah_pinjaman' => 50000]));

        $response->assertSessionHasErrors('jumlah_pinjaman');
    }

    public function test_store_status_otomatis_diajukan(): void
    {
        $this->actingAs($this->admin)
            ->post(route('pinjaman.store'), $this->dataPinjamanValid());

        $pinjaman = Pinjaman::latest()->first();
        $this->assertEquals('diajukan', $pinjaman->status);
    }

    public function test_store_user_id_otomatis_diisi(): void
    {
        $this->actingAs($this->admin)
            ->post(route('pinjaman.store'), $this->dataPinjamanValid());

        $this->assertDatabaseHas('pinjaman', ['user_id' => $this->admin->id]);
    }

    // ─── Update: Persetujuan dan Jadwal Angsuran ─────────────────

    public function test_update_status_disetujui_membuat_jadwal_angsuran(): void
    {
        $pinjaman = Pinjaman::factory()->create([
            'status'            => 'diajukan',
            'jangka_waktu'      => 6,
            'jumlah_pinjaman'   => 600000,
            'bunga'             => 1.5,
            'cicilan_per_bulan' => 109000,
        ]);

        $this->actingAs($this->admin)->put(route('pinjaman.update', $pinjaman), [
            'status'              => 'disetujui',
            'tanggal_persetujuan' => today()->format('Y-m-d'),
        ]);

        $this->assertEquals(6, $pinjaman->fresh()->angsuran()->count());
    }

    public function test_update_tidak_membuat_jadwal_duplikat_jika_sudah_disetujui(): void
    {
        $pinjaman = Pinjaman::factory()->disetujui()->create(['jangka_waktu' => 6]);

        // Buat 6 angsuran awal
        for ($i = 1; $i <= 6; $i++) {
            Angsuran::factory()->create(['pinjaman_id' => $pinjaman->id, 'ke' => $i]);
        }

        // Update lagi dengan status disetujui (tidak boleh buat jadwal baru)
        $this->actingAs($this->admin)->put(route('pinjaman.update', $pinjaman), [
            'status' => 'disetujui',
        ]);

        $this->assertEquals(6, $pinjaman->fresh()->angsuran()->count());
    }

    public function test_update_status_ditolak(): void
    {
        $pinjaman = Pinjaman::factory()->create(['status' => 'diajukan']);

        $this->actingAs($this->admin)->put(route('pinjaman.update', $pinjaman), [
            'status' => 'ditolak',
        ]);

        $this->assertEquals('ditolak', $pinjaman->fresh()->status);
    }

    public function test_update_gagal_status_tidak_valid(): void
    {
        $pinjaman = Pinjaman::factory()->create();

        $response = $this->actingAs($this->admin)->put(route('pinjaman.update', $pinjaman), [
            'status' => 'tidak_valid',
        ]);

        $response->assertSessionHasErrors('status');
    }

    // ─── Show ────────────────────────────────────────────────────

    public function test_show_menampilkan_detail_pinjaman(): void
    {
        $pinjaman = Pinjaman::factory()->create();

        $this->actingAs($this->admin)
            ->get(route('pinjaman.show', $pinjaman))
            ->assertStatus(200)
            ->assertViewHas('pinjaman');
    }

    // ─── Destroy ─────────────────────────────────────────────────

    public function test_destroy_berhasil(): void
    {
        $pinjaman = Pinjaman::factory()->create();

        $response = $this->actingAs($this->admin)->delete(route('pinjaman.destroy', $pinjaman));

        $response->assertRedirect(route('pinjaman.index'));
        $this->assertDatabaseMissing('pinjaman', ['id' => $pinjaman->id]);
    }
}
