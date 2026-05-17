<?php

namespace Tests\Feature\Dashboard;

use App\Models\Anggota;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_butuh_autentikasi(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/masuk');
    }

    public function test_admin_dapat_akses_dashboard(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
    }

    public function test_petugas_dapat_akses_dashboard(): void
    {
        $user = User::factory()->petugas()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
    }

    public function test_dashboard_menampilkan_statistik_anggota(): void
    {
        $user = User::factory()->admin()->create();
        Anggota::factory()->count(3)->create(['status' => 'aktif']);
        Anggota::factory()->tidakAktif()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('totalAnggota', 3);
    }

    public function test_dashboard_view_berisi_variabel_yang_diperlukan(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertViewHasAll([
            'totalAnggota',
            'totalSimpanan',
            'totalPinjaman',
            'pengajuanBaru',
        ]);
    }
}
