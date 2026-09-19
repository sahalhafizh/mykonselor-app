<?php

namespace Tests\Feature;

use App\Livewire\SymptomWizard;
use App\Models\Assessment;
use App\Models\User;
use App\Services\ResearchReportService;
use App\Support\ResearchStudy;
use Carbon\Carbon;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\DiseaseSeeder;
use Database\Seeders\RuleSeeder;
use Database\Seeders\SymptomSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class ResearchModeTest extends TestCase
{
    use RefreshDatabase;

    private Redirector $httpRedirector;

    protected function setUp(): void
    {
        parent::setUp();
        $this->httpRedirector = app('redirect');
        config([
            'research.mode' => 'research', 'research.study_code' => 'qa-ti-7-8',
            'research.starts_on' => '2026-09-01', 'research.ends_on' => '2026-09-30',
            'research.selection' => 'first', 'research.approved' => true,
            'privacy.approved' => true, 'privacy.operator' => 'Pengelola Pengujian',
            // Fixture only; no email is sent or ownership represented.
            'privacy.contact_email' => 'privacy@pengelola-qa.ac.id',
            'privacy.retention_days' => 30, 'privacy.backup_retention_days' => 7,
        ]);
        $this->travelTo(Carbon::parse('2026-09-18 12:00:00', 'Asia/Jakarta'));
    }

    private function switchUser(User $user): void
    {
        // A Livewire exception skips dehydrate in this in-process test harness.
        // A following HTTP request must use Laravel's redirector, as a fresh request would.
        app()->instance('redirect', $this->httpRedirector);
        Auth::logout();
        session()->flush();
        $this->actingAs($user);
    }

    private function participant(int $semester = 7, bool $verified = true): User
    {
        $user = User::factory()->create();
        $user->researchParticipations()->create([
            'study_code' => config('research.study_code'), 'semester' => $semester,
            'program_studi' => 'Teknik Informatika', 'protocol_fingerprint' => ResearchStudy::fingerprint(),
            'consent_version' => config('research.consent_version'), 'consent_snapshot' => ResearchStudy::snapshot(),
            'consented_at' => now(), 'verified_at' => $verified ? now() : null,
        ]);

        return $user;
    }

    private function completed(User $user, string $at, string $severity = 'normal', string $context = 'research'): Assessment
    {
        $participation = ResearchStudy::participation($user);
        $assessment = new Assessment(['user_id' => $user->id, 'status' => 'completed', 'completed_at' => $at]);
        $assessment->forceFill([
            'data_context' => $context, 'study_code' => $context === 'research' ? config('research.study_code') : null,
            'research_participation_id' => $context === 'research' ? $participation?->id : null,
            'research_semester' => $context === 'research' ? $participation?->semester : null,
            'research_snapshot' => $context === 'research' ? ['protocol_fingerprint' => ResearchStudy::fingerprint()] : null,
        ])->save();
        $assessment->result()->create([
            'stress_score' => 38, 'anxiety_score' => 34, 'depression_score' => 36,
            'stress_cf' => .9231, 'anxiety_cf' => .8162, 'depression_cf' => .7543,
            'stress_severity' => $severity, 'anxiety_severity' => $severity, 'depression_severity' => $severity,
            'highest_severity' => $severity,
        ]);

        return $assessment;
    }

    public function test_registration_requires_semester_and_separate_research_consent_and_ignores_forged_privileges(): void
    {
        $page = $this->get('/register')->assertOk()->assertSee('Pilih semester Anda')->assertSee('name="research_consent"', false);
        $this->assertSame(1, substr_count($page->getContent(), 'name="semester"'));
        $data = ['name' => 'Mahasiswa Penelitian', 'nim' => '202501234567', 'no_telp' => '081234567890',
            'email' => 'participant@example.test', 'password' => 'PribadiAman123!', 'password_confirmation' => 'PribadiAman123!', 'consent' => '1'];
        $this->post('/register', $data)->assertSessionHasErrors(['semester', 'research_consent']);
        $this->post('/register', [...$data, 'semester' => 6, 'research_consent' => '1'])->assertSessionHasErrors('semester');
        $this->post('/register', [...$data, 'semester' => 8, 'research_consent' => '1', 'role' => 'admin', 'verified_at' => now()->toDateTimeString()])
            ->assertRedirect('/dashboard');
        $user = User::where('nim', $data['nim'])->firstOrFail();
        $participation = ResearchStudy::participation($user);
        $this->assertSame(8, $participation->semester);
        $this->assertSame(8, $user->registration_semester);
        $this->assertTrue(ResearchStudy::hasCurrentConsent($participation));
        $this->assertNull($participation->verified_at);
        $this->assertNull($user->identity_verified_at);
        $this->assertTrue($user->isMahasiswa());
        $this->get('/assessment')->assertRedirect('/research/participation');
    }

    public function test_existing_account_needs_new_consent_and_admin_cohort_verification(): void
    {
        $user = User::factory()->create(['nim' => '202501234567']);
        $this->switchUser($user);
        $this->get('/research/participation')->assertOk()->assertSee('Simpan Persetujuan');
        $this->get('/profile')->assertOk()->assertSee('Semester penelitian');
        $this->post('/research/participation', ['semester' => 7, 'research_consent' => 1])->assertRedirect('/research/participation');
        $this->get('/assessment')->assertRedirect('/research/participation');
        $this->patch(route('admin.users.verify', $user), ['admin_password' => 'password', 'eligibility_confirmed' => 1])->assertForbidden();
        $admin = User::factory()->admin()->create(['password' => 'AdminPribadi123!']);
        $this->switchUser($admin);
        $this->get(route('admin.users.edit', $user))->assertOk()->assertSee($user->nim);
        $this->patch(route('admin.users.verify', $user), ['admin_password' => 'salah', 'eligibility_confirmed' => 1])->assertSessionHasErrors('admin_password');
        $this->patch(route('admin.users.verify', $user), ['admin_password' => 'AdminPribadi123!'])->assertSessionHasErrors('eligibility_confirmed');
        $this->patch(route('admin.users.verify', $user), ['admin_password' => 'AdminPribadi123!', 'eligibility_confirmed' => 1])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame($admin->id, ResearchStudy::participation($user)->verified_by);
        $this->switchUser($user->fresh());
        $this->get('/assessment')->assertOk();
        $this->post('/research/participation', ['semester' => 8, 'research_consent' => 1])->assertForbidden();
        $this->assertSame(7, ResearchStudy::participation($user)->semester);
    }

    public function test_verification_rejects_wrong_program_or_outdated_consent(): void
    {
        $user = $this->participant(8, false);
        $user->update(['program_studi' => 'Program Lain']);
        $this->switchUser(User::factory()->admin()->create(['password' => 'AdminPribadi123!']));
        $data = ['admin_password' => 'AdminPribadi123!', 'eligibility_confirmed' => 1];
        $this->patch(route('admin.users.verify', $user), $data)->assertSessionHasErrors('eligibility_confirmed');
        $user->update(['program_studi' => 'Teknik Informatika']);
        config(['research.consent_version' => 'versi-baru']);
        $this->patch(route('admin.users.verify', $user), $data)->assertSessionHasErrors('eligibility_confirmed');
        $this->assertNull(ResearchStudy::participation($user)->verified_at);
    }

    public function test_research_wizard_completes_21_answers_and_stores_cohort_without_exposing_scores(): void
    {
        $this->seed([DiseaseSeeder::class, SymptomSeeder::class, RuleSeeder::class]);
        $user = $this->participant(8);
        $wizard = Livewire::actingAs($user)->test(SymptomWizard::class)->call('start');
        foreach (range(1, 21) as $step) {
            $wizard->call('selectAnswer', 1);
        }
        $assessment = $user->assessments()->firstOrFail();
        $wizard->call('submit')->assertRedirect(route('assessment.result', $assessment));
        $assessment->refresh();
        $this->assertSame('completed', $assessment->status);
        $this->assertSame('research', $assessment->data_context);
        $this->assertSame(8, $assessment->research_semester);
        $this->assertSame(ResearchStudy::fingerprint(), $assessment->research_snapshot['protocol_fingerprint']);
        $this->assertSame(21, $assessment->answers()->count());
        $this->assertEquals(14, $assessment->result->stress_score);
        $this->get(route('assessment.result', $assessment))->assertOk()->assertViewIs('research.receipt')
            ->assertSee('12:00')->assertDontSee('Skor:')->assertDontSee('Hasil Certainty Factor');
    }

    public function test_scores_are_not_leaked_in_history_result_referral_or_json_accept_requests(): void
    {
        $user = $this->participant();
        $assessment = $this->completed($user, '2026-09-18 05:00:00', 'berat');
        $this->switchUser($user);
        foreach (['assessment.result', 'assessment.referral'] as $route) {
            $response = $this->get(route($route, $assessment), ['Accept' => 'application/json'])->assertOk();
            $response->assertDontSee('Skor: 38')->assertDontSee('92.3%')->assertDontSee('Tingkat keparahan tertinggi')->assertDontSee('stress_cf');
        }
        $this->get('/history')->assertOk()->assertSee('Lihat Tanda Selesai')->assertDontSee('Skor 38')->assertDontSee('Berat');
        $this->get(route('assessment.referral', $assessment))->assertSee('https://wa.me/6281288178208', false);
        $this->post(route('assessment.referral.store', $assessment), ['catatan' => 'Memerlukan bantuan'])->assertRedirect();
        $this->assertDatabaseHas('referral_requests', ['user_id' => $user->id, 'assessment_id' => $assessment->id]);
        $this->get('/admin/reports')->assertForbidden();
        $this->switchUser($this->participant());
        $this->get(route('assessment.result', $assessment))->assertForbidden();
        $this->get(route('assessment.referral', $assessment))->assertForbidden();
    }

    public function test_research_result_remains_hidden_when_local_mode_is_changed_to_demo(): void
    {
        $user = $this->participant();
        $research = $this->completed($user, '2026-09-18 05:00:00');
        $demo = $this->completed($user, '2026-09-18 05:01:00', 'normal', 'demo');
        config(['research.mode' => 'demo']);
        $this->switchUser($user);
        $this->get(route('assessment.result', $research))->assertViewIs('research.receipt');
        $this->get(route('assessment.result', $demo))->assertViewIs('assessment.result')->assertSee('Skor: 38')->assertSee('Demo lokal');
        $this->get('/history')->assertViewHas('assessments', fn ($items) => $items->total() === 1 && $items->first()->id === $demo->id);
    }

    public function test_admin_requires_mfa_even_on_shared_student_result_urls(): void
    {
        $assessment = $this->completed($this->participant(), '2026-09-18 05:00:00');
        config(['security.require_admin_mfa' => true]);
        $admin = User::factory()->admin()->create();
        $this->switchUser($admin);
        $this->get(route('assessment.result', $assessment))->assertRedirect('/admin/security');
        $this->get(route('assessment.referral', $assessment))->assertRedirect('/admin/security');
        $admin->forceFill(['two_factor_secret' => 'TESTMFASECRET', 'two_factor_confirmed_at' => now()])->save();
        $this->switchUser($admin->fresh());
        $this->withSession(['admin_mfa_verified' => $admin->id.':'.hash('sha256', $admin->two_factor_secret)])
            ->get(route('assessment.result', $assessment))->assertOk()->assertSee('Skor: 38');
    }

    public function test_revoking_participant_blocks_open_wizard_and_excludes_results(): void
    {
        $this->seed(SymptomSeeder::class);
        $user = $this->participant();
        $this->completed($user, '2026-09-18 05:00:00');
        $wizard = Livewire::actingAs($user)->test(SymptomWizard::class)->call('start');
        $this->switchUser(User::factory()->admin()->create(['password' => 'AdminPribadi123!']));
        $this->patch(route('admin.users.research.revoke', $user), ['admin_password' => 'AdminPribadi123!'])->assertRedirect();
        $this->switchUser($user->fresh());
        $wizard->call('selectAnswer', 1)->assertForbidden();
        $this->assertDatabaseCount('assessment_answers', 0);
        $this->assertSame(0, app(ResearchReportService::class)->summary()['respondents']);
    }

    public function test_first_or_last_result_is_selected_once_with_stable_ties_and_shared_report_filters(): void
    {
        $a = $this->participant(7);
        $b = $this->participant(8);
        $first = $this->completed($a, '2026-09-01 01:00:00', 'normal');
        $second = $this->completed($a, '2026-09-02 01:00:00', 'ringan');
        $last = $this->completed($a, '2026-09-02 01:00:00', 'berat');
        $other = $this->completed($b, '2026-09-03 01:00:00', 'sedang');
        $this->completed($a, '2026-08-31 16:59:59', 'berat'); // Before study in WIB.
        $this->completed($a, '2026-09-01 00:00:00', 'berat', 'demo');
        $this->completed($a, '2026-09-01 00:00:00', 'berat', 'legacy');
        $this->completed($this->participant(7, false), '2026-09-01 00:00:00');
        $deleted = $this->completed($a, '2026-08-31 17:00:00', 'berat');
        $deleted->delete();
        $missing = $this->completed($a, '2026-08-31 17:01:00');
        $missing->result()->delete();
        $foreign = $this->completed($a, '2026-08-31 17:02:00');
        $foreign->forceFill(['study_code' => 'other-study'])->save();
        $reports = app(ResearchReportService::class);
        $this->assertEqualsCanonicalizing([$first->id, $other->id], $reports->selected()->pluck('id')->all());
        $summary = $reports->summary();
        $this->assertSame(2, $summary['respondents']);
        $this->assertSame(4, $summary['responses']);
        $this->assertEquals(['normal' => 1, 'sedang' => 1], $summary['distribution']['stress']);
        $this->assertEquals([7 => 1, 8 => 1], $summary['semesters']);
        $this->assertEqualsCanonicalizing([$second->id, $other->id], $reports->selected(Carbon::parse('2026-09-01 17:00:00', 'UTC'))->pluck('id')->all());
        config(['research.selection' => 'last']);
        $this->assertEqualsCanonicalizing([$last->id, $other->id], $reports->selected()->pluck('id')->all());
        $this->assertEquals(['berat' => 1, 'sedang' => 1], $reports->summary()['distribution']['stress']);
    }

    public function test_dashboard_report_and_workbook_use_same_respondent_counts_and_log_method(): void
    {
        $a = $this->participant(7);
        $b = $this->participant(8);
        $this->completed($a, '2026-09-02 01:00:00', 'normal');
        $this->completed($a, '2026-09-03 01:00:00', 'berat');
        $this->completed($b, '2026-09-04 01:00:00', 'ringan');
        $this->switchUser(User::factory()->admin()->create());
        foreach (['/admin/dashboard', '/admin/reports'] as $url) {
            $this->get($url)->assertOk()->assertSee('2 mahasiswa unik')->assertViewHas('summary', fn ($s) => $s['respondents'] === 2 && $s['responses'] === 3);
        }
        $response = $this->get('/admin/reports/export/excel?from=2026-09-01&to=2026-09-30&anonymized=1')->assertOk();
        $book = IOFactory::load($response->baseResponse->getFile()->getPathname());
        $this->assertSame(2, $book->getSheetCount());
        $this->assertSame(3, $book->getSheetByName('Data Responden')->getHighestDataRow());
        $values = json_encode($book->getSheetByName('Data Responden')->toArray());
        $this->assertStringNotContainsString($a->nim, $values);
        $this->assertStringNotContainsString($a->name, $values);
        $this->assertEquals(2, $book->getSheetByName('Ringkasan dan Metode')->getCell('B7')->getValue());
        $book->disconnectWorksheets();
        $audit = DB::table('export_audit_events')->first();
        $this->assertSame(2, $audit->row_count);
        $context = json_decode($audit->report_context, true);
        $this->assertSame('qa-ti-7-8', $context['study_code']);
        $this->assertSame('first', $context['selection']);
        $this->get('/admin/reports/export/pdf?from=2026-09-01&to=2026-09-30&anonymized=1')->assertOk()->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_invalid_unapproved_or_closed_study_is_fail_closed_for_http_and_livewire(): void
    {
        $user = $this->participant();
        config(['research.approved' => false]);
        $this->switchUser($user);
        $this->get('/assessment')->assertRedirect('/research/participation');
        Livewire::actingAs($user)->test(SymptomWizard::class)->assertForbidden();
        config(['research.approved' => true, 'research.starts_on' => '2026-02-30']);
        $this->assertFalse(ResearchStudy::isReady());
        config(['research.starts_on' => '2026-09-01', 'research.ends_on' => '2026-09-10']);
        $this->assertFalse(ResearchStudy::isOpen());
        $this->post('/research/participation', ['semester' => 7, 'research_consent' => 1])->assertForbidden();
        $this->assertDatabaseCount('assessments', 0);
    }

    public function test_jakarta_period_boundaries_and_zero_denominator_are_handled(): void
    {
        $this->assertSame('2026-08-31 17:00:00', ResearchStudy::range()[0]->format('Y-m-d H:i:s'));
        $this->assertSame('2026-09-30 16:59:59', ResearchStudy::range()[1]->format('Y-m-d H:i:s'));
        $first = $this->completed($this->participant(), '2026-08-31 17:00:00');
        $last = $this->completed($this->participant(), '2026-09-30 16:59:59');
        $this->completed($this->participant(), '2026-09-30 17:00:00');
        $this->assertEqualsCanonicalizing([$first->id, $last->id], app(ResearchReportService::class)->selected()->pluck('id')->all());
        $this->switchUser(User::factory()->admin()->create());
        $this->get('/admin/reports?from=2026-10-01&to=2026-10-02')->assertOk()->assertSee('0 mahasiswa unik');
    }

    public function test_hosting_never_allows_demo_mode_or_seeds_example_accounts(): void
    {
        config(['research.mode' => 'demo']);
        $this->app->instance('env', 'production');
        $this->assertTrue(ResearchStudy::isResearch());
        $this->artisan('db:seed', ['--class' => AdminUserSeeder::class, '--force' => true])->assertSuccessful();
        $this->assertDatabaseCount('users', 0);
        config(['privacy.approved' => false]);
        $this->assertFalse(ResearchStudy::isReady());
    }

    public function test_research_mode_local_staging_does_not_seed_demo_and_old_data_is_not_imported(): void
    {
        $this->seed(AdminUserSeeder::class);
        $this->assertDatabaseCount('users', 0);
        $user = $this->participant();
        $this->completed($user, '2026-09-18 05:00:00', 'berat', 'legacy');
        $this->assertSame(0, app(ResearchReportService::class)->summary()['respondents']);
        $this->assertDatabaseCount('assessment_results', 1);
    }

    public function test_changed_protocol_requires_reconsent_and_does_not_trap_student_in_old_draft(): void
    {
        $this->seed(SymptomSeeder::class);
        $user = $this->participant();
        $oldWizard = Livewire::actingAs($user)->test(SymptomWizard::class)->call('start')->call('selectAnswer', 1);
        $oldDraft = $user->assessments()->firstOrFail();
        config(['research.consent_version' => 'versi-berikutnya']);
        $oldWizard->call('selectAnswer', 2)->assertForbidden();
        $this->switchUser($user);
        $this->get('/research/participation')->assertOk()->assertSee('Simpan Persetujuan');
        $this->post('/research/participation', ['semester' => 7, 'research_consent' => 1])->assertRedirect();
        $this->assertNull(ResearchStudy::participation($user)->verified_at);
        $this->switchUser(User::factory()->admin()->create(['password' => 'AdminPribadi123!']));
        $this->patch(route('admin.users.verify', $user), ['admin_password' => 'AdminPribadi123!', 'eligibility_confirmed' => 1])->assertSessionHasNoErrors();
        $this->switchUser($user->fresh());
        Livewire::actingAs($user->fresh())->test(SymptomWizard::class)->assertSet('assessment', null)->call('start')->assertOk();
        $this->assertSame(2, $user->assessments()->count());
        $this->assertSame(1, $oldDraft->answers()->count());
        $this->assertSame('in_progress', $oldDraft->fresh()->status);
    }

    public function test_corrected_semester_does_not_relabel_old_completed_data(): void
    {
        $user = $this->participant(7);
        $old = $this->completed($user, '2026-09-10 05:00:00');
        $participation = ResearchStudy::participation($user);
        $participation->update(['verified_at' => null]);
        $this->switchUser($user);
        $this->get('/research/participation')->assertSee('Perbaikan semester');
        $this->post('/research/participation', ['semester' => 8, 'research_consent' => 1])->assertRedirect();
        $this->switchUser(User::factory()->admin()->create(['password' => 'AdminPribadi123!']));
        $this->patch(route('admin.users.verify', $user), ['admin_password' => 'AdminPribadi123!', 'eligibility_confirmed' => 1])->assertSessionHasNoErrors();
        $this->assertSame(7, $old->fresh()->research_semester);
        $this->assertSame(0, app(ResearchReportService::class)->summary()['respondents']);
        $new = $this->completed($user, '2026-09-18 05:00:00');
        $this->assertSame([$new->id], app(ResearchReportService::class)->selected()->pluck('id')->all());
    }

    public function test_admin_filters_semester_and_unverified_research_even_if_identity_was_verified_previously(): void
    {
        $pending = $this->participant(8, false);
        $verified = $this->participant(7);
        $this->switchUser(User::factory()->admin()->create());
        $this->get('/admin/users?verification=pending&semester=8')->assertOk()->assertSee($pending->nim)->assertDontSee($verified->nim);
        $this->get('/admin/users?verification=verified&semester=7')->assertOk()->assertSee($verified->nim)->assertDontSee($pending->nim);
    }

    public function test_period_closure_blocks_submission_from_previously_open_browser(): void
    {
        $this->seed(SymptomSeeder::class);
        $user = $this->participant();
        $wizard = Livewire::actingAs($user)->test(SymptomWizard::class)->call('start')->call('selectAnswer', 1);
        $this->travelTo(Carbon::parse('2026-10-01 00:00:00', 'Asia/Jakarta'));
        $this->assertFalse(ResearchStudy::isOpen());
        $wizard->call('submit')->assertForbidden();
        $this->assertDatabaseCount('assessment_results', 0);
    }
}
