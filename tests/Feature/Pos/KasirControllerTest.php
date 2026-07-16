<?php

namespace Tests\Feature\Pos;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KasirControllerTest extends TestCase
{
    use RefreshDatabase;

    private function createPetugasWithPermission(string $slug): User
    {
        $role = Role::factory()->create();
        $permission = Permission::query()->firstOrCreate(
            ['slug' => $slug],
            ['nama' => strtoupper($slug), 'grup' => 'pos']
        );
        $role->permissions()->attach($permission->id);

        return User::factory()->petugas()->create([
            'role_id' => $role->id,
        ]);
    }

    public function test_halaman_kasir_dapat_diakses_petugas_yang_punya_permission(): void
    {
        $user = $this->createPetugasWithPermission('pos.kasir');

        $this->actingAs($user)
            ->get(route('pos.kasir'))
            ->assertOk()
            ->assertViewIs('pos.kasir');
    }

    public function test_checkout_melakukan_validasi_input_untuk_user_berizin(): void
    {
        $user = $this->createPetugasWithPermission('pos.kasir');

        $this->actingAs($user)
            ->from(route('pos.kasir'))
            ->post(route('pos.checkout'), [])
            ->assertRedirect(route('pos.kasir'))
            ->assertSessionHasErrors(['items', 'bayar', 'metode_bayar']);
    }
}
