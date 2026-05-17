<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_masuk_dapat_diakses(): void
    {
        $response = $this->get('/masuk');

        $response->assertStatus(200);
    }

    public function test_redirect_ke_dashboard_jika_sudah_login(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->get('/masuk');

        $response->assertRedirect(route('dashboard'));
    }

    public function test_login_berhasil_dengan_kredensial_valid(): void
    {
        $user = User::factory()->admin()->create(['email' => 'tes@test.com']);

        $response = $this->post('/masuk', [
            'email'    => 'tes@test.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_gagal_dengan_password_salah(): void
    {
        User::factory()->create(['email' => 'tes@test.com']);

        $response = $this->post('/masuk', [
            'email'    => 'tes@test.com',
            'password' => 'salah-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_validasi_email_kosong(): void
    {
        $response = $this->post('/masuk', ['email' => '', 'password' => 'password']);

        $response->assertSessionHasErrors('email');
    }

    public function test_login_validasi_password_kosong(): void
    {
        $response = $this->post('/masuk', ['email' => 'tes@test.com', 'password' => '']);

        $response->assertSessionHasErrors('password');
    }

    public function test_login_validasi_format_email_tidak_valid(): void
    {
        $response = $this->post('/masuk', ['email' => 'bukan-email', 'password' => 'password']);

        $response->assertSessionHasErrors('email');
    }

    public function test_logout_berhasil(): void
    {
        $user = User::factory()->admin()->create();

        $this->actingAs($user)->post('/keluar');

        $this->assertGuest();
    }

    public function test_logout_redirect_ke_beranda(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->post('/keluar');

        $response->assertRedirect(route('beranda'));
    }
}
