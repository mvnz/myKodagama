<?php

namespace Tests\Unit;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_dapat_dibuat(): void
    {
        $role = Role::factory()->create(['nama' => 'Kasir', 'slug' => 'kasir']);

        $this->assertDatabaseHas('roles', ['nama' => 'Kasir', 'slug' => 'kasir']);
    }

    public function test_role_memiliki_relasi_ke_permissions(): void
    {
        $role = Role::factory()->create();
        $perm1 = Permission::factory()->create(['slug' => 'anggota.lihat.1']);
        $perm2 = Permission::factory()->create(['slug' => 'simpanan.lihat.2']);

        $role->permissions()->attach([$perm1->id, $perm2->id]);

        $this->assertCount(2, $role->fresh()->permissions);
    }

    public function test_has_permission_mengembalikan_true_jika_ada(): void
    {
        $role = Role::factory()->create();
        $perm = Permission::factory()->create(['slug' => 'anggota.lihat.3']);
        $role->permissions()->attach($perm);

        $this->assertTrue($role->hasPermission('anggota.lihat.3'));
    }

    public function test_has_permission_mengembalikan_false_jika_tidak_ada(): void
    {
        $role = Role::factory()->create();

        $this->assertFalse($role->hasPermission('pinjaman.hapus'));
    }

    public function test_role_dapat_sync_permissions(): void
    {
        $role  = Role::factory()->create();
        $perms = Permission::factory()->count(3)->create();

        $role->permissions()->sync($perms->pluck('id'));
        $this->assertCount(3, $role->fresh()->permissions);

        // Sync ulang dengan 1 permission
        $role->permissions()->sync([$perms->first()->id]);
        $this->assertCount(1, $role->fresh()->permissions);
    }

    public function test_role_dapat_dihapus_beserta_pivot(): void
    {
        $role = Role::factory()->create();
        $perm = Permission::factory()->create(['slug' => 'anggota.hapus.99']);
        $role->permissions()->attach($perm);

        $roleId = $role->id;
        $role->permissions()->detach();
        $role->delete();

        $this->assertDatabaseMissing('roles', ['id' => $roleId]);
        $this->assertDatabaseMissing('role_permission', ['role_id' => $roleId]);
    }
}
