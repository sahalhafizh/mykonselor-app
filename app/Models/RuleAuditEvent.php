<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RuleAuditEvent extends Model
{
    public $timestamps = false;

    protected $fillable = ['disease_id', 'symptom_id', 'symptom_code', 'action', 'before_values', 'after_values', 'actor_id', 'created_at'];

    protected function casts(): array
    {
        return ['before_values' => 'array', 'after_values' => 'array', 'created_at' => 'datetime'];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
