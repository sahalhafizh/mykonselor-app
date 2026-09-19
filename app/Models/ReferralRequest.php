<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferralRequest extends Model
{
    public const STATUS_LABELS = [
        'pending' => 'Menunggu',
        'dihubungi' => 'Dihubungi',
        'selesai' => 'Selesai',
    ];

    protected $fillable = [
        'user_id', 'assessment_id', 'status', 'catatan', 'processed_by', 'processed_at',
    ];

    protected function casts(): array
    {
        return ['processed_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst($this->status);
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'pending' => 'text-bg-warning',
            'dihubungi' => 'text-bg-info',
            'selesai' => 'text-bg-success',
            default => 'text-bg-secondary',
        };
    }
}
