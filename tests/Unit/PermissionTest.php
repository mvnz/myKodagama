<?php

namespace Tests\Unit;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_permission_dapat_dibuat(): void
    {
        $perm = Permission::factory()->create([
            'nama' => 'Lihat Anggota',
            'slug' => 'anggota.lihat',
            'grup' => 'Anggota',
        ]);

        $this->assertDatabaseHas('permissions', [
            'nama' => 'Lihat Anggota',
            'slug' => 'anggota.lihat',
            'grup' => 'Anggota',
        ]);
    }

    public function test_permission_slug_harus_unik(): void
    {
        Permission::factory()->create(['slug' => 'unik.slug.1']);

        $this->expectException(\Illuminate\Database\UniqueConstraintViolationException::class);
        Permission::factory()->create(['slug' => 'unik.slug.1']);
    }

    public function test_permission_memiliki_relasi_ke_roles(): void
    {
        $perm  = Permission::factory()->create(['slug' => 'rel.test.1']);
        $role1 = Role::factory()->create();
        $role2 = Role::factory()->create();

        $perm->roles()->attach([$role1->id, $role2->id]);

        $this->assertCount(2, $perm->fresh()->roles);
    }

    public function test_permissions_dapat_dikelompokkan_per_grup(): void
    {
        Permission::factory()->create(['slug' => 'anggota.lihat.g1', 'grup' => 'Anggota']);
        Permission::factory()->create(['slug' => 'anggota.tambah.g1', 'grup' => 'Anggota']);
        Permission::factory()->create(['slug' => 'simpanan.lihat.g1', 'grup' => 'Simpanan']);

        $grouped = Permission::orderBy('grup')->get()->groupBy('grup');

        $this->assertArrayHasKey('Anggota', $grouped->toArray());
        $this->assertArrayHasKey('Simpanan', $grouped->toArray());
        $this->assertCount(2, $grouped['Anggota']);
    }
}
