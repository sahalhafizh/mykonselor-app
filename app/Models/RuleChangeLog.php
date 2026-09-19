<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RuleChangeLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['disease_symptom_id', 'changed_by', 'old_mb', 'old_md', 'new_mb', 'new_md', 'created_at'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    public function diseaseSymptom(): BelongsTo
    {
        return $this->belongsTo(DiseaseSymptom::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
