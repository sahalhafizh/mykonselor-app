<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResearchParticipation extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['semester' => 'integer', 'consent_snapshot' => 'array', 'consented_at' => 'datetime', 'verified_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
