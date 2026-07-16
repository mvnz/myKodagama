<?php

namespace Tests\Unit;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_selalu_memiliki_permission(): void
    {
        $admin = User::factory()->admin()->create();

        $this->assertTrue($admin->hasPermission('pos.kasir'));
        $this->assertTrue($admin->hasPermission('role.hapus'));
    }

    public function test_user_non_admin_mengikuti_permission_role(): void
    {
        $role = Role::factory()->create();
        $permission = Permission::query()->firstOrCreate(
            ['slug' => 'pos.kasir'],
            ['nama' => 'Akses Kasir POS', 'grup' => 'pos']
        );
        $role->permissions()->attach($permission->id);

        $user = User::factory()->petugas()->create([
            'role_id' => $role->id,
        ]);

        $this->assertTrue($user->fresh()->hasPermission('pos.kasir'));
        $this->assertFalse($user->fresh()->hasPermission('pos.riwayat'));
    }

    public function test_user_non_admin_tanpa_role_tidak_memiliki_permission(): void
    {
        $user = User::factory()->petugas()->create([
            'role_id' => null,
        ]);

        $this->assertFalse($user->hasPermission('pos.kasir'));
    }
}
