<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\ReferralRequest;
use App\Models\Symptom;
use App\Models\User;
use App\Support\PrivacyPolicy;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\SymptomSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class ReleaseOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_admin_uses_email_and_has_no_student_nim(): void
    {
        $this->artisan('app:create-admin')
            ->expectsQuestion('Nama admin', 'Admin Pribadi')
            ->expectsQuestion('Email admin', 'ADMIN.PRIBADI@example.test')
            ->expectsQuestion('Nomor telepon (format 628...)', '6281234567890')
            ->expectsQuestion('Password (12–72 karakter, huruf besar/kecil dan angka)', 'AdminPribadi123!')
            ->expectsQuestion('Ulangi password', 'AdminPribadi123!')->assertSuccessful();
        $u = User::where('email', 'admin.pribadi@example.test')->firstOrFail();
        $this->assertNull($u->nim);
        $this->assertTrue($u->isAdmin());
    }

    public function test_privacy_configuration_requires_approved_real_contact_and_retention(): void
    {
        $this->assertFalse(PrivacyPolicy::isReady());
        config(['privacy.approved' => true, 'privacy.operator' => 'Pengelola Pengujian', 'privacy.contact_email' => 'admin@example.test', 'privacy.retention_days' => 30, 'privacy.backup_retention_days' => 7]);
        $this->assertFalse(PrivacyPolicy::isReady());
        // A syntactically valid fixture only; no email is sent or ownership claimed.
        config(['privacy.contact_email' => 'privacy@pengelola-qa.ac.id']);
        $this->assertTrue(PrivacyPolicy::isReady());
        config(['privacy.retention_days' => 0]);
        $this->assertFalse(PrivacyPolicy::isReady());
    }

    public function test_hosting_check_fails_for_local_configuration(): void
    {
        $this->artisan('app:hosting-check')->assertFailed();
    }

    public function test_demo_disabling_requires_confirmed_personal_admin_and_keeps_data(): void
    {
        $this->seed(AdminUserSeeder::class);
        $this->artisan('app:disable-demo-accounts')->assertFailed();
        $admin = User::factory()->admin()->create(['nim' => null]);
        $admin->forceFill(['two_factor_secret' => (new Google2FA)->generateSecretKey(32), 'two_factor_confirmed_at' => now()])->save();
        $u = User::where('nim', '123456789012')->firstOrFail();
        $a = Assessment::create(['user_id' => $u->id, 'status' => 'completed', 'completed_at' => now()]);
        $this->artisan('app:disable-demo-accounts')->expectsConfirmation('Nonaktifkan akun contoh 000000000000 dan 123456789012?', 'yes')->assertSuccessful();
        $this->assertSame('nonaktif', $u->fresh()->status);
        $this->assertDatabaseHas('assessments', ['id' => $a->id]);
        $this->assertSame('aktif', $admin->fresh()->status);
    }

    public function test_mfa_recovery_is_console_only_and_revokes_existing_sessions(): void
    {
        config(['session.driver' => 'database']);
        $u = User::factory()->admin()->create();
        $u->forceFill(['two_factor_secret' => (new Google2FA)->generateSecretKey(32), 'two_factor_confirmed_at' => now()])->save();
        DB::table('sessions')->insert(['id' => 'recovery-fixture', 'user_id' => $u->id, 'payload' => '', 'last_activity' => time()]);
        $this->artisan('app:reset-admin-mfa', ['email' => $u->email])
            ->expectsConfirmation('Identitas pemilik sudah diverifikasi? Reset MFA akan mencabut semua sesi.', 'yes')->assertSuccessful();
        $this->assertNull($u->fresh()->two_factor_secret);
        $this->assertNull($u->fresh()->two_factor_confirmed_at);
        $this->assertDatabaseMissing('sessions', ['id' => 'recovery-fixture']);
    }

    public function test_admin_password_recovery_preserves_second_factor(): void
    {
        $u = User::factory()->admin()->create();
        $secret = (new Google2FA)->generateSecretKey(32);
        $u->forceFill(['two_factor_secret' => $secret, 'two_factor_confirmed_at' => now()])->save();
        $this->artisan('app:reset-admin-password', ['email' => $u->email])
            ->expectsConfirmation('Identitas pemilik akun sudah diverifikasi melalui prosedur pengelola?', 'yes')
            ->expectsQuestion('Password baru', 'PemulihanAdmin123!')->expectsQuestion('Ulangi password baru', 'PemulihanAdmin123!')->assertSuccessful();
        $this->assertTrue($u->fresh()->must_change_password);
        $this->assertSame($secret, $u->fresh()->two_factor_secret);
        $this->assertTrue(Hash::check('PemulihanAdmin123!', $u->fresh()->password));
    }

    public function test_operator_purge_requires_confirmation_and_removes_related_data(): void
    {
        $this->seed(SymptomSeeder::class);
        $u = User::factory()->create();
        $a = Assessment::create(['user_id' => $u->id, 'status' => 'completed', 'completed_at' => now()]);
        $answer = $a->answers()->create(['symptom_id' => Symptom::first()->id, 'answer_value' => 1, 'cf_user' => 0.4, 'dass_score' => 1]);
        $r = ReferralRequest::create(['user_id' => $u->id, 'assessment_id' => $a->id]);
        $prompt = 'Setelah memverifikasi permintaan dan backup, ketik HAPUS '.$u->nim;
        $this->artisan('app:purge-student', ['nim' => $u->nim])->expectsQuestion($prompt, 'batal')->assertFailed();
        $this->assertDatabaseHas('users', ['id' => $u->id]);
        $this->artisan('app:purge-student', ['nim' => $u->nim])->expectsQuestion($prompt, 'HAPUS '.$u->nim)->assertSuccessful();
        $this->assertDatabaseMissing('users', ['id' => $u->id]);
        $this->assertDatabaseMissing('assessments', ['id' => $a->id]);
        $this->assertDatabaseMissing('assessment_answers', ['id' => $answer->id]);
        $this->assertDatabaseMissing('referral_requests', ['id' => $r->id]);
    }

    public function test_admin_counts_follow_jakarta_day_and_month_boundaries(): void
    {
        $this->travelTo(Carbon::parse('2026-09-01 00:30:00', 'Asia/Jakarta'));
        $u = User::factory()->create();
        Assessment::create(['user_id' => $u->id, 'status' => 'completed', 'completed_at' => '2026-08-31 17:15:00']);
        Assessment::create(['user_id' => $u->id, 'status' => 'completed', 'completed_at' => '2026-08-31 16:59:00']);
        $this->actingAs(User::factory()->admin()->create())->get('/admin/dashboard')->assertOk()
            ->assertViewHas('totalScreeningsToday', 1)->assertViewHas('totalAssessmentsThisMonth', 1);
    }

    public function test_verification_filter_separates_pending_students(): void
    {
        $pending = User::factory()->create(['nim' => '202501234560', 'identity_verified_at' => null]);
        $verified = User::factory()->create(['nim' => '202501234561']);
        $this->actingAs(User::factory()->admin()->create())->get('/admin/users?verification=pending')->assertOk()
            ->assertSee($pending->nim)->assertDontSee($verified->nim);
    }
}
