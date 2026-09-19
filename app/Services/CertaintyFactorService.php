<?php

namespace App\Services;

use App\Models\Disease;
use Illuminate\Support\Collection;

class CertaintyFactorService
{
    public function calculateExpertCf(float $mb, float $md): float
    {
        return round($mb - $md, 3);
    }

    public function calculateSymptomCf(float $cfPakar, float $cfUser): float
    {
        return round($cfPakar * $cfUser, 3);
    }

    public function combinePositive(float $cf1, float $cf2): float
    {
        return $cf1 + $cf2 * (1 - $cf1);
    }

    public function combineNegative(float $cf1, float $cf2): float
    {
        return $cf1 + $cf2 * (1 + $cf1);
    }

    public function combineMixed(float $cf1, float $cf2): float
    {
        $denominator = 1 - min(abs($cf1), abs($cf2));
        return $denominator == 0.0 ? 0.0 : ($cf1 + $cf2) / $denominator;
    }

    public function combine(float $cf1, float $cf2): float
    {
        if ($cf1 >= 0 && $cf2 >= 0) {
            return $this->combinePositive($cf1, $cf2);
        }
        if ($cf1 < 0 && $cf2 < 0) {
            return $this->combineNegative($cf1, $cf2);
        }
        return $this->combineMixed($cf1, $cf2);
    }

    public function calculateDiseaseCf(Disease $disease, Collection $userCfBySymptomId): float
    {
        $cfCombined = null;

        foreach ($disease->symptoms as $symptom) {
            $cfUser = (float) $userCfBySymptomId->get($symptom->id, 0);
            if ($cfUser <= 0) {
                continue;
            }
            $cfPakar = (float) $symptom->pivot->cf_pakar;
            $cfElement = $this->calculateSymptomCf($cfPakar, $cfUser);
            $cfCombined = is_null($cfCombined) ? $cfElement : $this->combine($cfCombined, $cfElement);
        }

        return round($cfCombined ?? 0.0, 3);
    }

    public function calculateAllDiseaseCf(Collection $userCfBySymptomId): array
    {
        $diseases = Disease::with('symptoms')->get();
        $result = [];
        foreach ($diseases as $disease) {
            $result[$disease->cluster_key] = $this->calculateDiseaseCf($disease, $userCfBySymptomId);
        }
        return $result;
    }
}
