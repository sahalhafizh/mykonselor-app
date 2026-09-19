<?php

namespace Tests\Feature;

use App\Livewire\SymptomWizard;
use App\Models\User;
use Database\Seeders\SymptomSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class ReleaseSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function enrolledAdmin(): User
    {
        $user = User::factory()->admin()->create(['nim' => null, 'password' => 'AdminPribadi123!']);
        $user->forceFill(['two_factor_secret' => (new Google2FA)->generateSecretKey(32), 'two_factor_confirmed_at' => now()])->save();

        return $user;
    }

    public function test_student_profile_rejects_even_a_crafted_name_change(): void
    {
        $u = User::factory()->create(['name' => 'Nama Tetap']);
        $this->actingAs($u)->patch('/profile', ['name' => 'Nama Lain', 'email' => $u->email, 'no_telp' => '081234567890'])
            ->assertSessionHasErrorsIn('profileUpdate', 'name');
        $this->assertSame('Nama Tetap', $u->fresh()->name);
    }

    public function test_admin_full_nim_and_prefix_search_are_restricted_to_admin(): void
    {
        $u = User::factory()->create(['nim' => '202501234567']);
        $other = User::factory()->create(['nim' => '202601234567']);
        $this->actingAs($u)->get('/admin/users?search=2025')->assertForbidden();
        Auth::logout();
        session()->flush();
        $this->actingAs(User::factory()->admin()->create())->get('/admin/users?search=2025')
            ->assertOk()->assertSee($u->nim)->assertDontSee($other->nim);
    }

    public function test_admin_cannot_use_student_nim_login_even_with_correct_password(): void
    {
        $u = User::factory()->admin()->create(['nim' => '000000000000', 'password' => 'AdminPribadi123!']);
        $this->post('/login', ['nim' => $u->nim, 'password' => 'AdminPribadi123!'])->assertSessionHasErrors('nim');
        $this->assertGuest();
    }

    public function test_student_cannot_use_admin_email_login(): void
    {
        $u = User::factory()->create(['password' => 'AdminPribadi123!']);
        $this->post('/admin/login', ['email' => $u->email, 'password' => 'AdminPribadi123!'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_admin_mfa_does_not_authenticate_until_valid_code_and_rejects_replay(): void
    {
        $u = $this->enrolledAdmin();
        $code = (new Google2FA)->getCurrentOtp($u->two_factor_secret);
        $this->post('/admin/login', ['email' => $u->email, 'password' => 'AdminPribadi123!'])->assertRedirect('/admin/challenge');
        $this->assertGuest();
        $this->get('/admin/challenge')->assertOk()->assertSee('Kode enam digit');
        $this->get('/admin/dashboard')->assertRedirect('/admin/login');
        $this->post('/admin/challenge', ['code' => $code])->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($u);
        $this->get('/admin/dashboard')->assertOk();
        $this->post('/logout');
        $this->post('/admin/login', ['email' => $u->email, 'password' => 'AdminPribadi123!'])->assertRedirect('/admin/challenge');
        $this->post('/admin/challenge', ['code' => $code])->assertSessionHasErrors('code');
        $this->assertGuest();
        $this->assertNotSame($u->two_factor_secret, DB::table('users')->where('id', $u->id)->value('two_factor_secret'));
    }

    public function test_disabled_admin_cannot_finish_pending_mfa(): void
    {
        $u = $this->enrolledAdmin();
        $this->post('/admin/login', ['email' => $u->email, 'password' => 'AdminPribadi123!']);
        $u->update(['status' => 'nonaktif']);
        $this->post('/admin/challenge', ['code' => (new Google2FA)->getCurrentOtp($u->two_factor_secret)])->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    public function test_changed_password_invalidates_pending_admin_mfa(): void
    {
        $u = $this->enrolledAdmin();
        $this->post('/admin/login', ['email' => $u->email, 'password' => 'AdminPribadi123!']);
        $u->update(['password' => 'AdminPribadiBaru123!']);
        $this->post('/admin/challenge', ['code' => (new Google2FA)->getCurrentOtp($u->two_factor_secret)])->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    public function test_mfa_challenge_expires_and_requires_a_password_step(): void
    {
        $u = $this->enrolledAdmin();
        $this->post('/admin/challenge', ['code' => '123456'])->assertRedirect('/admin/login');
        $this->post('/admin/login', ['email' => $u->email, 'password' => 'AdminPribadi123!']);
        $pending = session('admin_mfa_pending');
        $pending['expires'] = time() - 1;
        $this->withSession(['admin_mfa_pending' => $pending])->post('/admin/challenge', ['code' => (new Google2FA)->getCurrentOtp($u->two_factor_secret)])
            ->assertRedirect('/admin/login');
        $this->assertGuest();
    }

    public function test_admin_login_is_rate_limited_even_if_password_later_correct(): void
    {
        $u = User::factory()->admin()->create(['password' => 'AdminPribadi123!']);
        for ($i = 0; $i < 5; $i++) {
            $this->post('/admin/login', ['email' => $u->email, 'password' => 'salah'])->assertSessionHasErrors('email');
        }
        $this->post('/admin/login', ['email' => $u->email, 'password' => 'AdminPribadi123!'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_admin_enrolment_is_required_and_confirms_current_password_and_code(): void
    {
        config(['security.require_admin_mfa' => true]);
        $u = User::factory()->admin()->create(['nim' => null, 'password' => 'AdminPribadi123!']);
        $this->post('/admin/login', ['email' => $u->email, 'password' => 'AdminPribadi123!'])->assertRedirect('/admin/security');
        $this->get('/admin/users')->assertRedirect('/admin/security');
        $this->get('/admin/security')->assertOk();
        $secret = session('mfa_setup.secret');
        $code = (new Google2FA)->getCurrentOtp($secret);
        $this->post('/admin/security', ['current_password' => 'salah', 'code' => $code])->assertSessionHasErrors('current_password');
        $this->assertNull($u->fresh()->two_factor_confirmed_at);
        $this->post('/admin/security', ['current_password' => 'AdminPribadi123!', 'code' => $code])->assertRedirect('/admin/dashboard');
        $this->get('/admin/dashboard')->assertOk();
        $this->get('/admin/security')->assertOk()->assertDontSee($secret);
        $this->assertNotNull($u->fresh()->two_factor_confirmed_at);
    }

    public function test_existing_enrolled_admin_session_requires_its_own_mfa_marker(): void
    {
        $u = $this->enrolledAdmin();
        $this->actingAs($u)->get('/admin/reports')->assertRedirect('/admin/security');
        $this->withSession(['admin_mfa_verified' => 'forged'])->get('/admin/reports')->assertRedirect('/admin/security');
    }

    public function test_temporary_password_must_be_changed_before_student_data_access(): void
    {
        $u = User::factory()->create(['password' => 'TemporaryAman123!']);
        $u->forceFill(['must_change_password' => true, 'temporary_password_expires_at' => now()->addDay()])->save();
        $this->post('/login', ['nim' => $u->nim, 'password' => 'TemporaryAman123!'])->assertRedirect('/dashboard');
        $this->get('/dashboard')->assertRedirect('/account/password');
        $this->get('/history')->assertRedirect('/account/password');
        $this->put('/account/password', ['current_password' => 'TemporaryAman123!', 'password' => 'PribadiAman123!', 'password_confirmation' => 'PribadiAman123!'])
            ->assertRedirect('/dashboard');
        $this->assertFalse($u->fresh()->must_change_password);
        $this->assertNull($u->fresh()->temporary_password_expires_at);
        $this->assertTrue(Hash::check('PribadiAman123!', $u->fresh()->password));
        $this->get('/dashboard')->assertOk();
    }

    public function test_expired_temporary_password_is_rejected(): void
    {
        $u = User::factory()->create(['password' => 'TemporaryAman123!']);
        $u->forceFill(['must_change_password' => true, 'temporary_password_expires_at' => now()->subMinute()])->save();
        $this->post('/login', ['nim' => $u->nim, 'password' => 'TemporaryAman123!'])->assertSessionHasErrors('nim');
        $this->assertGuest();
    }

    public function test_unverified_identity_cannot_start_screening_or_bypass_via_livewire(): void
    {
        config(['security.require_student_verification' => true]);
        $this->seed(SymptomSeeder::class);
        $u = User::factory()->create(['identity_verified_at' => null]);
        $this->actingAs($u)->get('/assessment')->assertRedirect('/verification');
        Livewire::actingAs($u)->test(SymptomWizard::class)->assertForbidden();
        $this->assertDatabaseCount('assessments', 0);
    }

    public function test_only_admin_with_password_can_verify_student_identity(): void
    {
        config(['security.require_student_verification' => true]);
        $this->seed(SymptomSeeder::class);
        $u = User::factory()->create(['identity_verified_at' => null]);
        $this->actingAs($u)->patch(route('admin.users.verify', $u), ['admin_password' => 'password'])->assertForbidden();
        Auth::logout();
        session()->flush();
        $admin = User::factory()->admin()->create(['password' => 'AdminPribadi123!']);
        $this->actingAs($admin)->patch(route('admin.users.verify', $u), ['admin_password' => 'salah'])->assertSessionHasErrors('admin_password');
        $this->assertNull($u->fresh()->identity_verified_at);
        $this->patch(route('admin.users.verify', $u), ['admin_password' => 'AdminPribadi123!'])->assertRedirect();
        $this->assertSame($admin->id, $u->fresh()->identity_verified_by);
        Auth::logout();
        session()->flush();
        $this->actingAs($u->fresh())->get('/assessment')->assertOk();
    }

    public function test_registration_stores_consent_but_does_not_trust_verification_fields(): void
    {
        $this->post('/register', ['name' => 'Nama Tetap', 'nim' => '202501234567', 'no_telp' => '081234567890', 'email' => 'student@example.test',
            'password' => 'PribadiAman123!', 'password_confirmation' => 'PribadiAman123!', 'consent' => '1', 'semester' => 7,
            'identity_verified_at' => now()->toDateTimeString(), 'role' => 'admin'])->assertRedirect('/dashboard');
        $u = User::where('nim', '202501234567')->firstOrFail();
        $this->assertSame(config('privacy.version'), $u->consent_version);
        $this->assertSame(config('privacy.version'), $u->consent_snapshot['version']);
        $this->assertNull($u->identity_verified_at);
        $this->assertSame('mahasiswa', $u->role);
    }

    public function test_production_registration_is_closed_until_privacy_is_approved(): void
    {
        $this->app->instance('env', 'production');
        config(['privacy.approved' => false]);
        $this->get('/register')->assertStatus(503)->assertSee('Pendaftaran belum tersedia');
        $this->assertDatabaseCount('users', 0);
    }

    public function test_csrf_is_enforced_when_not_running_in_test_environment(): void
    {
        $this->app->instance('env', 'local');
        $this->post('/admin/login', ['email' => 'admin@example.test', 'password' => 'anything'])->assertStatus(419);
    }
}
