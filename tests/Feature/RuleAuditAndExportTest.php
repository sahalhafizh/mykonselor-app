<?php

namespace Tests\Feature;

use App\Exports\AssessmentReportExport;
use App\Models\Article;
use App\Models\Assessment;
use App\Models\DiseaseSymptom;
use App\Models\ReferralRequest;
use App\Models\RuleAuditEvent;
use App\Models\RuleSetVersion;
use App\Models\Symptom;
use App\Models\User;
use App\Services\AssessmentService;
use App\Services\CertaintyFactorService;
use Database\Seeders\DiseaseSeeder;
use Database\Seeders\RuleSeeder;
use Database\Seeders\SymptomSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RuleAuditAndExportTest extends TestCase
{
    use RefreshDatabase;

    private function rules(): DiseaseSymptom
    {
        $this->seed([DiseaseSeeder::class, SymptomSeeder::class, RuleSeeder::class]);

        return DiseaseSymptom::firstOrFail();
    }

    private function payload(DiseaseSymptom $rule, array $values): array
    {
        return ['symptoms' => [$rule->symptom_id => array_merge(['selected' => '1', 'mb' => '0', 'md' => '1'], $values)]];
    }

    public function test_fractional_mb_and_unknown_symptom_are_rejected_without_mutation(): void
    {
        $rule = $this->rules();
        $before = $rule->getAttributes();
        $this->actingAs(User::factory()->admin()->create())->put(route('admin.rules.update', $rule->disease_id), $this->payload($rule, ['mb' => '0.5']))
            ->assertSessionHasErrors('symptoms.'.$rule->symptom_id.'.mb');
        $this->put(route('admin.rules.update', $rule->disease_id), ['symptoms' => [999999 => ['selected' => '1', 'mb' => '1', 'md' => '0']]])
            ->assertSessionHasErrors('symptoms');
        $this->assertSame($before, $rule->fresh()->getAttributes());
        $this->assertDatabaseCount('rule_audit_events', 0);
    }

    public function test_rule_update_and_deletion_history_survive_deleted_pivot(): void
    {
        $rule = $this->rules();
        $this->actingAs(User::factory()->admin()->create())->put(route('admin.rules.update', $rule->disease_id), $this->payload($rule, []))->assertRedirect();
        $this->assertSame(-1.0, (float) $rule->fresh()->cf_pakar);
        $this->put(route('admin.rules.update', $rule->disease_id), $this->payload($rule, ['selected' => '0']))->assertRedirect();
        $this->assertDatabaseMissing('disease_symptom', ['id' => $rule->id]);
        $this->assertDatabaseCount('rule_audit_events', 2);
        $event = RuleAuditEvent::latest('id')->firstOrFail();
        $this->assertSame('deleted', $event->action);
        $this->assertSame(-1, $event->before_values['cf_pakar']);
        $this->get(route('admin.rules.audit', $rule->disease_id))->assertOk()->assertSee($event->symptom_code);
    }

    public function test_failed_audit_insert_rolls_back_the_entire_rule_update(): void
    {
        $rule = $this->rules();
        $before = $rule->getAttributes();
        RuleAuditEvent::creating(function () {
            throw new \RuntimeException('Simulated audit failure');
        });
        try {
            $this->actingAs(User::factory()->admin()->create())->put(route('admin.rules.update', $rule->disease_id), $this->payload($rule, []))->assertStatus(500);
            $this->assertSame($before, $rule->fresh()->getAttributes());
            $this->assertDatabaseCount('rule_audit_events', 0);
        } finally {
            RuleAuditEvent::flushEventListeners();
        }
    }

    public function test_finalization_archives_exact_rules_without_changing_calculation(): void
    {
        $rule = $this->rules();
        $a = Assessment::create(['user_id' => User::factory()->create()->id, 'status' => 'in_progress']);
        $service = app(AssessmentService::class);
        foreach (Symptom::all() as $symptom) {
            $service->saveAnswer($a, $symptom, 2);
        }
        $expected = app(CertaintyFactorService::class)->calculateAllDiseaseCf($a->answers()->get()->pluck('cf_user', 'symptom_id'));
        $result = $service->finalize($a);
        $snapshot = RuleSetVersion::findOrFail($result->rule_set_version_id);
        $this->assertSame(hash_file('sha256', app_path('Services/CertaintyFactorService.php')), $snapshot->snapshot['cf_source_sha256']);
        $this->assertNotEmpty($snapshot->snapshot['rules'][0]['symptoms'][0]['text']);
        foreach ($expected as $cluster => $cf) {
            $this->assertEquals($cf, $result->{$cluster.'_cf'});
        }
        $before = $snapshot->snapshot;
        $this->actingAs(User::factory()->admin()->create())->put(route('admin.rules.update', $rule->disease_id), $this->payload($rule, []))->assertRedirect();
        $this->assertSame($before, $snapshot->fresh()->snapshot);
        $this->assertSame($result->id, $service->finalize($a)->id);
    }

    public function test_repeated_article_titles_get_distinct_slugs_and_existing_slug_stays(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $data = ['judul' => 'Judul yang sama', 'konten' => 'Konten artikel.', 'status' => 'draft'];
        $this->post('/admin/articles', $data)->assertRedirect('/admin/articles');
        $this->post('/admin/articles', $data)->assertRedirect('/admin/articles');
        $this->assertCount(2, Article::pluck('slug')->unique());
        $article = Article::first();
        $slug = $article->slug;
        $this->put(route('admin.articles.update', $article), [...$data, 'judul' => 'Judul revisi'])->assertRedirect();
        $this->assertSame($slug, $article->fresh()->slug);
    }

    public function test_export_requires_dates_and_rejects_oversize_results(): void
    {
        foreach (range(1, 3) as $i) {
            $a = Assessment::create(['user_id' => User::factory()->create()->id, 'status' => 'completed', 'completed_at' => '2026-09-10 07:00:00']);
            $a->result()->create(['stress_severity' => 'normal', 'anxiety_severity' => 'normal', 'depression_severity' => 'normal', 'highest_severity' => 'normal']);
        }
        $this->actingAs(User::factory()->admin()->create());
        $this->get('/admin/reports/export/excel')->assertSessionHasErrors(['from', 'to']);
        config(['security.export_max_rows' => 2]);
        $this->get('/admin/reports/export/excel?from=2026-09-10&to=2026-09-10')->assertSessionHasErrors('from');
        $this->get('/admin/reports/export/pdf?from=2020-01-01&to=2026-09-10')->assertSessionHasErrors('to');
        $this->assertDatabaseCount('export_audit_events', 0);
    }

    public function test_excel_export_download_is_bounded_logged_and_identity_can_be_hidden(): void
    {
        $u = User::factory()->create(['nim' => '001234567890', 'name' => 'Nama Pribadi']);
        $a = Assessment::create(['user_id' => $u->id, 'status' => 'completed', 'completed_at' => '2026-09-10 07:00:00']);
        $a->result()->create(['stress_severity' => 'normal', 'anxiety_severity' => 'normal', 'depression_severity' => 'normal', 'highest_severity' => 'normal']);
        $a->load(['user', 'result']);
        $mapped = (new AssessmentReportExport(collect([$a]), true))->map($a);
        $this->assertStringStartsWith('R-', $mapped[0]);
        $this->assertNotContains($u->nim, $mapped);
        $this->assertNotContains($u->name, $mapped);
        $this->assertSame($u->nim, (new AssessmentReportExport(collect([$a]), false))->map($a)[0]);
        $this->actingAs(User::factory()->admin()->create())->get('/admin/reports/export/excel?from=2026-09-10&to=2026-09-10&anonymized=1')->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertDatabaseHas('export_audit_events', ['format' => 'xlsx', 'anonymized' => 1, 'row_count' => 1]);
    }

    public function test_pdf_anonymous_template_contains_no_name_or_nim_and_download_works(): void
    {
        $u = User::factory()->create(['nim' => '001234567890', 'name' => 'Nama Pribadi']);
        $a = Assessment::create(['user_id' => $u->id, 'status' => 'completed', 'completed_at' => '2026-09-10 07:00:00']);
        $a->result()->create(['stress_severity' => 'normal', 'anxiety_severity' => 'normal', 'depression_severity' => 'normal', 'highest_severity' => 'normal']);
        $this->view('admin.reports.pdf', ['assessments' => collect([$a->load(['user', 'result'])]), 'from' => null, 'to' => null, 'anonymized' => true])
            ->assertDontSee($u->name)->assertDontSee($u->nim)->assertSee('Kode Responden');
        $this->actingAs(User::factory()->admin()->create())->get('/admin/reports/export/pdf?from=2026-09-10&to=2026-09-10&anonymized=1')
            ->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertDatabaseHas('export_audit_events', ['format' => 'pdf', 'anonymized' => 1, 'row_count' => 1]);
    }

    public function test_archived_student_does_not_break_admin_reports_or_referral_views(): void
    {
        $u = User::factory()->create();
        $a = Assessment::create(['user_id' => $u->id, 'status' => 'completed', 'completed_at' => now()]);
        $referral = ReferralRequest::create(['user_id' => $u->id, 'assessment_id' => $a->id, 'status' => 'pending']);
        $u->delete();
        $this->actingAs(User::factory()->admin()->create())->get('/admin/reports')->assertOk();
        $this->get('/admin/referrals')->assertOk();
        $this->get(route('admin.referrals.show', $referral))->assertOk();
    }
}
