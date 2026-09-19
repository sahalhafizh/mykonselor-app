<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentResult extends Model
{
    protected $fillable = [
        'assessment_id',
        'stress_cf', 'anxiety_cf', 'depression_cf',
        'stress_score', 'anxiety_score', 'depression_score',
        'stress_severity', 'anxiety_severity', 'depression_severity',
        'highest_severity', 'calculation_version', 'rule_set_version_id',
    ];

    protected function casts(): array
    {
        return [
            'stress_cf' => 'float',
            'anxiety_cf' => 'float',
            'depression_cf' => 'float',
        ];
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function clusters(): array
    {
        return [
            'stress' => ['label' => 'Stres', 'cf_percentage' => round($this->stress_cf * 100, 1), 'score' => $this->stress_score, 'severity' => $this->stress_severity],
            'anxiety' => ['label' => 'Kecemasan', 'cf_percentage' => round($this->anxiety_cf * 100, 1), 'score' => $this->anxiety_score, 'severity' => $this->anxiety_severity],
            'depression' => ['label' => 'Depresi', 'cf_percentage' => round($this->depression_cf * 100, 1), 'score' => $this->depression_score, 'severity' => $this->depression_severity],
        ];
    }

    public static function severityBadgeColor(string $severity): string
    {
        return match ($severity) {
            'normal' => 'success', 'ringan' => 'info', 'sedang' => 'warning', 'berat' => 'danger',
            default => 'secondary',
        };
    }
}
