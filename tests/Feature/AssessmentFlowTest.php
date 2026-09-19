<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\Symptom;
use App\Models\User;
use App\Services\AssessmentService;
use Database\Seeders\DiseaseSeeder;
use Database\Seeders\RuleSeeder;
use Database\Seeders\SymptomSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssessmentFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([DiseaseSeeder::class, SymptomSeeder::class, RuleSeeder::class]);
    }

    public function test_menjawab_semua_gejala_stress_maksimal_menghasilkan_severity_berat(): void
    {
        $user = User::factory()->create(['role' => 'mahasiswa']);
        $assessment = Assessment::create(['user_id' => $user->id, 'status' => 'in_progress']);

        $service = app(AssessmentService::class);

        foreach (Symptom::all() as $symptom) {
            $service->saveAnswer($assessment, $symptom, 3);
        }

        $result = $service->finalize($assessment->fresh());

        $this->assertEquals(42, $result->stress_score);
        $this->assertEquals(42, $result->anxiety_score);
        $this->assertEquals(42, $result->depression_score);
        $this->assertEquals('berat', $result->stress_severity);
        $this->assertEquals('berat', $result->anxiety_severity);
        $this->assertEquals('berat', $result->depression_severity);
        $this->assertEquals('berat', $result->highest_severity);
        $this->assertGreaterThan(0.5, $result->stress_cf);
    }

    public function test_menjawab_semua_gejala_dengan_tidak_pernah_menghasilkan_severity_normal(): void
    {
        $user = User::factory()->create(['role' => 'mahasiswa']);
        $assessment = Assessment::create(['user_id' => $user->id, 'status' => 'in_progress']);

        $service = app(AssessmentService::class);

        foreach (Symptom::all() as $symptom) {
            $service->saveAnswer($assessment, $symptom, 0);
        }

        $result = $service->finalize($assessment->fresh());

        $this->assertEquals(0, $result->stress_score);
        $this->assertEquals('normal', $result->stress_severity);
        $this->assertEquals('normal', $result->highest_severity);
        $this->assertEquals(0.0, $result->stress_cf);
    }

    public function test_finalize_ditolak_jika_jawaban_belum_lengkap(): void
    {
        $user = User::factory()->create(['role' => 'mahasiswa']);
        $assessment = Assessment::create(['user_id' => $user->id, 'status' => 'in_progress']);

        $service = app(AssessmentService::class);

        foreach (Symptom::take(5)->get() as $symptom) {
            $service->saveAnswer($assessment, $symptom, 2);
        }

        $this->expectException(\RuntimeException::class);
        $service->finalize($assessment->fresh());
    }

    public function test_user_lain_tidak_bisa_akses_hasil_assessment_orang_lain(): void
    {
        $owner = User::factory()->create(['role' => 'mahasiswa']);
        $stranger = User::factory()->create(['role' => 'mahasiswa']);

        $assessment = Assessment::create([
            'user_id' => $owner->id, 'status' => 'completed', 'completed_at' => now(),
        ]);

        $response = $this->actingAs($stranger)->get(route('assessment.result', $assessment));

        $response->assertForbidden();
    }
}
