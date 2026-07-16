<?php

namespace Tests\Feature\Security;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_diarahkan_ke_login_saat_akses_kasir(): void
    {
        $this->get(route('pos.kasir'))->assertRedirect(route('masuk'));
    }

    public function test_user_tanpa_permission_ditolak_saat_akses_kasir(): void
    {
        $role = Role::factory()->create();
        $user = User::factory()->petugas()->create([
            'role_id' => $role->id,
        ]);

        $this->actingAs($user)
            ->get(route('pos.kasir'))
            ->assertForbidden();
    }

    public function test_user_tanpa_permission_tidak_bisa_checkout_pos(): void
    {
        $role = Role::factory()->create();
        $user = User::factory()->petugas()->create([
            'role_id' => $role->id,
        ]);

        $this->actingAs($user)
            ->post(route('pos.checkout'), [
                'items' => json_encode([]),
                'bayar' => 0,
                'metode_bayar' => 'tunai',
            ])
            ->assertForbidden();
    }
}
