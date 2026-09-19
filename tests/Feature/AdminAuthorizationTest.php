<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dapat_membuka_area_admin(): void
    {
        $admin = User::factory()->admin()->create(['status' => 'aktif']);

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
        $this->actingAs($admin)->get(route('admin.users.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.referrals.index'))->assertOk();
    }

    public function test_mahasiswa_tidak_dapat_membuka_area_admin(): void
    {
        $student = User::factory()->create(['role' => 'mahasiswa']);

        $this->actingAs($student)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($student)->get(route('admin.referrals.index'))->assertForbidden();
    }

    public function test_endpoint_pengguna_admin_tidak_dapat_dipakai_untuk_memodifikasi_admin_lain(): void
    {
        $admin = User::factory()->admin()->create();
        $targetAdmin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->patch(route('admin.users.toggle-status', $targetAdmin))
            ->assertNotFound();
    }
}
