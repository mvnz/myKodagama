<?php

namespace Tests\Feature\Anggota;

use App\Models\Anggota;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AnggotaControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
        Storage::fake('public');
    }

    // ─── Index ───────────────────────────────────────────────────

    public function test_index_butuh_autentikasi(): void
    {
        $this->get(route('anggota.index'))->assertRedirect('/masuk');
    }

    public function test_index_menampilkan_daftar_anggota(): void
    {
        Anggota::factory()->count(3)->create();

        $this->actingAs($this->admin)
            ->get(route('anggota.index'))
            ->assertStatus(200)
            ->assertViewHas('anggota');
    }

    public function test_index_dapat_filter_berdasarkan_status(): void
    {
        Anggota::factory()->count(2)->create(['status' => 'aktif']);
        Anggota::factory()->tidakAktif()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('anggota.index', ['status' => 'aktif']));

        $response->assertStatus(200);
        $this->assertEquals(2, $response->viewData('anggota')->total());
    }

    public function test_index_dapat_pencarian_berdasarkan_nama(): void
    {
        Anggota::factory()->create(['nama' => 'Budi Santoso', 'nik' => '1234567890123456']);
        Anggota::factory()->create(['nama' => 'Siti Rahayu', 'nik' => '1234567890123457']);

        $response = $this->actingAs($this->admin)
            ->get(route('anggota.index', ['cari' => 'Budi']));

        $this->assertEquals(1, $response->viewData('anggota')->total());
    }

    // ─── Create ──────────────────────────────────────────────────

    public function test_form_tambah_anggota_dapat_diakses(): void
    {
        $this->actingAs($this->admin)
            ->get(route('anggota.create'))
            ->assertStatus(200)
            ->assertViewHas('nomorBaru');
    }

    // ─── Store ───────────────────────────────────────────────────

    private function dataAnggotaValid(array $override = []): array
    {
        return array_merge([
            'nomor_anggota'     => 'KOP-202601-9999',
            'nama'              => 'Tes Pengguna',
            'nik'               => '1234567890000001',
            'tempat_lahir'      => 'Yogyakarta',
            'tanggal_lahir'     => '1990-01-01',
            'jenis_kelamin'     => 'Laki-laki',
            'alamat'            => 'Jl. Tes No.1',
            'telepon'           => '081234567890',
            'pekerjaan'         => 'Wiraswasta',
            'email'             => 'tes@example.com',
            'status'            => 'aktif',
            'tanggal_bergabung' => '2024-01-01',
        ], $override);
    }

    public function test_store_berhasil_dengan_data_valid(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('anggota.store'), $this->dataAnggotaValid());

        $response->assertRedirect(route('anggota.index'));
        $response->assertSessionHas('sukses');
        $this->assertDatabaseHas('anggota', ['nama' => 'Tes Pengguna']);
    }

    public function test_store_gagal_tanpa_nama(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('anggota.store'), $this->dataAnggotaValid(['nama' => '']));

        $response->assertSessionHasErrors('nama');
    }

    public function test_store_gagal_nik_tidak_16_digit(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('anggota.store'), $this->dataAnggotaValid(['nik' => '123']));

        $response->assertSessionHasErrors('nik');
    }

    public function test_store_gagal_nik_duplikat(): void
    {
        Anggota::factory()->create(['nik' => '1234567890000001']);

        $response = $this->actingAs($this->admin)
            ->post(route('anggota.store'), $this->dataAnggotaValid(['nik' => '1234567890000001']));

        $response->assertSessionHasErrors('nik');
    }

    public function test_store_gagal_jenis_kelamin_tidak_valid(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('anggota.store'), $this->dataAnggotaValid(['jenis_kelamin' => 'Lainnya']));

        $response->assertSessionHasErrors('jenis_kelamin');
    }

    public function test_store_dapat_upload_foto(): void
    {
        $foto = UploadedFile::fake()->create('foto.jpg', 10, 'image/jpeg');

        $response = $this->actingAs($this->admin)
            ->post(route('anggota.store'), array_merge(
                $this->dataAnggotaValid(),
                ['foto' => $foto]
            ));

        $response->assertRedirect(route('anggota.index'));
        $anggota = Anggota::where('nama', 'Tes Pengguna')->first();
        $this->assertNotNull($anggota->foto);
        Storage::disk('public')->assertExists($anggota->foto);
    }

    // ─── Show ────────────────────────────────────────────────────

    public function test_show_menampilkan_detail_anggota(): void
    {
        $anggota = Anggota::factory()->create();

        $this->actingAs($this->admin)
            ->get(route('anggota.show', $anggota))
            ->assertStatus(200)
            ->assertViewHas('anggota');
    }

    public function test_show_404_jika_anggota_tidak_ada(): void
    {
        $this->actingAs($this->admin)
            ->get(route('anggota.show', 9999))
            ->assertStatus(404);
    }

    // ─── Update ──────────────────────────────────────────────────

    public function test_update_berhasil(): void
    {
        $anggota = Anggota::factory()->create();

        $response = $this->actingAs($this->admin)->put(
            route('anggota.update', $anggota),
            $this->dataAnggotaValid([
                'nama'          => 'Nama Baru',
                'nomor_anggota' => $anggota->nomor_anggota,
                'nik'           => $anggota->nik,
            ])
        );

        $response->assertRedirect(route('anggota.show', $anggota));
        $this->assertDatabaseHas('anggota', ['id' => $anggota->id, 'nama' => 'Nama Baru']);
    }

    // ─── Destroy ─────────────────────────────────────────────────

    public function test_destroy_berhasil_menghapus_anggota(): void
    {
        $anggota = Anggota::factory()->create();

        $response = $this->actingAs($this->admin)->delete(route('anggota.destroy', $anggota));

        $response->assertRedirect(route('anggota.index'));
        $this->assertDatabaseMissing('anggota', ['id' => $anggota->id]);
    }

    public function test_destroy_juga_hapus_foto_dari_storage(): void
    {
        $foto    = UploadedFile::fake()->create('hapus.jpg', 10, 'image/jpeg');
        $path    = $foto->store('anggota', 'public');
        $anggota = Anggota::factory()->create(['foto' => $path]);

        $this->actingAs($this->admin)->delete(route('anggota.destroy', $anggota));

        Storage::disk('public')->assertMissing($path);
    }
}
