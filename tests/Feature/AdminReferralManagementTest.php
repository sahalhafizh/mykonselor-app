<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\AssessmentResult;
use App\Models\ReferralRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReferralManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dapat_melihat_detail_dan_memperbarui_status_pengajuan(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->create();
        $assessment = Assessment::create(['user_id' => $student->id, 'status' => 'completed', 'completed_at' => now()]);

        AssessmentResult::create([
            'assessment_id' => $assessment->id,
            'stress_cf' => 0.8,
            'anxiety_cf' => 0.2,
            'depression_cf' => 0.1,
            'stress_score' => 26,
            'anxiety_score' => 8,
            'depression_score' => 6,
            'stress_severity' => 'berat',
            'anxiety_severity' => 'ringan',
            'depression_severity' => 'normal',
            'highest_severity' => 'berat',
            'calculation_version' => '1.0',
        ]);

        $referral = ReferralRequest::create([
            'user_id' => $student->id,
            'assessment_id' => $assessment->id,
            'status' => 'pending',
            'catatan' => 'Membutuhkan arahan jadwal layanan.',
        ]);

        $this->actingAs($admin)->get(route('admin.referrals.index'))->assertOk()->assertSee($student->name);
        $this->actingAs($admin)->get(route('admin.referrals.show', $referral))->assertOk()->assertSee('Membutuhkan arahan');
        $this->actingAs($admin)->patch(route('admin.referrals.update', $referral), ['status' => 'dihubungi'])->assertRedirect();

        $referral->refresh();
        $this->assertSame('dihubungi', $referral->status);
        $this->assertSame($admin->id, $referral->processed_by);
        $this->assertNotNull($referral->processed_at);
    }

    public function test_mahasiswa_tidak_dapat_mengelola_pengajuan_admin(): void
    {
        $student = User::factory()->create();
        $referral = ReferralRequest::create(['user_id' => $student->id, 'status' => 'pending']);

        $this->actingAs($student)->get(route('admin.referrals.show', $referral))->assertForbidden();
        $this->actingAs($student)->patch(route('admin.referrals.update', $referral), ['status' => 'selesai'])->assertForbidden();
    }
}
