<?php

namespace Tests\Feature\E2E;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * E2E Workflow Test: Alur lengkap manajemen Role & Hak Akses.
 *
 * Mensimulasikan:
 * 1. Admin login
 * 2. Membuat role baru dengan beberapa permission
 * 3. Melihat daftar role
 * 4. Mengedit role dan mengubah permission
 * 5. Memastikan permission tersync dengan benar
 * 6. Menghapus role
 */
class AlurRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_alur_lengkap_manajemen_role(): void
    {
        // ── 1. Siapkan permissions & login ────────────────────────
        $permAnggotaLihat   = Permission::factory()->create(['slug' => 'anggota.lihat',   'grup' => 'Anggota']);
        $permAnggotaTambah  = Permission::factory()->create(['slug' => 'anggota.tambah',  'grup' => 'Anggota']);
        $permSimpananLihat  = Permission::factory()->create(['slug' => 'simpanan.lihat',  'grup' => 'Simpanan']);
        $permPinjamanSetujui = Permission::factory()->create(['slug' => 'pinjaman.setujui', 'grup' => 'Pinjaman']);

        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        // ── 2. Buat role baru "Kasir" dengan 2 permission ────────
        $responseStore = $this->post(route('role.store'), [
            'nama'        => 'Kasir',
            'deskripsi'   => 'Petugas kasir koperasi',
            'permissions' => [$permAnggotaLihat->id, $permSimpananLihat->id],
        ]);

        $responseStore->assertRedirect(route('role.index'));
        $responseStore->assertSessionHas('sukses');

        $role = Role::where('slug', 'kasir')->firstOrFail();
        $this->assertEquals('Kasir', $role->nama);
        $this->assertCount(2, $role->permissions);

        // ── 3. Cek di halaman daftar role ────────────────────────
        $responseIndex = $this->get(route('role.index'));
        $responseIndex->assertStatus(200);
        $this->assertCount(1, $responseIndex->viewData('roles'));

        // ── 4. Edit role: ganti nama + tambah permission ─────────
        $responseEdit = $this->get(route('role.edit', $role));
        $responseEdit->assertStatus(200);
        $this->assertContains($permAnggotaLihat->id, $responseEdit->viewData('selectedPermissions'));

        $responseUpdate = $this->put(route('role.update', $role), [
            'nama'        => 'Kasir Senior',
            'deskripsi'   => 'Kasir senior dengan hak lebih',
            'permissions' => [$permAnggotaLihat->id, $permAnggotaTambah->id, $permPinjamanSetujui->id],
        ]);

        $responseUpdate->assertRedirect(route('role.index'));
        $role->refresh();
        $this->assertEquals('Kasir Senior', $role->nama);
        $this->assertCount(3, $role->permissions);

        // ── 5. Pastikan permission lama (simpananLihat) sudah tidak ada ──
        $slugPermissions = $role->permissions->pluck('slug')->toArray();
        $this->assertNotContains('simpanan.lihat', $slugPermissions);
        $this->assertContains('anggota.lihat', $slugPermissions);
        $this->assertContains('pinjaman.setujui', $slugPermissions);

        // ── 6. Hapus role ─────────────────────────────────────────
        $roleId = $role->id;
        $responseDestroy = $this->delete(route('role.destroy', $role));

        $responseDestroy->assertRedirect(route('role.index'));
        $this->assertDatabaseMissing('roles', ['id' => $roleId]);
        $this->assertDatabaseMissing('role_permission', ['role_id' => $roleId]);

        // Permissions tidak ikut terhapus
        $this->assertDatabaseHas('permissions', ['slug' => 'anggota.lihat']);
    }

    public function test_user_dengan_role_kasir_dapat_cek_permission(): void
    {
        $permLihat  = Permission::factory()->create(['slug' => 'anggota.lihat.c1', 'grup' => 'Anggota']);
        $permHapus  = Permission::factory()->create(['slug' => 'anggota.hapus.c1', 'grup' => 'Anggota']);

        $roleKasir = Role::factory()->create(['nama' => 'Kasir Tes', 'slug' => 'kasir-tes']);
        $roleKasir->permissions()->attach($permLihat);

        $petugas = User::factory()->petugas()->create(['role_id' => $roleKasir->id]);

        $this->assertTrue($petugas->hasPermission('anggota.lihat.c1'));
        $this->assertFalse($petugas->hasPermission('anggota.hapus.c1'));
        $this->assertFalse($petugas->isAdmin());
    }

    public function test_admin_melewati_semua_cek_permission(): void
    {
        $admin = User::factory()->admin()->create();

        // Tanpa role_id, admin tetap dapat semua permission
        $this->assertTrue($admin->hasPermission('permission.apapun'));
        $this->assertTrue($admin->hasPermission('role.kelola'));
        $this->assertTrue($admin->isAdmin());
    }

    public function test_membuat_dua_role_dengan_nama_sama_menghasilkan_slug_berbeda(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $this->post(route('role.store'), ['nama' => 'Bendahara']);
        $this->post(route('role.store'), ['nama' => 'Bendahara']);
        $this->post(route('role.store'), ['nama' => 'Bendahara']);

        $this->assertDatabaseHas('roles', ['slug' => 'bendahara']);
        $this->assertDatabaseHas('roles', ['slug' => 'bendahara-1']);
        $this->assertDatabaseHas('roles', ['slug' => 'bendahara-2']);
    }
}
