<?php

namespace App\Support;

use App\Models\Assessment;
use App\Models\ResearchParticipation;
use App\Models\User;
use Carbon\CarbonImmutable;

class ResearchStudy
{
    public static function isResearch(): bool
    {
        return ! app()->environment(['local', 'testing']) || config('research.mode') !== 'demo';
    }

    public static function range(): ?array
    {
        try {
            $dates = [];
            foreach (['starts_on', 'ends_on'] as $key) {
                $value = (string) config('research.'.$key);
                if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                    return null;
                }
                $date = CarbonImmutable::createFromFormat('!Y-m-d', $value, config('app.display_timezone', 'Asia/Jakarta'));
                if (! $date || $date->format('Y-m-d') !== $value) {
                    return null;
                }
                $dates[] = $date;
            }

            return $dates[0]->lte($dates[1]) ? [$dates[0]->startOfDay()->utc(), $dates[1]->endOfDay()->utc()] : null;
        } catch (\Throwable) {
            return null;
        }
    }

    public static function isReady(): bool
    {
        return config('research.approved') === true
            && (! app()->isProduction() || PrivacyPolicy::isReady())
            && preg_match('/^[a-z0-9][a-z0-9_-]{2,63}$/', (string) config('research.study_code')) === 1
            && in_array(config('research.selection'), ['first', 'last'], true)
            && self::range() !== null;
    }

    public static function isOpen(): bool
    {
        $range = self::range();

        return self::isReady() && CarbonImmutable::now('UTC')->betweenIncluded($range[0], $range[1]);
    }

    public static function snapshot(): array
    {
        return [
            'study_code' => config('research.study_code'),
            'university' => config('research.university'), 'program' => config('research.program'),
            'semesters' => config('research.semesters'),
            'starts_on' => config('research.starts_on'), 'ends_on' => config('research.ends_on'),
            'consent_version' => config('research.consent_version'), 'privacy_version' => config('privacy.version'),
            'operator' => config('privacy.operator'), 'contact_email' => config('privacy.contact_email'),
            'retention_days' => config('privacy.retention_days'), 'backup_retention_days' => config('privacy.backup_retention_days'),
            'purpose' => 'Penelitian gambaran gejala stres, kecemasan, dan depresi pada responden TI UNPAM semester 7–8.',
            'results_access' => 'Skor dan interpretasi individual untuk peneliti/admin berwenang; peserta menerima tanda selesai dan akses bantuan.',
            'voluntary' => true,
        ];
    }

    public static function fingerprint(): string
    {
        return hash('sha256', json_encode(self::snapshot(), JSON_THROW_ON_ERROR));
    }

    public static function participation(User $user): ?ResearchParticipation
    {
        return $user->researchParticipations()->where('study_code', config('research.study_code'))->first();
    }

    public static function hasCurrentConsent(?ResearchParticipation $participation): bool
    {
        return $participation && $participation->consented_at
            && hash_equals(self::fingerprint(), $participation->protocol_fingerprint);
    }

    public static function eligible(User $user): bool
    {
        $participation = self::participation($user);

        return $user->isMahasiswa() && $user->status === 'aktif' && $user->identity_verified_at
            && $user->program_studi === config('research.program')
            && self::hasCurrentConsent($participation) && $participation->verified_at
            && $participation->program_studi === config('research.program')
            && in_array($participation->semester, config('research.semesters'), true);
    }

    public static function assertCanParticipate(User $user): void
    {
        if (self::isResearch()) {
            abort_unless(self::isOpen(), 403, 'Pengisian penelitian belum dibuka atau periodenya telah berakhir.');
            abort_unless(self::eligible($user), 403, 'Persetujuan dan verifikasi peserta penelitian belum lengkap.');
        }
    }

    public static function assertAssessmentWritable(Assessment $assessment): void
    {
        if (self::isResearch()) {
            $user = $assessment->user()->firstOrFail();
            self::assertCanParticipate($user);
            abort_unless($assessment->data_context === 'research'
                && $assessment->study_code === config('research.study_code')
                && $assessment->research_semester === self::participation($user)?->semester
                && ($assessment->research_snapshot['protocol_fingerprint'] ?? null) === self::fingerprint(), 403);
        } else {
            abort_if($assessment->data_context === 'research', 403, 'Data penelitian tidak dapat diubah dalam mode demo.');
        }
    }

    public static function participantResultsHidden(Assessment $assessment): bool
    {
        return ! auth()->user()?->isAdmin() && (self::isResearch() || $assessment->data_context === 'research');
    }

    public static function selectionLabel(): string
    {
        return config('research.selection') === 'last' ? 'Hasil terakhir per mahasiswa' : 'Hasil pertama per mahasiswa';
    }
}
