<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\AssessmentResult;
use App\Models\DiseaseSymptom;
use App\Models\User;
use Database\Seeders\DiseaseSeeder;
use Database\Seeders\RuleSeeder;
use Database\Seeders\SymptomSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ReleaseMigrationTest extends TestCase
{
    use DatabaseMigrations;

    public function test_upgrade_keeps_existing_users_results_and_copies_legacy_rule_history(): void
    {
        // Rebuild only the dedicated test database to the immediately previous schema.
        $this->artisan('migrate:rollback', ['--step' => 3, '--force' => true])->assertSuccessful();
        $this->assertFalse(Schema::hasColumn('users', 'two_factor_secret'));
        $this->seed([DiseaseSeeder::class, SymptomSeeder::class, RuleSeeder::class]);
        $u = User::create(['nim' => '202501234567', 'name' => 'Data Lama', 'email' => 'legacy@example.test', 'no_telp' => '6281234567890', 'password' => 'PasswordLama123!', 'role' => 'mahasiswa', 'status' => 'aktif']);
        $a = Assessment::create(['user_id' => $u->id, 'status' => 'completed', 'completed_at' => now()]);
        $result = AssessmentResult::create(['assessment_id' => $a->id, 'stress_score' => 14, 'stress_severity' => 'ringan', 'anxiety_severity' => 'normal', 'depression_severity' => 'normal', 'highest_severity' => 'ringan']);
        $rule = DiseaseSymptom::first();
        DB::table('rule_change_logs')->insert(['disease_symptom_id' => $rule->id, 'changed_by' => null, 'old_mb' => 0, 'old_md' => 1, 'new_mb' => 1, 'new_md' => 0, 'created_at' => now()]);
        $this->artisan('migrate', ['--force' => true])->assertSuccessful();
        $this->assertSame('Data Lama', $u->fresh()->name);
        $this->assertSame('202501234567', $u->fresh()->nim);
        $this->assertSame('6281234567890', $u->fresh()->no_telp);
        $this->assertTrue(Hash::check('PasswordLama123!', $u->fresh()->password));
        $this->assertEquals(14, $result->fresh()->stress_score);
        $this->assertNull($result->fresh()->rule_set_version_id);
        $this->assertNull($u->fresh()->identity_verified_at);
        $this->assertDatabaseCount('rule_audit_events', 1);
        $this->artisan('migrate', ['--force' => true])->assertSuccessful();
        $this->assertDatabaseCount('rule_audit_events', 1);
        $this->assertDatabaseCount('assessment_results', 1);
    }
}
