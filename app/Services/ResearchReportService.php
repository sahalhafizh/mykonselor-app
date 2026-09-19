<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\AssessmentResult;
use App\Support\ResearchStudy;
use Illuminate\Database\Eloquent\Builder;

class ResearchReportService
{
    public function eligible($from = null, $to = null): Builder
    {
        $query = Assessment::inCurrentMode()->where('status', 'completed')
            ->whereNotNull('completed_at')->whereHas('result');
        if (ResearchStudy::isResearch()) {
            if (! ResearchStudy::isReady()) {
                return $query->whereRaw('1 = 0');
            }
            $query->whereBetween('completed_at', ResearchStudy::range())
                ->whereIn('research_semester', config('research.semesters'))
                ->whereNotNull('research_snapshot')
                ->whereHas('researchParticipation', fn ($q) => $q->whereNotNull('verified_at')
                    ->where('study_code', config('research.study_code'))
                    ->whereColumn('research_participations.user_id', 'assessments.user_id')
                    ->whereColumn('research_participations.semester', 'assessments.research_semester')
                    ->where('program_studi', config('research.program')));
        }

        return $query->when($from, fn ($q) => $q->where('completed_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('completed_at', '<=', $to));
    }

    public function selected($from = null, $to = null): Builder
    {
        $eligible = $this->eligible($from, $to);
        $comparison = config('research.selection') === 'last' ? '>' : '<';

        // Both sides use the same eligible set. Timestamp ties use ID.
        return (clone $eligible)->whereNotExists(function ($q) use ($eligible, $comparison) {
            $q->selectRaw('1')->fromSub((clone $eligible)->select(['assessments.id', 'assessments.user_id', 'assessments.completed_at']), 'candidate')
                ->whereColumn('candidate.user_id', 'assessments.user_id')
                ->where(function ($q) use ($comparison) {
                    $q->whereColumn('candidate.completed_at', $comparison, 'assessments.completed_at')
                        ->orWhere(fn ($q) => $q->whereColumn('candidate.completed_at', 'assessments.completed_at')
                            ->whereColumn('candidate.id', $comparison, 'assessments.id'));
                });
        });
    }

    public function summary($from = null, $to = null): array
    {
        $selected = $this->selected($from, $to);
        $count = (clone $selected)->count();
        $distribution = [];
        foreach (['stress', 'anxiety', 'depression'] as $cluster) {
            $column = $cluster.'_severity';
            $distribution[$cluster] = AssessmentResult::whereIn('assessment_id', (clone $selected)->select('assessments.id'))
                ->select($column)->selectRaw('COUNT(*) as total')->groupBy($column)->pluck('total', $column)->all();
        }

        return [
            'respondents' => $count, 'responses' => $this->eligible($from, $to)->count(),
            'distribution' => $distribution,
            'semesters' => (clone $selected)->select('research_semester')->selectRaw('COUNT(*) as total')
                ->groupBy('research_semester')->pluck('total', 'research_semester')->all(),
        ];
    }

    public function metadata($from = null, $to = null): array
    {
        if (ResearchStudy::isResearch() && ($period = ResearchStudy::range())) {
            $from ??= $period[0];
            $to ??= $period[1];
        }
        $timezone = config('app.display_timezone', 'Asia/Jakarta');

        return [
            'mode' => ResearchStudy::isResearch() ? 'Penelitian' : 'Demo lokal — data simulasi/legacy',
            'study_code' => ResearchStudy::isResearch() ? config('research.study_code') : 'DEMO',
            'from' => $from?->copy()->timezone($timezone)->format('Y-m-d'),
            'to' => $to?->copy()->timezone($timezone)->format('Y-m-d'),
            'timezone' => $timezone, 'selection' => config('research.selection'),
            'study_starts_on' => ResearchStudy::isResearch() ? config('research.starts_on') : null,
            'study_ends_on' => ResearchStudy::isResearch() ? config('research.ends_on') : null,
            'selection_label' => ResearchStudy::selectionLabel(), 'target' => 'Teknik Informatika UNPAM semester 7–8',
            'scope' => 'Satu mahasiswa dihitung satu kali dalam rentang laporan. Gejala per skala, bukan diagnosis atau prevalensi seluruh mahasiswa.',
        ];
    }
}
