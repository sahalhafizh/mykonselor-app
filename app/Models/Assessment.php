<?php

namespace App\Models;

use App\Support\ResearchStudy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assessment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['user_id', 'status', 'current_step', 'started_at', 'completed_at'];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'research_snapshot' => 'array',
            'research_semester' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(AssessmentAnswer::class);
    }

    public function result(): HasOne
    {
        return $this->hasOne(AssessmentResult::class);
    }

    public function referralRequest(): HasOne
    {
        return $this->hasOne(ReferralRequest::class);
    }

    public function researchParticipation(): BelongsTo
    {
        return $this->belongsTo(ResearchParticipation::class);
    }

    public function scopeInCurrentMode(Builder $query): Builder
    {
        return ResearchStudy::isResearch()
            ? $query->where('data_context', 'research')->where('study_code', config('research.study_code'))
            : $query->whereIn('data_context', ['demo', 'legacy']);
    }

    public function scopeResumableBy(Builder $query, User $user): Builder
    {
        $query->inCurrentMode()->where('user_id', $user->id)->where('status', 'in_progress');
        if (ResearchStudy::isResearch()) {
            $query->where('research_snapshot->protocol_fingerprint', ResearchStudy::fingerprint())
                ->where('research_semester', ResearchStudy::participation($user)?->semester);
        }

        return $query;
    }
}
