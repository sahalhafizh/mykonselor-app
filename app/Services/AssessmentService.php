<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\AssessmentResult;
use App\Models\Disease;
use App\Models\RuleSetVersion;
use App\Models\Symptom;
use App\Models\User;
use App\Support\ResearchStudy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssessmentService
{
    public function __construct(
        protected CertaintyFactorService $cf,
        protected Dass21Service $dass,
    ) {}

    public const ANSWER_SCALE = [
        0 => ['label' => 'Tidak Pernah', 'answer_value' => 0, 'cf_user' => 0.0],
        1 => ['label' => 'Kadang-kadang', 'answer_value' => 1, 'cf_user' => 0.4],
        2 => ['label' => 'Sering', 'answer_value' => 2, 'cf_user' => 0.8],
        3 => ['label' => 'Hampir Selalu', 'answer_value' => 3, 'cf_user' => 1.0],
    ];

    public const CALCULATION_VERSION = '1.0';

    public function finalize(Assessment $assessment): AssessmentResult
    {
        return DB::transaction(function () use ($assessment) {
            if (ResearchStudy::isResearch()) {
                User::whereKey($assessment->user_id)->lockForUpdate()->firstOrFail();
            }
            $assessment = Assessment::whereKey($assessment->id)->lockForUpdate()->firstOrFail();
            if ($assessment->status === 'completed') {
                return $assessment->result()->firstOrFail();
            }
            abort_unless($assessment->status === 'in_progress', 409);
            ResearchStudy::assertAssessmentWritable($assessment);
            $this->assertAllQuestionsAnswered($assessment);

            $answers = $assessment->answers()->get();

            $userCfBySymptomId = $answers->pluck('cf_user', 'symptom_id');
            // Serialize against rule edits and calculate from the same loaded rules that are archived.
            $diseases = Disease::orderBy('id')->lockForUpdate()->get();
            $diseases->load(['symptoms' => fn ($query) => $query->sharedLock()]);
            $cfPerCluster = [];
            $rules = [];
            foreach ($diseases as $disease) {
                $cfPerCluster[$disease->cluster_key] = $this->cf->calculateDiseaseCf($disease, $userCfBySymptomId);
                $rules[] = ['disease_id' => $disease->id, 'cluster' => $disease->cluster_key,
                    'symptoms' => $disease->symptoms->map(fn ($s) => [
                        'id' => $s->id, 'code' => $s->kode, 'text' => $s->deskripsi,
                        'mb' => (float) $s->pivot->mb, 'md' => (float) $s->pivot->md, 'cf' => (float) $s->pivot->cf_pakar,
                    ])->all()];
            }
            $snapshot = ['calculation_version' => self::CALCULATION_VERSION, 'answer_scale' => self::ANSWER_SCALE,
                'cf_source_sha256' => hash_file('sha256', app_path('Services/CertaintyFactorService.php')),
                'dass_source_sha256' => hash_file('sha256', app_path('Services/Dass21Service.php')), 'rules' => $rules];
            $version = RuleSetVersion::firstOrCreate(
                ['fingerprint' => hash('sha256', json_encode($snapshot, JSON_THROW_ON_ERROR))], ['snapshot' => $snapshot]);

            $dassPerCluster = $this->dass->calculateAll($answers);

            $severities = collect($dassPerCluster)->pluck('severity')->all();
            $highestSeverity = $this->dass->highestSeverity($severities);

            $result = AssessmentResult::updateOrCreate(
                ['assessment_id' => $assessment->id],
                [
                    'stress_cf' => $cfPerCluster['stress'] ?? 0,
                    'anxiety_cf' => $cfPerCluster['anxiety'] ?? 0,
                    'depression_cf' => $cfPerCluster['depression'] ?? 0,
                    'stress_score' => $dassPerCluster['stress']['score'],
                    'anxiety_score' => $dassPerCluster['anxiety']['score'],
                    'depression_score' => $dassPerCluster['depression']['score'],
                    'stress_severity' => $dassPerCluster['stress']['severity'],
                    'anxiety_severity' => $dassPerCluster['anxiety']['severity'],
                    'depression_severity' => $dassPerCluster['depression']['severity'],
                    'highest_severity' => $highestSeverity,
                    'calculation_version' => self::CALCULATION_VERSION,
                    'rule_set_version_id' => $version->id,
                ]
            );

            $assessment->update(['status' => 'completed', 'completed_at' => now()]);

            return $result;
        });
    }

    public function saveAnswer(Assessment $assessment, Symptom $symptom, int $choiceIndex): void
    {
        if (! array_key_exists($choiceIndex, self::ANSWER_SCALE)) {
            throw new \InvalidArgumentException('Pilihan jawaban tidak valid.');
        }

        $choice = self::ANSWER_SCALE[$choiceIndex];

        DB::transaction(function () use ($assessment, $symptom, $choice) {
            if (ResearchStudy::isResearch()) {
                User::whereKey($assessment->user_id)->lockForUpdate()->firstOrFail();
            }
            $assessment = Assessment::whereKey($assessment->id)->lockForUpdate()->firstOrFail();
            abort_unless($assessment->status === 'in_progress', 409);
            ResearchStudy::assertAssessmentWritable($assessment);
            $assessment->answers()->updateOrCreate(
                ['symptom_id' => $symptom->id],
                ['answer_value' => $choice['answer_value'], 'cf_user' => $choice['cf_user'], 'dass_score' => $choice['answer_value']]
            );
        });
    }

    protected function assertAllQuestionsAnswered(Assessment $assessment): void
    {
        $totalSymptoms = Symptom::count();
        $answeredCount = $assessment->answers()->count();

        if ($answeredCount < $totalSymptoms) {
            Log::warning('Assessment finalize ditolak: jawaban belum lengkap', [
                'assessment_id' => $assessment->id, 'answered' => $answeredCount, 'required' => $totalSymptoms,
            ]);
            throw new \RuntimeException('Seluruh pertanyaan wajib dijawab sebelum melihat hasil.');
        }
    }
}
