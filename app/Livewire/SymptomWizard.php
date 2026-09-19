<?php

namespace App\Livewire;

use App\Models\Assessment;
use App\Models\Symptom;
use App\Models\User;
use App\Services\AssessmentService;
use App\Support\ResearchStudy;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class SymptomWizard extends Component
{
    #[Locked]
    public ?Assessment $assessment = null;

    #[Locked]
    public array $symptomIds = [];

    #[Locked]
    public int $currentIndex = 0;

    #[Locked]
    public array $answers = [];

    #[Locked]
    public bool $showSummary = false;

    #[Locked]
    public bool $showIntroduction = true;

    #[Locked]
    public bool $isSubmitting = false;

    #[Locked]
    public ?string $errorMessage = null;

    public function boot(): void
    {
        $this->ensureActiveStudent();
    }

    public function mount(): void
    {
        $this->symptomIds = Symptom::orderBy('kode')->pluck('id')->toArray();

        $this->assessment = Assessment::resumableBy(auth()->user())
            ->latest('updated_at')
            ->first();

        if (! $this->assessment) {
            return;
        }

        $this->ensureOwnsAssessment();

        $this->answers = $this->assessment->answers()
            ->pluck('answer_value', 'symptom_id')
            ->map(fn ($value) => (int) $value)
            ->toArray();

        $this->showIntroduction = count($this->answers) === 0;

        if ($this->totalSteps > 0) {
            $this->currentIndex = min(max($this->assessment->current_step - 1, 0), $this->totalSteps - 1);
        }
    }

    #[Computed]
    public function currentSymptom(): ?Symptom
    {
        $id = $this->symptomIds[$this->currentIndex] ?? null;

        return $id ? Symptom::find($id) : null;
    }

    #[Computed]
    public function totalSteps(): int
    {
        return count($this->symptomIds);
    }

    #[Computed]
    public function progressPercent(): float
    {
        return $this->totalSteps > 0 ? round((($this->currentIndex + 1) / $this->totalSteps) * 100) : 0;
    }

    public function scaleOptions(): array
    {
        return [0 => 'Tidak Pernah', 1 => 'Kadang-kadang', 2 => 'Sering', 3 => 'Hampir Selalu'];
    }

    public function selectAnswer(int $choiceIndex, AssessmentService $assessmentService): void
    {
        $this->ensureOwnsAssessment();
        abort_unless(array_key_exists($choiceIndex, AssessmentService::ANSWER_SCALE), 422);
        $symptomId = $this->symptomIds[$this->currentIndex];
        $symptom = Symptom::findOrFail($symptomId);

        $assessmentService->saveAnswer($this->assessment, $symptom, $choiceIndex);

        $this->answers[$symptomId] = $choiceIndex;

        $this->next();
    }

    public function next(): void
    {
        $this->ensureOwnsAssessment();
        if ($this->currentIndex < $this->totalSteps - 1) {
            $this->currentIndex++;
            $this->assessment->update(['current_step' => $this->currentIndex + 1]);
        } else {
            $this->showSummary = true;
        }
    }

    public function back(): void
    {
        $this->ensureOwnsAssessment();
        if ($this->showSummary) {
            $this->showSummary = false;

            return;
        }

        if ($this->currentIndex > 0) {
            $this->currentIndex--;
            $this->assessment->update(['current_step' => $this->currentIndex + 1]);
        }
    }

    public function submit(AssessmentService $assessmentService)
    {
        $this->ensureOwnsAssessment();
        $this->isSubmitting = true;
        $this->errorMessage = null;

        try {
            $assessmentService->finalize($this->assessment);

            return redirect()->route('assessment.result', $this->assessment);
        } catch (\RuntimeException $e) {
            $this->errorMessage = 'Masih ada pertanyaan yang belum Anda jawab. Silakan lengkapi sebelum menyelesaikan skrining.';
            $this->isSubmitting = false;
        }
    }

    public function start(): void
    {
        $this->ensureActiveStudent();
        abort_if($this->totalSteps === 0, 503, 'Pertanyaan skrining belum tersedia.');
        if (! $this->assessment) {
            // Serialize start requests for one student (including two tabs).
            $this->assessment = DB::transaction(function () {
                $user = User::whereKey(auth()->id())->lockForUpdate()->firstOrFail();
                ResearchStudy::assertCanParticipate($user);
                $assessment = Assessment::resumableBy($user)->first();
                if ($assessment) {
                    return $assessment;
                }
                $assessment = new Assessment(['user_id' => $user->id, 'status' => 'in_progress', 'started_at' => now(), 'current_step' => 1]);
                $assessment->forceFill(['data_context' => ResearchStudy::isResearch() ? 'research' : 'demo']);
                if (ResearchStudy::isResearch()) {
                    $participation = ResearchStudy::participation($user);
                    $assessment->forceFill([
                        'study_code' => $participation->study_code, 'research_participation_id' => $participation->id,
                        'research_semester' => $participation->semester,
                        'research_snapshot' => [
                            ...$participation->consent_snapshot,
                            'protocol_fingerprint' => $participation->protocol_fingerprint,
                            'semester' => $participation->semester,
                            'consented_at' => $participation->consented_at->toIso8601String(),
                            'verified_at' => $participation->verified_at->toIso8601String(),
                            'verified_by' => $participation->verified_by,
                        ],
                    ]);
                }
                $assessment->save();

                return $assessment;
            });
        }

        $this->ensureOwnsAssessment();
        $this->showIntroduction = false;
    }

    protected function ensureOwnsAssessment(): void
    {
        $this->ensureActiveStudent();
        $this->assessment?->refresh();
        abort_unless(
            auth()->check()
            && $this->assessment
            && $this->assessment->user_id === auth()->id()
            && $this->assessment->status === 'in_progress',
            403
        );
        ResearchStudy::assertAssessmentWritable($this->assessment);
    }

    protected function ensureActiveStudent(): void
    {
        $user = auth()->user()?->fresh();
        abort_unless($user && $user->isMahasiswa() && $user->status === 'aktif', 403);
        abort_if(config('security.require_student_verification') && ! $user->identity_verified_at, 403, 'Identitas mahasiswa belum diverifikasi.');
        ResearchStudy::assertCanParticipate($user);
    }

    public function render()
    {
        return view('livewire.symptom-wizard');
    }
}
