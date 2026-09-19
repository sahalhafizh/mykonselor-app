<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\Symptom;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\SymptomSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssessmentStateAndTimezoneTest extends TestCase
{
    use RefreshDatabase;

    public function test_membuka_halaman_skrining_tidak_langsung_membuat_draft(): void
    {
        $user = User::factory()->create(['role' => 'mahasiswa', 'status' => 'aktif']);

        $this->actingAs($user)->get(route('assessment.create'))->assertOk();

        $this->assertDatabaseCount('assessments', 0);
    }

    public function test_dashboard_mengabaikan_draft_kosong_dan_menampilkan_mulai_skrining(): void
    {
        $user = User::factory()->create(['role' => 'mahasiswa', 'status' => 'aktif']);
        Assessment::create(['user_id' => $user->id, 'status' => 'in_progress']);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Mulai Skrining')
            ->assertDontSee('Lanjutkan Skrining');
    }

    public function test_dashboard_hanya_menampilkan_lanjutkan_untuk_draft_yang_sudah_dijawab(): void
    {
        $this->seed(SymptomSeeder::class);

        $user = User::factory()->create(['role' => 'mahasiswa', 'status' => 'aktif']);
        $assessment = Assessment::create(['user_id' => $user->id, 'status' => 'in_progress']);
        $symptom = Symptom::firstOrFail();

        $assessment->answers()->create([
            'symptom_id' => $symptom->id,
            'answer_value' => 1,
            'cf_user' => 0.4,
            'dass_score' => 1,
        ]);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Lanjutkan Skrining');
    }

    public function test_waktu_selesai_utc_ditampilkan_dalam_wib(): void
    {
        config(['app.display_timezone' => 'Asia/Jakarta']);

        $user = User::factory()->create(['role' => 'mahasiswa', 'status' => 'aktif']);
        Assessment::create([
            'user_id' => $user->id,
            'status' => 'completed',
            'completed_at' => Carbon::parse('2026-08-30 06:21:00', 'UTC'),
        ]);

        $this->actingAs($user)->get(route('assessment.history'))
            ->assertOk()
            ->assertSee('13:21')
            ->assertSee('WIB')
            ->assertDontSee('06:21');
    }
}
