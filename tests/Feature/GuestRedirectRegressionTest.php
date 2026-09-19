<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestRedirectRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_open_public_pages_without_a_redirect(): void
    {
        foreach (['login', 'register', 'welcome', 'legal.privacy', 'articles.index'] as $route) {
            $this->get(route($route))->assertOk();
        }
    }

    public function test_guest_login_page_keeps_the_existing_session(): void
    {
        $this->withSession(['guest_marker' => 'keep', '_token' => str_repeat('a', 40)])
            ->get(route('login'))
            ->assertOk()
            ->assertSessionHas('guest_marker', 'keep')
            ->assertSessionHas('_token', str_repeat('a', 40));
    }

    public function test_protected_page_redirect_ends_at_the_login_form(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->get(route('login'))->assertOk();
        $this->assertGuest();
    }

    public function test_inactive_account_is_logged_out_then_can_open_login(): void
    {
        $user = User::factory()->create(['status' => 'nonaktif']);

        $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('login'));
        $this->assertGuest();
        $this->get(route('login'))->assertOk();
    }

    public function test_active_account_can_still_open_its_dashboard(): void
    {
        $user = User::factory()->create(['status' => 'aktif']);

        $this->actingAs($user)->get(route('dashboard'))->assertOk();
        $this->assertAuthenticatedAs($user);
    }
}
