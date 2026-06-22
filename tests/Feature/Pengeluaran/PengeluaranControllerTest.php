<?php

namespace Tests\Feature\Pengeluaran;

use App\Models\Koperasi\Pengeluaran;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PengeluaranControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $userTanpaAkses;

    protected function setUp(): void
    {
        parent::setUp();

        $roleAdmin = Role::factory()->create(['slug' => 'admin', 'nama' => 'Administrator']);
        foreach (['pengeluaran.lihat', 'pengeluaran.kelola'] as $slug) {
            $perm = Permission::firstOrCreate(['slug' => $slug], [
                'nama' => ucwords(str_replace('.', ' ', $slug)),
                'grup' => 'pengeluaran',
            ]);
            $roleAdmin->permissions()->syncWithoutDetaching([$perm->id]);
        }
        $this->admin = User::factory()->admin()->create(['role_id' => $roleAdmin->id]);

        $roleBiasa = Role::factory()->create(['slug' => 'biasa']);
        $this->userTanpaAkses = User::factory()->create([
            'role'    => 'biasa',
            'role_id' => $roleBiasa->id,
        ]);
    }

    // ─── Index ─────────────────────────────────────────────────

    public function test_admin_bisa_lihat_daftar_pengeluaran(): void
    {
        Pengeluaran::factory()->count(3)->create(['user_id' => $this->admin->id]);

        $this->actingAs($this->admin)
            ->get(route('pengeluaran.index'))
            ->assertOk();
    }

    public function test_user_tanpa_izin_tidak_bisa_lihat_pengeluaran(): void
    {
        $this->actingAs($this->userTanpaAkses)
            ->get(route('pengeluaran.index'))
            ->assertStatus(403);
    }

    public function test_tamu_tidak_bisa_akses_pengeluaran(): void
    {
        $this->get(route('pengeluaran.index'))
            ->assertRedirect(route('masuk'));
    }

    // ─── Create / Store ────────────────────────────────────────

    public function test_admin_bisa_tambah_pengeluaran(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('pengeluaran.store'), [
                'tanggal'  => '2026-05-01',
                'judul'    => 'Honor Pengurus Mei',
                'kategori' => 'gaji_honor',
                'jumlah'   => 500000,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('pengeluaran', [
            'judul'    => 'Honor Pengurus Mei',
            'kategori' => 'gaji_honor',
            'jumlah'   => 500000,
        ]);
    }

    public function test_pengeluaran_gagal_jika_kategori_tidak_valid(): void
    {
        $this->actingAs($this->admin)
            ->post(route('pengeluaran.store'), [
                'tanggal'  => '2026-05-01',
                'judul'    => 'Test',
                'kategori' => 'korupsi',
                'jumlah'   => 100000,
            ])
            ->assertSessionHasErrors('kategori');
    }

    public function test_pengeluaran_gagal_jika_jumlah_nol(): void
    {
        $this->actingAs($this->admin)
            ->post(route('pengeluaran.store'), [
                'tanggal'  => '2026-05-01',
                'judul'    => 'Test',
                'kategori' => 'lain_lain',
                'jumlah'   => 0,
            ])
            ->assertSessionHasErrors('jumlah');
    }

    public function test_pengeluaran_gagal_tanpa_judul(): void
    {
        $this->actingAs($this->admin)
            ->post(route('pengeluaran.store'), [
                'tanggal'  => '2026-05-01',
                'judul'    => '',
                'kategori' => 'lain_lain',
                'jumlah'   => 100000,
            ])
            ->assertSessionHasErrors('judul');
    }

    public function test_user_tanpa_izin_tidak_bisa_tambah_pengeluaran(): void
    {
        $this->actingAs($this->userTanpaAkses)
            ->post(route('pengeluaran.store'), [
                'tanggal'  => '2026-05-01',
                'judul'    => 'Test',
                'kategori' => 'lain_lain',
                'jumlah'   => 100000,
            ])
            ->assertStatus(403);
    }

    // ─── Show ──────────────────────────────────────────────────

    public function test_admin_bisa_lihat_detail_pengeluaran(): void
    {
        $pengeluaran = Pengeluaran::factory()->create(['user_id' => $this->admin->id]);

        $this->actingAs($this->admin)
            ->get(route('pengeluaran.show', $pengeluaran))
            ->assertOk();
    }

    // ─── Update ────────────────────────────────────────────────

    public function test_admin_bisa_update_pengeluaran(): void
    {
        $pengeluaran = Pengeluaran::factory()->create([
            'user_id' => $this->admin->id,
            'jumlah'  => 200000,
        ]);

        $this->actingAs($this->admin)
            ->put(route('pengeluaran.update', $pengeluaran), [
                'tanggal'  => $pengeluaran->tanggal->format('Y-m-d'),
                'judul'    => 'Honor Diperbarui',
                'kategori' => $pengeluaran->kategori,
                'jumlah'   => 300000,
            ])
            ->assertRedirect(route('pengeluaran.show', $pengeluaran));

        $this->assertDatabaseHas('pengeluaran', [
            'id'     => $pengeluaran->id,
            'judul'  => 'Honor Diperbarui',
            'jumlah' => 300000,
        ]);
    }

    // ─── Destroy ───────────────────────────────────────────────

    public function test_admin_bisa_hapus_pengeluaran(): void
    {
        $pengeluaran = Pengeluaran::factory()->create(['user_id' => $this->admin->id]);

        $this->actingAs($this->admin)
            ->delete(route('pengeluaran.destroy', $pengeluaran))
            ->assertRedirect(route('pengeluaran.index'));

        $this->assertDatabaseMissing('pengeluaran', ['id' => $pengeluaran->id]);
    }

    // ─── Kalkulasi ─────────────────────────────────────────────

    public function test_total_pengeluaran_per_tahun_terhitung_benar(): void
    {
        Pengeluaran::factory()->create([
            'user_id' => $this->admin->id,
            'tanggal' => '2026-02-01',
            'jumlah'  => 2_000_000,
        ]);
        Pengeluaran::factory()->create([
            'user_id' => $this->admin->id,
            'tanggal' => '2026-04-01',
            'jumlah'  => 750_000,
        ]);
        // tahun lain — tidak masuk filter
        Pengeluaran::factory()->create([
            'user_id' => $this->admin->id,
            'tanggal' => '2025-12-01',
            'jumlah'  => 999_999,
        ]);

        $total = Pengeluaran::whereYear('tanggal', 2026)->sum('jumlah');
        $this->assertEquals(2_750_000, $total);
    }

    public function test_rekap_per_kategori(): void
    {
        Pengeluaran::factory()->count(3)->create([
            'user_id' => $this->admin->id,
            'tanggal' => '2026-01-01',
            'jumlah'  => 100_000,
            'kategori'=> 'atk',
        ]);
        Pengeluaran::factory()->create([
            'user_id' => $this->admin->id,
            'tanggal' => '2026-01-01',
            'jumlah'  => 500_000,
            'kategori'=> 'gaji_honor',
        ]);

        $rekap = Pengeluaran::whereYear('tanggal', 2026)
            ->selectRaw('kategori, SUM(jumlah) as total')
            ->groupBy('kategori')
            ->pluck('total', 'kategori');

        $this->assertEquals(300_000, $rekap['atk']);
        $this->assertEquals(500_000, $rekap['gaji_honor']);
    }
}
