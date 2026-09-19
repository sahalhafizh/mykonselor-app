<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InactiveUserLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_akun_nonaktif_ditolak_dengan_pesan_generik(): void
    {
        $user = User::factory()->create([
            'nim' => '123456789010',
            'password' => 'password',
            'status' => 'nonaktif',
        ]);

        $this->post(route('login'), ['nim' => $user->nim, 'password' => 'password'])
            ->assertSessionHasErrors('nim');

        $this->assertGuest();
    }

    public function test_sesi_akun_yang_dinonaktifkan_dihentikan(): void
    {
        $user = User::factory()->create(['status' => 'nonaktif']);

        $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
