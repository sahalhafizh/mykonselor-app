<?php

namespace Tests\Feature;

use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DevelopmentAccountsTest extends TestCase
{
    use RefreshDatabase;

    public function test_akun_mahasiswa_dan_admin_development_dapat_login(): void
    {
        $this->seed(AdminUserSeeder::class);

        $this->post(route('login'), ['nim' => '123456789012', 'password' => 'password'])
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        $this->post(route('logout'))->assertRedirect(route('welcome'));
        $this->assertGuest();

        $this->post(route('admin.login'), ['email' => 'admin@mykonselor.test', 'password' => 'AdminDemoLokal2026!'])
            ->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();
    }
}
