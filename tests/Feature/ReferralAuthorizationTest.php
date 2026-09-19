<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\AssessmentResult;
use App\Models\User;
use Database\Seeders\DiseaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferralAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DiseaseSeeder::class);
    }

    public function test_pemilik_dapat_membuka_dan_mengirim_satu_pengajuan(): void
    {
        $owner = User::factory()->create();
        $assessment = $this->completedAssessment($owner);

        $this->actingAs($owner)->get(route('assessment.referral', $assessment))->assertOk();
        $this->actingAs($owner)->post(route('assessment.referral.store', $assessment), ['catatan' => 'Mohon informasi jadwal konseling.'])->assertRedirect();
        $this->actingAs($owner)->post(route('assessment.referral.store', $assessment), ['catatan' => 'Pengiriman ulang.'])->assertRedirect();

        $this->assertDatabaseCount('referral_requests', 1);
        $this->assertDatabaseHas('referral_requests', [
            'user_id' => $owner->id,
            'assessment_id' => $assessment->id,
            'status' => 'pending',
        ]);
    }

    public function test_mahasiswa_lain_tidak_dapat_membuka_atau_mengirim_rujukan(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $assessment = $this->completedAssessment($owner);

        $this->actingAs($stranger)->get(route('assessment.referral', $assessment))->assertForbidden();
        $this->actingAs($stranger)->post(route('assessment.referral.store', $assessment), [])->assertForbidden();
    }

    private function completedAssessment(User $user): Assessment
    {
        $assessment = Assessment::create([
            'user_id' => $user->id,
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        AssessmentResult::create([
            'assessment_id' => $assessment->id,
            'stress_cf' => 0.4,
            'anxiety_cf' => 0.2,
            'depression_cf' => 0.1,
            'stress_score' => 16,
            'anxiety_score' => 6,
            'depression_score' => 4,
            'stress_severity' => 'ringan',
            'anxiety_severity' => 'normal',
            'depression_severity' => 'normal',
            'highest_severity' => 'ringan',
            'calculation_version' => '1.0',
        ]);

        return $assessment;
    }
}
