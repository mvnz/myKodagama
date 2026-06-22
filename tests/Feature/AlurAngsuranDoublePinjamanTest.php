<?php
/**
 * Integration Test: Angsuran Flow & Double-Pinjaman Check
 */

namespace Tests\Feature;

use App\Models\Koperasi\Anggota;
use App\Models\Koperasi\Angsuran;
use App\Models\Permission;
use App\Models\Koperasi\Pinjaman;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlurAngsuranDoublePinjamanTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Role $roleAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->roleAdmin = Role::factory()->create(['slug' => 'admin', 'nama' => 'Administrator']);
        $permisi = [
            'angsuran.lihat', 'angsuran.bayar',
            'pinjaman.lihat', 'pinjaman.tambah', 'pinjaman.edit',
            'pinjaman.verifikasi', 'pinjaman.setujui',
        ];
        foreach ($permisi as $slug) {
            $perm = Permission::firstOrCreate(['slug' => $slug], [
                'nama' => ucwords(str_replace('.', ' ', $slug)),
                'grup' => explode('.', $slug)[0],
            ]);
            $this->roleAdmin->permissions()->syncWithoutDetaching([$perm->id]);
        }

        $this->admin = User::factory()->admin()->create(['role_id' => $this->roleAdmin->id]);
    }

    // ──────────────────────────────────────────────────────────
    // ANGSURAN FLOW
    // ──────────────────────────────────────────────────────────

    public function test_admin_dapat_lihat_daftar_angsuran(): void
    {
        Angsuran::factory()->count(5)->create();

        $this->actingAs($this->admin)
            ->get(route('angsuran.index'))
            ->assertStatus(200)
            ->assertViewHas('angsuran');
    }

    public function test_bayar_angsuran_mengubah_status_menjadi_sudah(): void
    {
        $pinjaman = Pinjaman::factory()->disetujui()->create();
        $angsuran = Angsuran::factory()->create([
            'pinjaman_id' => $pinjaman->id,
            'status'      => 'belum',
        ]);

        $this->actingAs($this->admin)
            ->post(route('angsuran.bayar', $angsuran), [
                'tanggal_bayar' => now()->toDateString(),
            ])
            ->assertRedirect();

        $angsuranFresh = $angsuran->fresh();
        $this->assertEquals('sudah', $angsuranFresh->status);
        $this->assertEquals(now()->toDateString(), $angsuranFresh->tanggal_bayar->toDateString());
    }

    public function test_semua_angsuran_lunas_mengubah_status_pinjaman_menjadi_lunas(): void
    {
        $pinjaman = Pinjaman::factory()->disetujui()->create();

        // 3 angsuran, bayar 2 sudah, 1 belum
        $angsuranLunas = Angsuran::factory()->sudahBayar()->count(2)->create([
            'pinjaman_id' => $pinjaman->id,
        ]);
        $angsuranTerakhir = Angsuran::factory()->create([
            'pinjaman_id' => $pinjaman->id,
            'status'      => 'belum',
        ]);

        // Bayar angsuran terakhir
        $this->actingAs($this->admin)
            ->post(route('angsuran.bayar', $angsuranTerakhir), [
                'tanggal_bayar' => now()->toDateString(),
            ]);

        // Setelah semua lunas, pinjaman harus lunas
        $this->assertEquals('lunas', $pinjaman->fresh()->status);
    }

    public function test_angsuran_belum_satu_saja_pinjaman_tetap_disetujui(): void
    {
        $pinjaman = Pinjaman::factory()->disetujui()->create();

        // 2 angsuran sudah, 1 masih belum
        Angsuran::factory()->sudahBayar()->count(2)->create(['pinjaman_id' => $pinjaman->id]);
        Angsuran::factory()->create([
            'pinjaman_id' => $pinjaman->id,
            'status'      => 'belum',
        ]);

        // Pinjaman tetap disetujui
        $this->assertEquals('disetujui', $pinjaman->fresh()->status);
    }

    public function test_bayar_angsuran_tanpa_tanggal_bayar_menggunakan_tanggal_hari_ini(): void
    {
        $angsuran = Angsuran::factory()->create(['status' => 'belum']);

        $this->actingAs($this->admin)
            ->post(route('angsuran.bayar', $angsuran), [
                // tanggal_bayar tidak diisi → controller pakai today()
            ])
            ->assertRedirect();

        // Status harus tetap berubah ke sudah karena tanggal default hari ini
        $this->assertEquals('sudah', $angsuran->fresh()->status);
    }

    public function test_filter_angsuran_terlambat_berhasil(): void
    {
        // Angsuran terlambat: jatuh tempo sebelum hari ini, status belum
        Angsuran::factory()->count(3)->create([
            'tanggal_jatuh_tempo' => now()->subDays(30)->toDateString(),
            'status'              => 'belum',
        ]);
        Angsuran::factory()->count(2)->sudahBayar()->create();

        $this->actingAs($this->admin)
            ->get(route('angsuran.index', ['filter' => 'terlambat']))
            ->assertStatus(200);
    }

    // ──────────────────────────────────────────────────────────
    // DOUBLE PINJAMAN PREVENTION (anggota role)
    // ──────────────────────────────────────────────────────────

    public function test_anggota_tidak_bisa_ajukan_pinjaman_jika_ada_pinjaman_aktif(): void
    {
        $roleAnggota = Role::factory()->create(['slug' => 'anggota']);
        $permPinjaman = Permission::firstOrCreate(['slug' => 'pinjaman.tambah'], [
            'nama' => 'Tambah Pinjaman', 'grup' => 'Pinjaman',
        ]);
        $roleAnggota->permissions()->syncWithoutDetaching([$permPinjaman->id]);

        $anggota = Anggota::factory()->create(['status' => 'aktif']);
        $user    = User::factory()->create([
            'role'       => 'anggota',
            'role_id'    => $roleAnggota->id,
            'anggota_id' => $anggota->id,
        ]);

        // Pinjaman aktif yang belum lunas
        Pinjaman::factory()->disetujui()->create(['anggota_id' => $anggota->id]);

        // Coba buka form pengajuan pinjaman
        $this->actingAs($user)
            ->get(route('pinjaman.create'))
            ->assertRedirect(route('pinjaman.index'))
            ->assertSessionHas('pinjaman_aktif', true);
    }

    public function test_anggota_bisa_ajukan_pinjaman_jika_semua_pinjaman_sudah_lunas(): void
    {
        $roleAnggota  = Role::factory()->create(['slug' => 'anggota-lunas']);
        $permPinjaman = Permission::firstOrCreate(['slug' => 'pinjaman.tambah'], [
            'nama' => 'Tambah Pinjaman', 'grup' => 'Pinjaman',
        ]);
        $roleAnggota->permissions()->syncWithoutDetaching([$permPinjaman->id]);

        $anggota = Anggota::factory()->create(['status' => 'aktif']);
        $user    = User::factory()->create([
            'role'       => 'anggota',
            'role_id'    => $roleAnggota->id,
            'anggota_id' => $anggota->id,
        ]);

        // Pinjaman yang sudah lunas
        Pinjaman::factory()->lunas()->create(['anggota_id' => $anggota->id]);

        // Coba buka form — harus berhasil (tidak redirect karena tidak ada pinjaman aktif)
        $response = $this->actingAs($user)
            ->get(route('pinjaman.create'));

        $this->assertFalse($response->isRedirect(route('pinjaman.index')));
    }

    public function test_anggota_tidak_terdaftar_tidak_bisa_ajukan_pinjaman(): void
    {
        $roleAnggota  = Role::factory()->create(['slug' => 'anggota']);
        $permPinjaman = Permission::firstOrCreate(['slug' => 'pinjaman.tambah'], [
            'nama' => 'Tambah Pinjaman', 'grup' => 'Pinjaman',
        ]);
        $roleAnggota->permissions()->syncWithoutDetaching([$permPinjaman->id]);

        // User dengan role anggota tapi anggota_id null (belum punya data anggota)
        $user = User::factory()->create([
            'role'       => 'anggota',
            'role_id'    => $roleAnggota->id,
            'anggota_id' => null,
        ]);

        // Controller harus redirect karena !$dataAnggota
        $this->actingAs($user)
            ->get(route('pinjaman.create'))
            ->assertRedirect(route('pinjaman.index'));
    }

    public function test_anggota_form_pinjaman_auto_fill_nama(): void
    {
        $roleAnggota  = Role::factory()->create(['slug' => 'anggota']);
        $permPinjaman = Permission::firstOrCreate(['slug' => 'pinjaman.tambah'], [
            'nama' => 'Tambah Pinjaman', 'grup' => 'Pinjaman',
        ]);
        $roleAnggota->permissions()->syncWithoutDetaching([$permPinjaman->id]);

        $anggota = Anggota::factory()->create(['nama' => 'Siti Autofill', 'status' => 'aktif']);
        $user    = User::factory()->create([
            'role'       => 'anggota',
            'role_id'    => $roleAnggota->id,
            'anggota_id' => $anggota->id,
        ]);

        $response = $this->actingAs($user)
            ->get(route('pinjaman.create'))
            ->assertStatus(200);

        // Form harus menampilkan nama anggota (auto-fill)
        $response->assertViewHas('anggotaSaya', fn($a) => $a !== null && $a->id === $anggota->id);
    }

    // ──────────────────────────────────────────────────────────
    // STATISTIK DASHBOARD
    // ──────────────────────────────────────────────────────────

    public function test_statistik_angsuran_terlambat_akurat(): void
    {
        // 3 terlambat, 2 sudah, 2 belum (tapi belum jatuh tempo)
        Angsuran::factory()->count(3)->create([
            'tanggal_jatuh_tempo' => now()->subDays(15)->toDateString(),
            'status'              => 'belum',
        ]);
        Angsuran::factory()->sudahBayar()->count(2)->create();
        Angsuran::factory()->count(2)->create([
            'tanggal_jatuh_tempo' => now()->addDays(15)->toDateString(),
            'status'              => 'belum',
        ]);

        $terlambat = Angsuran::where('status', 'belum')
            ->where('tanggal_jatuh_tempo', '<', now()->toDateString())
            ->count();

        $this->assertEquals(3, $terlambat);
    }

    public function test_dashboard_index_dapat_diakses_user_terautentikasi(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertStatus(200);
    }
}
