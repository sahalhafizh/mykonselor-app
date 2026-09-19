<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_hanya_dapat_membuka_halaman_publik(): void
    {
        $owner = User::factory()->create();
        $assessment = Assessment::create(['user_id' => $owner->id, 'status' => 'completed', 'completed_at' => now()]);

        $this->get(route('welcome'))->assertOk();
        $this->get(route('articles.index'))->assertOk();
        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->get(route('assessment.create'))->assertRedirect(route('login'));
        $this->get(route('assessment.history'))->assertRedirect(route('login'));
        $this->get(route('assessment.result', $assessment))->assertRedirect(route('login'));
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
    }
}
