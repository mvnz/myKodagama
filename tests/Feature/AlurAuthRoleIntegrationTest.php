<?php
/**
 * Integration Test: Autentikasi & Role/Permission
 */

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlurAuthRoleIntegrationTest extends TestCase
{
    use RefreshDatabase;

    // ──────────────────────────────────────────────────────────
    // AUTH FLOW
    // ──────────────────────────────────────────────────────────

    public function test_user_bisa_login_dan_redirect_ke_dashboard(): void
    {
        $user = User::factory()->admin()->create(['email' => 'admin@test.id']);

        $this->post(route('masuk.proses'), [
            'email'    => 'admin@test.id',
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_bisa_logout_dan_session_dihapus(): void
    {
        $user = User::factory()->admin()->create();
        $this->actingAs($user);

        $this->post(route('keluar'))
            ->assertRedirect('/');

        $this->assertGuest();
    }

    public function test_redirect_ke_dashboard_jika_sudah_login_buka_halaman_masuk(): void
    {
        $user = User::factory()->admin()->create();

        $this->actingAs($user)
            ->get(route('masuk'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_halaman_masuk_dapat_diakses_tamu(): void
    {
        $this->get(route('masuk'))->assertStatus(200);
    }

    // ──────────────────────────────────────────────────────────
    // ROLE & PERMISSION MANAGEMENT
    // ──────────────────────────────────────────────────────────

    public function test_admin_dapat_melihat_daftar_role(): void
    {
        $roleAdmin = Role::factory()->create(['slug' => 'admin']);
        $permRole  = Permission::firstOrCreate(['slug' => 'role.kelola'], [
            'nama' => 'Kelola Role', 'grup' => 'Role',
        ]);
        $roleAdmin->permissions()->syncWithoutDetaching([$permRole->id]);
        $admin = User::factory()->admin()->create(['role_id' => $roleAdmin->id]);

        $this->actingAs($admin)
            ->get(route('role.index'))
            ->assertStatus(200);
    }

    public function test_admin_dapat_buat_role_baru(): void
    {
        $roleAdmin = Role::factory()->create(['slug' => 'admin']);
        $permRole  = Permission::firstOrCreate(['slug' => 'role.kelola'], [
            'nama' => 'Kelola Role', 'grup' => 'Role',
        ]);
        $roleAdmin->permissions()->syncWithoutDetaching([$permRole->id]);
        $admin = User::factory()->admin()->create(['role_id' => $roleAdmin->id]);

        $this->actingAs($admin)
            ->post(route('role.store'), [
                'nama'        => 'Supervisor',
                'slug'        => 'supervisor',
                'deskripsi'   => 'Role supervisor koperasi',
                'permissions' => [],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('roles', ['slug' => 'supervisor']);
    }

    public function test_admin_dapat_update_permissions_role(): void
    {
        $roleAdmin = Role::factory()->create(['slug' => 'admin']);
        $permRole  = Permission::firstOrCreate(['slug' => 'role.kelola'], [
            'nama' => 'Kelola Role', 'grup' => 'Role',
        ]);
        $roleAdmin->permissions()->syncWithoutDetaching([$permRole->id]);
        $admin = User::factory()->admin()->create(['role_id' => $roleAdmin->id]);

        $targetRole = Role::factory()->create();
        $perms      = Permission::factory()->count(3)->create();

        $this->actingAs($admin)
            ->put(route('role.update', $targetRole), [
                'nama'        => $targetRole->nama,
                'slug'        => $targetRole->slug,
                'permissions' => $perms->pluck('id')->toArray(),
            ])
            ->assertRedirect();

        $this->assertCount(3, $targetRole->fresh()->permissions);
    }

    public function test_role_dapat_dihapus(): void
    {
        $roleAdmin = Role::factory()->create(['slug' => 'admin']);
        $permRole  = Permission::firstOrCreate(['slug' => 'role.kelola'], [
            'nama' => 'Kelola Role', 'grup' => 'Role',
        ]);
        $roleAdmin->permissions()->syncWithoutDetaching([$permRole->id]);
        $admin = User::factory()->admin()->create(['role_id' => $roleAdmin->id]);

        $targetRole = Role::factory()->create();

        $this->actingAs($admin)
            ->delete(route('role.destroy', $targetRole))
            ->assertRedirect();

        $this->assertDatabaseMissing('roles', ['id' => $targetRole->id]);
    }

    // ──────────────────────────────────────────────────────────
    // GATE / PERMISSION CHECK
    // ──────────────────────────────────────────────────────────

    public function test_user_dengan_permission_dapat_akses_route_terkait(): void
    {
        $role = Role::factory()->create(['slug' => 'petugas-test']);
        $perm = Permission::factory()->create(['slug' => 'anggota.lihat']);
        $role->permissions()->attach($perm);
        $user = User::factory()->petugas()->create(['role_id' => $role->id]);

        $this->actingAs($user)
            ->get(route('anggota.index'))
            ->assertStatus(200);
    }

    public function test_user_tanpa_permission_ditolak_403(): void
    {
        $role = Role::factory()->create(['slug' => 'petugas-kosong']);
        // Tidak attach permission apapun
        $user = User::factory()->petugas()->create(['role_id' => $role->id]);

        $this->actingAs($user)
            ->get(route('anggota.index'))
            ->assertStatus(403);
    }

    public function test_admin_selalu_dapat_akses_semua_route(): void
    {
        $admin = User::factory()->admin()->create();

        // Admin tidak butuh permission spesifik
        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertStatus(200);

        $this->actingAs($admin)
            ->get(route('anggota.index'))
            ->assertStatus(200);
    }

    // ──────────────────────────────────────────────────────────
    // MULTIPLE ROLES ISOLATION
    // ──────────────────────────────────────────────────────────

    public function test_permission_satu_role_tidak_bocor_ke_role_lain(): void
    {
        $roleA = Role::factory()->create(['slug' => 'role-a']);
        $roleB = Role::factory()->create(['slug' => 'role-b']);

        $permA = Permission::factory()->create(['slug' => 'hanya.role.a']);
        $roleA->permissions()->attach($permA);

        $userB = User::factory()->petugas()->create(['role_id' => $roleB->id]);

        $this->assertFalse($userB->hasPermission('hanya.role.a'));
    }

    public function test_user_mendapat_permission_dari_role_yang_ditetapkan(): void
    {
        $role = Role::factory()->create(['slug' => 'role-c']);
        $perm = Permission::factory()->create(['slug' => 'test.permission.c']);
        $role->permissions()->attach($perm);

        $user = User::factory()->petugas()->create(['role_id' => $role->id]);
        $user->load('roleModel.permissions');

        $this->assertTrue($user->hasPermission('test.permission.c'));
        $this->assertFalse($user->hasPermission('test.permission.tidak.ada'));
    }
}
