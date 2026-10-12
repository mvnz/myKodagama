<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosPanduanTest extends TestCase
{
    use RefreshDatabase;

    public function test_panduan_pos_butuh_login(): void
    {
        $this->get(route('pos.panduan'))->assertRedirect('/masuk');
    }

    public function test_panduan_pos_tampil_untuk_user_login(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('pos.panduan'))
            ->assertOk()
            ->assertSee('Panduan Penggunaan POS');
    }
}