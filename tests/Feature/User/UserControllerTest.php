<?php

namespace Tests\Feature\User;

use App\Models\Koperasi\Anggota;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Role $roleAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->roleAdmin = Role::factory()->create(['slug' => 'admin', 'nama' => 'Administrator']);
        $this->admin     = User::factory()->admin()->create(['role_id' => $this->roleAdmin->id]);
    }

    // ─── Index ─────────────────────────────────────────────────

    public function test_admin_bisa_lihat_daftar_user(): void
    {
        User::factory()->count(3)->create();

        $this->actingAs($this->admin)
            ->get(route('user.index'))
            ->assertOk();
    }

    public function test_tamu_tidak_bisa_akses_manajemen_user(): void
    {
        $this->get(route('user.index'))
            ->assertRedirect(route('masuk'));
    }

    public function test_pencarian_user_berdasarkan_nama(): void
    {
        User::factory()->create(['name' => 'Budi Santoso']);
        User::factory()->create(['name' => 'Siti Rahayu']);

        $this->actingAs($this->admin)
            ->get(route('user.index', ['q' => 'Budi']))
            ->assertOk()
            ->assertSeeText('Budi Santoso');
    }

    // ─── Store ─────────────────────────────────────────────────

    public function test_admin_bisa_tambah_user_baru(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('user.store'), [
                'name'                  => 'Pegawai Baru',
                'username'              => 'pegawai_baru',
                'email'                 => 'pegawai@koperasi.id',
                'role_id'               => $this->roleAdmin->id,
                'password'              => 'Password123!',
                'password_confirmation' => 'Password123!',
            ]);

        $response->assertRedirect(route('user.index'));
        $this->assertDatabaseHas('users', [
            'username' => 'pegawai_baru',
            'email'    => 'pegawai@koperasi.id',
        ]);
    }

    public function test_password_user_baru_tersimpan_sebagai_hash(): void
    {
        $this->actingAs($this->admin)
            ->post(route('user.store'), [
                'name'                  => 'Test User',
                'username'              => 'test_hash',
                'email'                 => 'testhash@koperasi.id',
                'role_id'               => $this->roleAdmin->id,
                'password'              => 'RahasiaKuat99!',
                'password_confirmation' => 'RahasiaKuat99!',
            ]);

        $user = User::where('username', 'test_hash')->first();
        $this->assertNotNull($user);
        $this->assertNotEquals('RahasiaKuat99!', $user->password);
        $this->assertTrue(Hash::check('RahasiaKuat99!', $user->password));
    }

    public function test_username_harus_unik(): void
    {
        User::factory()->create(['username' => 'existing_user']);

        $this->actingAs($this->admin)
            ->post(route('user.store'), [
                'name'                  => 'User Lain',
                'username'              => 'existing_user',
                'email'                 => 'unique@koperasi.id',
                'role_id'               => $this->roleAdmin->id,
                'password'              => 'Password123!',
                'password_confirmation' => 'Password123!',
            ])
            ->assertSessionHasErrors('username');
    }

    public function test_email_harus_unik(): void
    {
        User::factory()->create(['email' => 'existing@koperasi.id']);

        $this->actingAs($this->admin)
            ->post(route('user.store'), [
                'name'                  => 'User Baru',
                'username'              => 'user_baru99',
                'email'                 => 'existing@koperasi.id',
                'role_id'               => $this->roleAdmin->id,
                'password'              => 'Password123!',
                'password_confirmation' => 'Password123!',
            ])
            ->assertSessionHasErrors('email');
    }

    public function test_konfirmasi_password_tidak_cocok_ditolak(): void
    {
        $this->actingAs($this->admin)
            ->post(route('user.store'), [
                'name'                  => 'User Test',
                'username'              => 'user_test99',
                'email'                 => 'test99@koperasi.id',
                'role_id'               => $this->roleAdmin->id,
                'password'              => 'Password123!',
                'password_confirmation' => 'BedaSekali999',
            ])
            ->assertSessionHasErrors('password');
    }

    public function test_role_id_tidak_valid_ditolak(): void
    {
        $this->actingAs($this->admin)
            ->post(route('user.store'), [
                'name'                  => 'User Test',
                'username'              => 'user_test88',
                'email'                 => 'test88@koperasi.id',
                'role_id'               => 9999,
                'password'              => 'Password123!',
                'password_confirmation' => 'Password123!',
            ])
            ->assertSessionHasErrors('role_id');
    }

    public function test_user_bisa_dihubungkan_ke_anggota(): void
    {
        $anggota = Anggota::factory()->create();

        $this->actingAs($this->admin)
            ->post(route('user.store'), [
                'name'                  => 'Anggota User',
                'username'              => 'anggota_user1',
                'email'                 => 'anggota_user1@koperasi.id',
                'role_id'               => $this->roleAdmin->id,
                'anggota_id'            => $anggota->id,
                'password'              => 'Password123!',
                'password_confirmation' => 'Password123!',
            ]);

        $this->assertDatabaseHas('users', [
            'username'   => 'anggota_user1',
            'anggota_id' => $anggota->id,
        ]);
    }

    // ─── Update ────────────────────────────────────────────────

    public function test_admin_bisa_update_data_user(): void
    {
        $user = User::factory()->create(['username' => 'user_update', 'role_id' => $this->roleAdmin->id]);

        $this->actingAs($this->admin)
            ->put(route('user.update', $user), [
                'name'     => 'Nama Diperbarui',
                'username' => $user->username,
                'email'    => $user->email,
                'role_id'  => $this->roleAdmin->id,
            ])
            ->assertRedirect(route('user.index'));

        $this->assertDatabaseHas('users', [
            'id'   => $user->id,
            'name' => 'Nama Diperbarui',
        ]);
    }

    public function test_update_password_opsional(): void
    {
        $user = User::factory()->create([
            'username' => 'user_pswd',
            'password' => Hash::make('PasswordLama!'),
            'role_id'  => $this->roleAdmin->id,
        ]);

        // Update tanpa isi password — password tidak berubah
        $this->actingAs($this->admin)
            ->put(route('user.update', $user), [
                'name'     => 'Nama Baru',
                'username' => $user->username,
                'email'    => $user->email,
                'role_id'  => $this->roleAdmin->id,
                'password' => '',
            ]);

        $this->assertTrue(Hash::check('PasswordLama!', $user->fresh()->password));
    }

    public function test_update_username_sendiri_tidak_ditolak(): void
    {
        $user = User::factory()->create([
            'username' => 'my_username',
            'role_id'  => $this->roleAdmin->id,
        ]);

        $this->actingAs($this->admin)
            ->put(route('user.update', $user), [
                'name'     => 'Nama Lain',
                'username' => 'my_username', // sendiri — boleh
                'email'    => $user->email,
                'role_id'  => $this->roleAdmin->id,
            ])
            ->assertRedirect(route('user.index'));
    }

    // ─── Destroy ───────────────────────────────────────────────

    public function test_admin_bisa_hapus_user(): void
    {
        $user = User::factory()->create(['role_id' => $this->roleAdmin->id]);

        $this->actingAs($this->admin)
            ->delete(route('user.destroy', $user))
            ->assertRedirect(route('user.index'));

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
