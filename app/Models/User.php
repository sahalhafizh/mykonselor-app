<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'nim', 'no_telp', 'name', 'email', 'password',
        'fakultas', 'program_studi', 'role', 'theme_preference', 'status', 'data_consent_at',
    ];

    protected $hidden = ['password', 'remember_token', 'two_factor_secret'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'data_consent_at' => 'datetime',
            'registration_semester' => 'integer',
            'password' => 'hashed',
            'no_telp' => 'encrypted',
            'must_change_password' => 'boolean',
            'temporary_password_expires_at' => 'datetime',
            'identity_verified_at' => 'datetime',
            'consent_snapshot' => 'array',
            'two_factor_secret' => 'encrypted',
            'two_factor_confirmed_at' => 'datetime',
            'two_factor_last_step' => 'integer',
        ];
    }

    public function username(): string
    {
        return 'nim';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isMahasiswa(): bool
    {
        return $this->role === 'mahasiswa';
    }

    public function maskedNim(): string
    {
        return str_repeat('*', max(strlen($this->nim) - 4, 0)).substr($this->nim, -4);
    }

    public function maskedPhone(): string
    {
        if (! $this->no_telp) {
            return '-';
        }
        $len = strlen($this->no_telp);

        return substr($this->no_telp, 0, 4).str_repeat('*', max($len - 8, 0)).substr($this->no_telp, -4);
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }

    public function researchParticipations(): HasMany
    {
        return $this->hasMany(ResearchParticipation::class);
    }

    public function referralRequests(): HasMany
    {
        return $this->hasMany(ReferralRequest::class);
    }
}
