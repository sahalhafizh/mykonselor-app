<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiseaseSymptom extends Model
{
    protected $table = 'disease_symptom';

    protected $fillable = ['disease_id', 'symptom_id', 'rule_code', 'mb', 'md', 'cf_pakar', 'updated_by'];

    protected static function booted(): void
    {
        static::saving(function (DiseaseSymptom $row) {
            $row->cf_pakar = round($row->mb - $row->md, 3);
        });
    }

    public function disease(): BelongsTo
    {
        return $this->belongsTo(Disease::class);
    }

    public function symptom(): BelongsTo
    {
        return $this->belongsTo(Symptom::class);
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function changeLogs(): HasMany
    {
        return $this->hasMany(RuleChangeLog::class);
    }
}
