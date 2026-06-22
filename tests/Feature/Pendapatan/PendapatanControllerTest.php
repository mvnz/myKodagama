<?php

namespace Tests\Feature\Pendapatan;

use App\Models\Koperasi\Pendapatan;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PendapatanControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $userTanpaAkses;

    protected function setUp(): void
    {
        parent::setUp();

        $roleAdmin = Role::factory()->create(['slug' => 'admin', 'nama' => 'Administrator']);
        foreach (['pendapatan.lihat', 'pendapatan.kelola'] as $slug) {
            $perm = Permission::firstOrCreate(['slug' => $slug], [
                'nama' => ucwords(str_replace('.', ' ', $slug)),
                'grup' => 'pendapatan',
            ]);
            $roleAdmin->permissions()->syncWithoutDetaching([$perm->id]);
        }
        $this->admin = User::factory()->admin()->create(['role_id' => $roleAdmin->id]);

        // User tanpa permission pendapatan
        $roleBiasa = Role::factory()->create(['slug' => 'biasa']);
        $this->userTanpaAkses = User::factory()->create([
            'role'    => 'biasa',
            'role_id' => $roleBiasa->id,
        ]);
    }

    // ─── Index ─────────────────────────────────────────────────

    public function test_admin_bisa_lihat_daftar_pendapatan(): void
    {
        Pendapatan::factory()->count(3)->create(['user_id' => $this->admin->id]);

        $this->actingAs($this->admin)
            ->get(route('pendapatan.index'))
            ->assertOk();
    }

    public function test_user_tanpa_izin_tidak_bisa_lihat_pendapatan(): void
    {
        $this->actingAs($this->userTanpaAkses)
            ->get(route('pendapatan.index'))
            ->assertStatus(403);
    }

    public function test_tamu_tidak_bisa_akses_pendapatan(): void
    {
        $this->get(route('pendapatan.index'))
            ->assertRedirect(route('masuk'));
    }

    // ─── Filter ────────────────────────────────────────────────

    public function test_filter_tahun_berjalan(): void
    {
        Pendapatan::factory()->create([
            'user_id' => $this->admin->id,
            'tanggal' => '2026-01-15',
            'jumlah'  => 500000,
        ]);
        Pendapatan::factory()->create([
            'user_id' => $this->admin->id,
            'tanggal' => '2025-06-10',
            'jumlah'  => 300000,
        ]);

        $this->actingAs($this->admin)
            ->get(route('pendapatan.index', ['tahun' => '2026']))
            ->assertOk()
            ->assertSeeText('500');
    }

    // ─── Create / Store ────────────────────────────────────────

    public function test_admin_bisa_tambah_pendapatan(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('pendapatan.store'), [
                'tanggal'  => '2026-05-01',
                'judul'    => 'Provisi Pinjaman Mei',
                'kategori' => 'provisi',
                'jumlah'   => 250000,
            ]);

        $response->assertRedirect(route('pendapatan.index'));
        $this->assertDatabaseHas('pendapatan', [
            'judul'    => 'Provisi Pinjaman Mei',
            'kategori' => 'provisi',
            'jumlah'   => 250000,
        ]);
    }

    public function test_pendapatan_gagal_jika_kategori_tidak_valid(): void
    {
        $this->actingAs($this->admin)
            ->post(route('pendapatan.store'), [
                'tanggal'  => '2026-05-01',
                'judul'    => 'Test',
                'kategori' => 'tidak_ada',
                'jumlah'   => 100000,
            ])
            ->assertSessionHasErrors('kategori');
    }

    public function test_pendapatan_gagal_jika_jumlah_nol(): void
    {
        $this->actingAs($this->admin)
            ->post(route('pendapatan.store'), [
                'tanggal'  => '2026-05-01',
                'judul'    => 'Test',
                'kategori' => 'lain_lain',
                'jumlah'   => 0,
            ])
            ->assertSessionHasErrors('jumlah');
    }

    public function test_pendapatan_gagal_tanpa_judul(): void
    {
        $this->actingAs($this->admin)
            ->post(route('pendapatan.store'), [
                'tanggal'  => '2026-05-01',
                'judul'    => '',
                'kategori' => 'lain_lain',
                'jumlah'   => 100000,
            ])
            ->assertSessionHasErrors('judul');
    }

    public function test_user_tanpa_izin_tidak_bisa_tambah_pendapatan(): void
    {
        $this->actingAs($this->userTanpaAkses)
            ->post(route('pendapatan.store'), [
                'tanggal'  => '2026-05-01',
                'judul'    => 'Test',
                'kategori' => 'lain_lain',
                'jumlah'   => 100000,
            ])
            ->assertStatus(403);
    }

    // ─── Show ──────────────────────────────────────────────────

    public function test_admin_bisa_lihat_detail_pendapatan(): void
    {
        $pendapatan = Pendapatan::factory()->create(['user_id' => $this->admin->id]);

        $this->actingAs($this->admin)
            ->get(route('pendapatan.show', $pendapatan))
            ->assertOk();
    }

    // ─── Update ────────────────────────────────────────────────

    public function test_admin_bisa_update_pendapatan(): void
    {
        $pendapatan = Pendapatan::factory()->create([
            'user_id' => $this->admin->id,
            'jumlah'  => 100000,
        ]);

        $this->actingAs($this->admin)
            ->put(route('pendapatan.update', $pendapatan), [
                'tanggal'  => $pendapatan->tanggal->format('Y-m-d'),
                'judul'    => 'Diperbarui',
                'kategori' => $pendapatan->kategori,
                'jumlah'   => 200000,
            ])
            ->assertRedirect(route('pendapatan.index'));

        $this->assertDatabaseHas('pendapatan', [
            'id'     => $pendapatan->id,
            'judul'  => 'Diperbarui',
            'jumlah' => 200000,
        ]);
    }

    // ─── Destroy ───────────────────────────────────────────────

    public function test_admin_bisa_hapus_pendapatan(): void
    {
        $pendapatan = Pendapatan::factory()->create(['user_id' => $this->admin->id]);

        $this->actingAs($this->admin)
            ->delete(route('pendapatan.destroy', $pendapatan))
            ->assertRedirect(route('pendapatan.index'));

        $this->assertDatabaseMissing('pendapatan', ['id' => $pendapatan->id]);
    }

    // ─── Total & Rekap ─────────────────────────────────────────

    public function test_total_pendapatan_per_tahun_terhitung_benar(): void
    {
        Pendapatan::factory()->create([
            'user_id' => $this->admin->id,
            'tanggal' => '2026-01-01',
            'jumlah'  => 1_000_000,
            'kategori'=> 'provisi',
        ]);
        Pendapatan::factory()->create([
            'user_id' => $this->admin->id,
            'tanggal' => '2026-03-15',
            'jumlah'  => 500_000,
            'kategori'=> 'denda',
        ]);

        $total = Pendapatan::whereYear('tanggal', 2026)->sum('jumlah');
        $this->assertEquals(1_500_000, $total);
    }

    public function test_rekap_per_kategori(): void
    {
        Pendapatan::factory()->count(2)->create([
            'user_id' => $this->admin->id,
            'tanggal' => '2026-01-01',
            'jumlah'  => 200_000,
            'kategori'=> 'provisi',
        ]);
        Pendapatan::factory()->create([
            'user_id' => $this->admin->id,
            'tanggal' => '2026-01-01',
            'jumlah'  => 300_000,
            'kategori'=> 'denda',
        ]);

        $rekap = Pendapatan::whereYear('tanggal', 2026)
            ->selectRaw('kategori, SUM(jumlah) as total')
            ->groupBy('kategori')
            ->pluck('total', 'kategori');

        $this->assertEquals(400_000, $rekap['provisi']);
        $this->assertEquals(300_000, $rekap['denda']);
    }
}
