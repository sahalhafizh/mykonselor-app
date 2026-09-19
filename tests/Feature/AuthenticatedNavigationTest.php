<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticatedNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_mahasiswa_dapat_berpindah_di_area_yang_diizinkan(): void
    {
        $user = User::factory()->create(['role' => 'mahasiswa', 'status' => 'aktif']);

        $this->actingAs($user)->get(route('welcome'))->assertOk();
        $this->actingAs($user)->get(route('dashboard'))->assertOk();
        $this->actingAs($user)->get(route('assessment.create'))->assertOk();
        $this->actingAs($user)->get(route('assessment.history'))->assertOk();
        $this->actingAs($user)->get(route('articles.index'))->assertOk();
        $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_login_mahasiswa_aktif_berhasil(): void
    {
        $user = User::factory()->create([
            'nim' => '123456789011',
            'password' => 'password',
            'role' => 'mahasiswa',
            'status' => 'aktif',
        ]);

        $this->post(route('login'), ['nim' => $user->nim, 'password' => 'password'])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }
}
