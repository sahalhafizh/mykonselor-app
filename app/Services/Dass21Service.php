<?php

namespace App\Services;

use App\Models\Symptom;
use Illuminate\Support\Collection;

class Dass21Service
{
    protected array $clusters = [
        'stress' => ['G01', 'G06', 'G08', 'G11', 'G12', 'G14', 'G18'],
        'anxiety' => ['G02', 'G04', 'G07', 'G09', 'G15', 'G19', 'G20'],
        'depression' => ['G03', 'G05', 'G10', 'G13', 'G16', 'G17', 'G21'],
    ];

    protected array $cutoffs = [
        'stress' => [
            ['max' => 14, 'label' => 'normal'], ['max' => 18, 'label' => 'ringan'],
            ['max' => 25, 'label' => 'sedang'], ['max' => 33, 'label' => 'berat'],
        ],
        'anxiety' => [
            ['max' => 7, 'label' => 'normal'], ['max' => 9, 'label' => 'ringan'],
            ['max' => 14, 'label' => 'sedang'], ['max' => 19, 'label' => 'berat'],
        ],
        'depression' => [
            ['max' => 9, 'label' => 'normal'], ['max' => 13, 'label' => 'ringan'],
            ['max' => 20, 'label' => 'sedang'], ['max' => 27, 'label' => 'berat'],
        ],
    ];

    public function clusterKeys(): array
    {
        return array_keys($this->clusters);
    }

    public function symptomCodesFor(string $cluster): array
    {
        return $this->clusters[$cluster] ?? [];
    }

    public function clusterOf(string $symptomKode): ?string
    {
        foreach ($this->clusters as $cluster => $codes) {
            if (in_array($symptomKode, $codes, true)) {
                return $cluster;
            }
        }
        return null;
    }

    public function scoreFor(string $cluster, Collection $answersByCode): int
    {
        $codes = $this->symptomCodesFor($cluster);
        $rawSum = collect($codes)->sum(fn ($code) => (int) $answersByCode->get($code, 0));
        return $rawSum * 2;
    }

    public function severityFor(string $cluster, int $score): string
    {
        foreach ($this->cutoffs[$cluster] as $tier) {
            if ($score <= $tier['max']) {
                return $tier['label'];
            }
        }
        return 'berat';
    }

    public function severityRank(string $severity): int
    {
        return match ($severity) {
            'normal' => 0, 'ringan' => 1, 'sedang' => 2, 'berat' => 3, default => 0,
        };
    }

    public function highestSeverity(array $severities): string
    {
        $ranked = collect($severities)->sortByDesc(fn ($s) => $this->severityRank($s));
        return $ranked->first() ?? 'normal';
    }

    public function calculateAll(Collection $answers): array
    {
        $symptomCodes = Symptom::whereIn('id', $answers->pluck('symptom_id'))->pluck('kode', 'id');
        $answersByCode = $answers->mapWithKeys(
            fn ($answer) => [$symptomCodes->get($answer->symptom_id) => $answer->answer_value]
        );

        $result = [];
        foreach ($this->clusterKeys() as $cluster) {
            $score = $this->scoreFor($cluster, $answersByCode);
            $result[$cluster] = ['score' => $score, 'severity' => $this->severityFor($cluster, $score)];
        }
        return $result;
    }
}
