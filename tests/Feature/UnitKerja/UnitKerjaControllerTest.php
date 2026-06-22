<?php

namespace Tests\Feature\UnitKerja;

use App\Models\Koperasi\UnitKerja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnitKerjaControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    // ─── Index ─────────────────────────────────────────────────

    public function test_admin_bisa_lihat_daftar_unit_kerja(): void
    {
        UnitKerja::factory()->count(5)->create();

        $this->actingAs($this->admin)
            ->get(route('unit-kerja.index'))
            ->assertOk();
    }

    public function test_tamu_tidak_bisa_akses_unit_kerja(): void
    {
        $this->get(route('unit-kerja.index'))
            ->assertRedirect(route('masuk'));
    }

    public function test_filter_pencarian_unit_kerja(): void
    {
        UnitKerja::factory()->create(['nama' => 'Dinas Pendidikan', 'kode' => 'UK-PDK']);
        UnitKerja::factory()->create(['nama' => 'Dinas Kesehatan',  'kode' => 'UK-KES']);

        $this->actingAs($this->admin)
            ->get(route('unit-kerja.index', ['cari' => 'Pendidikan']))
            ->assertOk()
            ->assertSeeText('Dinas Pendidikan');
    }

    // ─── Create / Store ────────────────────────────────────────

    public function test_admin_bisa_tambah_unit_kerja(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('unit-kerja.store'), [
                'kode'       => 'UK-001',
                'nama'       => 'Bagian Keuangan',
                'singkatan'  => 'BKU',
                'aktif'      => true,
            ]);

        $response->assertRedirect(route('unit-kerja.index'));
        $this->assertDatabaseHas('unit_kerja', [
            'kode' => 'UK-001',
            'nama' => 'Bagian Keuangan',
        ]);
    }

    public function test_gagal_tambah_tanpa_kode(): void
    {
        $this->actingAs($this->admin)
            ->post(route('unit-kerja.store'), [
                'kode' => '',
                'nama' => 'Bagian Test',
            ])
            ->assertSessionHasErrors('kode');
    }

    public function test_gagal_tambah_tanpa_nama(): void
    {
        $this->actingAs($this->admin)
            ->post(route('unit-kerja.store'), [
                'kode' => 'UK-999',
                'nama' => '',
            ])
            ->assertSessionHasErrors('nama');
    }

    public function test_kode_unit_kerja_harus_unik(): void
    {
        UnitKerja::factory()->create(['kode' => 'UK-DUPLIKAT']);

        $this->actingAs($this->admin)
            ->post(route('unit-kerja.store'), [
                'kode' => 'UK-DUPLIKAT',
                'nama' => 'Unit Lain',
            ])
            ->assertSessionHasErrors('kode');
    }

    public function test_unit_kerja_nonaktif_bisa_disimpan(): void
    {
        $this->actingAs($this->admin)
            ->post(route('unit-kerja.store'), [
                'kode'  => 'UK-NONAKTIF',
                'nama'  => 'Unit Tidak Aktif',
                'aktif' => false,
            ]);

        $this->assertDatabaseHas('unit_kerja', [
            'kode'  => 'UK-NONAKTIF',
            'aktif' => false,
        ]);
    }

    // ─── Update ────────────────────────────────────────────────

    public function test_admin_bisa_update_unit_kerja(): void
    {
        $unit = UnitKerja::factory()->create(['kode' => 'UK-OLD', 'nama' => 'Nama Lama']);

        $this->actingAs($this->admin)
            ->put(route('unit-kerja.update', $unit), [
                'kode' => 'UK-OLD',
                'nama' => 'Nama Baru',
            ])
            ->assertRedirect(route('unit-kerja.index'));

        $this->assertDatabaseHas('unit_kerja', [
            'id'   => $unit->id,
            'nama' => 'Nama Baru',
        ]);
    }

    public function test_update_kode_duplikat_ditolak(): void
    {
        $unit1 = UnitKerja::factory()->create(['kode' => 'UK-A']);
        $unit2 = UnitKerja::factory()->create(['kode' => 'UK-B']);

        $this->actingAs($this->admin)
            ->put(route('unit-kerja.update', $unit2), [
                'kode' => 'UK-A', // sudah dipakai unit1
                'nama' => 'Unit B Diperbarui',
            ])
            ->assertSessionHasErrors('kode');
    }

    public function test_update_kode_sendiri_tidak_ditolak(): void
    {
        $unit = UnitKerja::factory()->create(['kode' => 'UK-SAMA', 'nama' => 'Nama Lama']);

        $this->actingAs($this->admin)
            ->put(route('unit-kerja.update', $unit), [
                'kode' => 'UK-SAMA', // kode milik sendiri — boleh
                'nama' => 'Nama Diperbarui',
            ])
            ->assertRedirect(route('unit-kerja.index'));

        $this->assertDatabaseHas('unit_kerja', [
            'id'   => $unit->id,
            'nama' => 'Nama Diperbarui',
        ]);
    }

    // ─── Destroy ───────────────────────────────────────────────

    public function test_admin_bisa_hapus_unit_kerja(): void
    {
        $unit = UnitKerja::factory()->create();

        $this->actingAs($this->admin)
            ->delete(route('unit-kerja.destroy', $unit))
            ->assertRedirect(route('unit-kerja.index'));

        $this->assertDatabaseMissing('unit_kerja', ['id' => $unit->id]);
    }

    // ─── Statistik ─────────────────────────────────────────────

    public function test_statistik_unit_kerja_aktif_dan_tidak_aktif(): void
    {
        UnitKerja::factory()->count(4)->create(['aktif' => true]);
        UnitKerja::factory()->count(2)->tidakAktif()->create();

        $aktif      = UnitKerja::where('aktif', true)->count();
        $tidakAktif = UnitKerja::where('aktif', false)->count();

        $this->assertEquals(4, $aktif);
        $this->assertEquals(2, $tidakAktif);
    }
}
