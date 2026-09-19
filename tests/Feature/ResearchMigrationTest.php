<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\User;
use App\Services\ResearchReportService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ResearchMigrationTest extends TestCase
{
    use DatabaseMigrations;

    public function test_upgrade_preserves_existing_database_without_inventing_participants_or_changing_results(): void
    {
        $this->artisan('migrate:rollback', ['--step' => 2, '--force' => true])->assertSuccessful();
        $this->assertFalse(Schema::hasTable('research_participations'));
        $user = User::factory()->create(['name' => 'Mahasiswa Lama', 'nim' => '202501234567']);
        $assessment = Assessment::create(['user_id' => $user->id, 'status' => 'completed', 'completed_at' => '2026-09-10 05:00:00']);
        $result = $assessment->result()->create(['stress_score' => 20, 'stress_cf' => .75, 'stress_severity' => 'sedang', 'anxiety_severity' => 'normal', 'depression_severity' => 'normal', 'highest_severity' => 'sedang']);
        $before = $result->fresh()->getAttributes();
        $password = $user->password;
        $this->artisan('migrate', ['--force' => true])->assertSuccessful();
        $this->assertSame('legacy', $assessment->fresh()->data_context);
        $this->assertNull($assessment->fresh()->research_snapshot);
        $this->assertSame($before, $result->fresh()->getAttributes());
        $this->assertSame($password, $user->fresh()->password);
        $this->assertSame('202501234567', $user->fresh()->nim);
        $this->assertDatabaseCount('research_participations', 0);
        config(['research.mode' => 'research', 'research.approved' => true, 'research.study_code' => 'qa-ti-7-8', 'research.starts_on' => '2026-09-01', 'research.ends_on' => '2026-09-30']);
        $this->assertSame(0, app(ResearchReportService::class)->summary()['respondents']);
        config(['research.mode' => 'demo']);
        $this->assertSame(1, app(ResearchReportService::class)->summary()['respondents']);
        $this->artisan('migrate', ['--force' => true])->assertSuccessful();
        $this->assertDatabaseCount('assessment_results', 1);
    }
}
