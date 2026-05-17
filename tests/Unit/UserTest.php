<?php

namespace Tests\Unit;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_admin_mengembalikan_true_dari_is_admin(): void
    {
        $user = User::factory()->admin()->create();

        $this->assertTrue($user->isAdmin());
    }

    public function test_user_petugas_mengembalikan_false_dari_is_admin(): void
    {
        $user = User::factory()->petugas()->create();

        $this->assertFalse($user->isAdmin());
    }

    public function test_admin_memiliki_semua_permission_tanpa_cek_role(): void
    {
        $user = User::factory()->admin()->create();

        // Admin selalu true bahkan tanpa role model
        $this->assertTrue($user->hasPermission('permission.apapun'));
    }

    public function test_user_dengan_role_bisa_cek_permission(): void
    {
        $role = Role::factory()->create();
        $perm = Permission::factory()->create(['slug' => 'anggota.lihat.10']);
        $role->permissions()->attach($perm);

        $user = User::factory()->petugas()->create(['role_id' => $role->id]);

        $this->assertTrue($user->hasPermission('anggota.lihat.10'));
    }

    public function test_user_tanpa_role_tidak_punya_permission(): void
    {
        $user = User::factory()->petugas()->create(['role_id' => null]);

        $this->assertFalse($user->hasPermission('anggota.lihat'));
    }

    public function test_user_dengan_role_tidak_punya_permission_yang_tidak_diberikan(): void
    {
        $role = Role::factory()->create();
        $perm = Permission::factory()->create(['slug' => 'anggota.lihat.20']);
        $role->permissions()->attach($perm);

        $user = User::factory()->petugas()->create(['role_id' => $role->id]);

        $this->assertFalse($user->hasPermission('pinjaman.hapus'));
    }

    public function test_user_memiliki_relasi_ke_role_model(): void
    {
        $role = Role::factory()->create(['nama' => 'Bendahara']);
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->assertEquals('Bendahara', $user->roleModel->nama);
    }
}
