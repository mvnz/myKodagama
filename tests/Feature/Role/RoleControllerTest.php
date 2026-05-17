<?php

namespace Tests\Feature\Role;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $petugas;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin   = User::factory()->admin()->create();
        $this->petugas = User::factory()->petugas()->create();
    }

    // ─── Index ───────────────────────────────────────────────────

    public function test_index_butuh_autentikasi(): void
    {
        $this->get(route('role.index'))->assertRedirect('/masuk');
    }

    public function test_index_dapat_diakses_oleh_user_login(): void
    {
        $this->actingAs($this->admin)
            ->get(route('role.index'))
            ->assertStatus(200);
    }

    public function test_index_menampilkan_daftar_role(): void
    {
        Role::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)->get(route('role.index'));

        $response->assertStatus(200);
        $response->assertViewHas('roles');
        $this->assertCount(3, $response->viewData('roles'));
    }

    // ─── Create ──────────────────────────────────────────────────

    public function test_form_tambah_role_dapat_diakses(): void
    {
        Permission::factory()->count(5)->create();

        $this->actingAs($this->admin)
            ->get(route('role.create'))
            ->assertStatus(200)
            ->assertViewHas('permissions');
    }

    // ─── Store ───────────────────────────────────────────────────

    public function test_store_berhasil_dengan_data_valid(): void
    {
        $perm = Permission::factory()->create(['slug' => 'anggota.lihat.s1']);

        $response = $this->actingAs($this->admin)->post(route('role.store'), [
            'nama'        => 'Kasir',
            'deskripsi'   => 'Petugas kasir koperasi',
            'permissions' => [$perm->id],
        ]);

        $response->assertRedirect(route('role.index'));
        $response->assertSessionHas('sukses');
        $this->assertDatabaseHas('roles', ['nama' => 'Kasir', 'slug' => 'kasir']);
        $this->assertDatabaseHas('role_permission', ['permission_id' => $perm->id]);
    }

    public function test_store_gagal_tanpa_nama(): void
    {
        $response = $this->actingAs($this->admin)->post(route('role.store'), [
            'nama' => '',
        ]);

        $response->assertSessionHasErrors('nama');
        $this->assertDatabaseCount('roles', 0);
    }

    public function test_store_nama_maksimal_100_karakter(): void
    {
        $response = $this->actingAs($this->admin)->post(route('role.store'), [
            'nama' => str_repeat('a', 101),
        ]);

        $response->assertSessionHasErrors('nama');
    }

    public function test_store_slug_otomatis_dibuat_dari_nama(): void
    {
        $this->actingAs($this->admin)->post(route('role.store'), [
            'nama' => 'Kepala Unit',
        ]);

        $this->assertDatabaseHas('roles', ['nama' => 'Kepala Unit', 'slug' => 'kepala-unit']);
    }

    public function test_store_slug_unik_jika_nama_sama(): void
    {
        Role::factory()->create(['nama' => 'Kasir', 'slug' => 'kasir']);

        $this->actingAs($this->admin)->post(route('role.store'), ['nama' => 'Kasir']);

        $this->assertDatabaseHas('roles', ['slug' => 'kasir-1']);
    }

    public function test_store_tanpa_permissions_tetap_berhasil(): void
    {
        $response = $this->actingAs($this->admin)->post(route('role.store'), [
            'nama' => 'Role Kosong',
        ]);

        $response->assertRedirect(route('role.index'));
        $this->assertDatabaseHas('roles', ['nama' => 'Role Kosong']);
        $role = Role::where('nama', 'Role Kosong')->first();
        $this->assertCount(0, $role->permissions);
    }

    public function test_store_validasi_permission_harus_ada_di_database(): void
    {
        $response = $this->actingAs($this->admin)->post(route('role.store'), [
            'nama'        => 'Role Baru',
            'permissions' => [9999],
        ]);

        $response->assertSessionHasErrors('permissions.0');
    }

    // ─── Edit ────────────────────────────────────────────────────

    public function test_form_edit_role_dapat_diakses(): void
    {
        $role = Role::factory()->create();
        Permission::factory()->count(3)->create();

        $this->actingAs($this->admin)
            ->get(route('role.edit', $role))
            ->assertStatus(200)
            ->assertViewHasAll(['role', 'permissions', 'selectedPermissions']);
    }

    // ─── Update ──────────────────────────────────────────────────

    public function test_update_berhasil_dengan_data_valid(): void
    {
        $role = Role::factory()->create(['nama' => 'Lama']);
        $perm = Permission::factory()->create(['slug' => 'simpanan.lihat.u1']);

        $response = $this->actingAs($this->admin)->put(route('role.update', $role), [
            'nama'        => 'Baru',
            'deskripsi'   => 'Deskripsi baru',
            'permissions' => [$perm->id],
        ]);

        $response->assertRedirect(route('role.index'));
        $response->assertSessionHas('sukses');
        $this->assertDatabaseHas('roles', ['id' => $role->id, 'nama' => 'Baru']);
    }

    public function test_update_sync_permissions_dengan_benar(): void
    {
        $role   = Role::factory()->create();
        $perms  = Permission::factory()->count(3)->create();
        $role->permissions()->sync($perms->pluck('id'));

        $pembaruanPerm = Permission::factory()->create(['slug' => 'pinjaman.lihat.v1']);

        $this->actingAs($this->admin)->put(route('role.update', $role), [
            'nama'        => $role->nama,
            'permissions' => [$pembaruanPerm->id],
        ]);

        $role->refresh();
        $this->assertCount(1, $role->permissions);
        $this->assertEquals($pembaruanPerm->id, $role->permissions->first()->id);
    }

    public function test_update_hapus_semua_permissions_jika_tidak_dikirim(): void
    {
        $role = Role::factory()->create();
        $perm = Permission::factory()->create(['slug' => 'pinjaman.hapus.v2']);
        $role->permissions()->attach($perm);

        $this->actingAs($this->admin)->put(route('role.update', $role), [
            'nama' => $role->nama,
        ]);

        $role->refresh();
        $this->assertCount(0, $role->permissions);
    }

    public function test_update_gagal_tanpa_nama(): void
    {
        $role = Role::factory()->create(['nama' => 'Asli']);

        $response = $this->actingAs($this->admin)->put(route('role.update', $role), [
            'nama' => '',
        ]);

        $response->assertSessionHasErrors('nama');
        $this->assertDatabaseHas('roles', ['nama' => 'Asli']);
    }

    // ─── Destroy ─────────────────────────────────────────────────

    public function test_destroy_berhasil_menghapus_role(): void
    {
        $role = Role::factory()->create();

        $response = $this->actingAs($this->admin)->delete(route('role.destroy', $role));

        $response->assertRedirect(route('role.index'));
        $response->assertSessionHas('sukses');
        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }

    public function test_destroy_juga_menghapus_pivot_permissions(): void
    {
        $role = Role::factory()->create();
        $perm = Permission::factory()->create(['slug' => 'perm.hapus.d1']);
        $role->permissions()->attach($perm);

        $this->actingAs($this->admin)->delete(route('role.destroy', $role));

        $this->assertDatabaseMissing('role_permission', ['role_id' => $role->id]);
    }

    public function test_destroy_role_tidak_ada_menghasilkan_404(): void
    {
        $this->actingAs($this->admin)
            ->delete(route('role.destroy', 9999))
            ->assertStatus(404);
    }
}
