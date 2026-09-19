<?php

namespace Tests\Feature;

use App\Livewire\SymptomWizard;
use App\Models\Assessment;
use App\Models\Symptom;
use App\Models\User;
use App\Services\AssessmentService;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\SymptomSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class HostingSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_csp_uses_a_nonce_and_has_no_eval(): void
    {
        $response = $this->get('/login')->assertOk();
        $policy = $response->headers->get('Content-Security-Policy');
        $this->assertStringContainsString("object-src 'none'", $policy);
        $this->assertStringNotContainsString('unsafe-eval', $policy);
        preg_match("/nonce-([^']+)/", $policy, $matches);
        $this->assertNotEmpty($matches[1]);
        $response->assertSee('nonce="'.$matches[1].'"', false);
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }

    public function test_demo_seeder_never_resets_an_existing_password(): void
    {
        $this->seed(AdminUserSeeder::class);
        $user = User::where('nim', '000000000000')->firstOrFail();
        $user->update(['password' => 'PasswordPribadi123!']);
        $this->seed(AdminUserSeeder::class);
        $this->assertTrue(Hash::check('PasswordPribadi123!', $user->fresh()->password));
    }

    public function test_demo_accounts_are_not_seeded_in_production(): void
    {
        $this->app->instance('env', 'production');
        $this->artisan('db:seed', ['--class' => AdminUserSeeder::class, '--force' => true])->assertSuccessful();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_password_change_revokes_remember_tokens_and_database_sessions(): void
    {
        config(['session.driver' => 'database']);
        $user = User::factory()->create(['password' => 'password', 'remember_token' => 'old-token']);
        DB::table('sessions')->insert([
            'id' => 'old-device-session', 'user_id' => $user->id,
            'payload' => base64_encode(''), 'last_activity' => time(),
        ]);
        $this->actingAs($user)->put('/profile/password', [
            'current_password' => 'password',
            'password' => 'PasswordBaru123!', 'password_confirmation' => 'PasswordBaru123!',
        ])->assertRedirect('/profile');
        $this->assertDatabaseMissing('sessions', ['id' => 'old-device-session']);
        $this->assertNotSame('old-token', $user->fresh()->remember_token);
        $this->assertTrue(Hash::check('PasswordBaru123!', $user->fresh()->password));
        $this->get('/profile')->assertOk();
    }

    public function test_old_password_hash_in_session_cannot_access_profile(): void
    {
        $user = User::factory()->create(['password' => 'PasswordLama123!']);
        $oldHash = $user->password;
        $user->update(['password' => 'PasswordBaru123!']);
        $this->actingAs($user)->withSession(['password_hash_web' => $oldHash])
            ->get('/profile')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_admin_password_is_required_for_reset(): void
    {
        $admin = User::factory()->admin()->create(['password' => 'PasswordAdmin123!']);
        $student = User::factory()->create(['password' => 'PasswordMahasiswa123!']);
        $this->actingAs($admin)->patch(route('admin.users.reset-password', $student), [
            'admin_password' => 'salah',
        ])->assertSessionHasErrors('admin_password');
        $this->assertTrue(Hash::check('PasswordMahasiswa123!', $student->fresh()->password));
    }

    public function test_admin_reset_revokes_existing_sessions(): void
    {
        config(['session.driver' => 'database']);
        $admin = User::factory()->admin()->create(['password' => 'PasswordAdmin123!']);
        $student = User::factory()->create(['password' => 'PasswordMahasiswa123!', 'remember_token' => 'old-token']);
        DB::table('sessions')->insert([
            'id' => 'student-old-session', 'user_id' => $student->id,
            'payload' => base64_encode(''), 'last_activity' => time(),
        ]);
        $this->actingAs($admin)->patch(route('admin.users.reset-password', $student), [
            'admin_password' => 'PasswordAdmin123!',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('sessions', ['id' => 'student-old-session']);
        $this->assertFalse(Hash::check('PasswordMahasiswa123!', $student->fresh()->password));
        $this->assertNotSame('old-token', $student->fresh()->remember_token);
    }

    public function test_array_profile_fields_are_validation_errors(): void
    {
        $student = User::factory()->create();
        $this->actingAs($student)->patch('/profile', [
            'name' => ['unexpected'], 'email' => ['unexpected'], 'no_telp' => ['unexpected'],
        ])->assertSessionHasErrorsIn('profileUpdate', ['name', 'email', 'no_telp']);
    }

    public function test_weak_new_password_is_rejected(): void
    {
        $student = User::factory()->create(['password' => 'password']);
        $this->actingAs($student)->put('/profile/password', [
            'current_password' => 'password',
            'password' => 'onlylowercase', 'password_confirmation' => 'onlylowercase',
        ])->assertSessionHasErrorsIn('passwordUpdate', 'password');
    }

    public function test_livewire_client_cannot_overwrite_server_state(): void
    {
        $this->seed(SymptomSeeder::class);
        $student = User::factory()->create();
        $component = Livewire::actingAs($student)->test(SymptomWizard::class);
        $this->expectException(CannotUpdateLockedPropertyException::class);
        $component->set('symptomIds', []);
    }

    public function test_deactivated_student_cannot_start_from_an_open_tab(): void
    {
        $this->seed(SymptomSeeder::class);
        $student = User::factory()->create();
        $component = Livewire::actingAs($student)->test(SymptomWizard::class);
        $student->update(['status' => 'nonaktif']);
        $component->call('start')->assertForbidden();
        $this->assertDatabaseCount('assessments', 0);
    }

    public function test_admin_cannot_mount_student_wizard(): void
    {
        Livewire::actingAs(User::factory()->admin()->create())
            ->test(SymptomWizard::class)->assertForbidden();
    }

    public function test_repeated_finalization_does_not_change_result_or_completion_time(): void
    {
        $this->seed(DatabaseSeeder::class);
        $student = User::factory()->create();
        $assessment = Assessment::create(['user_id' => $student->id, 'status' => 'in_progress']);
        $service = app(AssessmentService::class);
        foreach (Symptom::all() as $symptom) {
            $service->saveAnswer($assessment, $symptom, 1);
        }
        $result = $service->finalize($assessment);
        $completedAt = $assessment->fresh()->completed_at->toDateTimeString();
        $this->travel(1)->hours();
        $second = $service->finalize($assessment);
        $this->assertSame($result->id, $second->id);
        $this->assertSame($completedAt, $assessment->fresh()->completed_at->toDateTimeString());
        $this->assertDatabaseCount('assessment_results', 1);
    }

    public function test_answers_cannot_be_changed_after_completion(): void
    {
        $this->seed(SymptomSeeder::class);
        $assessment = Assessment::create(['user_id' => User::factory()->create()->id, 'status' => 'completed']);
        try {
            app(AssessmentService::class)->saveAnswer($assessment, Symptom::firstOrFail(), 2);
            $this->fail('Expected a conflict when editing a completed assessment.');
        } catch (HttpException $exception) {
            $this->assertSame(409, $exception->getStatusCode());
        }
        $this->assertDatabaseCount('assessment_answers', 0);
    }

    public function test_invalid_report_range_is_rejected(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get('/admin/reports?from=2026-09-08&to=2026-08-01')
            ->assertSessionHasErrors('to');
    }
}
