<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RegistrationSemesterMigrationTest extends TestCase
{
    use DatabaseMigrations;

    public function test_upgrade_preserves_old_accounts_and_results_without_guessing_registration_semester(): void
    {
        $this->artisan('migrate:rollback', ['--step' => 1, '--force' => true])->assertSuccessful();
        $this->assertFalse(Schema::hasColumn('users', 'registration_semester'));
        $user = User::factory()->create(['name' => 'Mahasiswa Lama', 'nim' => '202501234567']);
        $userBefore = $user->fresh()->getAttributes();
        $assessment = Assessment::create(['user_id' => $user->id, 'status' => 'completed', 'completed_at' => '2026-09-10 05:00:00']);
        $result = $assessment->result()->create(['stress_score' => 20, 'stress_cf' => .75, 'stress_severity' => 'sedang',
            'anxiety_severity' => 'normal', 'depression_severity' => 'normal', 'highest_severity' => 'sedang']);
        $resultBefore = $result->fresh()->getAttributes();

        $this->artisan('migrate', ['--force' => true])->assertSuccessful();
        $this->assertNull($user->fresh()->registration_semester);
        $this->assertSame($userBefore, array_diff_key($user->fresh()->getAttributes(), ['registration_semester' => true]));
        $this->assertSame($resultBefore, $result->fresh()->getAttributes());
        $this->assertDatabaseCount('research_participations', 0);
        $this->artisan('migrate', ['--force' => true])->assertSuccessful();
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('assessment_results', 1);
    }
}
