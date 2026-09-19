<?php

namespace App\Support;

class PrivacyPolicy
{
    public static function isReady(): bool
    {
        $email = (string) config('privacy.contact_email');

        return config('privacy.approved') === true
            && mb_strlen(trim((string) config('privacy.operator'))) >= 3
            && filter_var($email, FILTER_VALIDATE_EMAIL) !== false
            && ! preg_match('/(?:example\.(?:com|org|net)|\.(?:test|invalid)|localhost)$/i', $email)
            && config('privacy.retention_days') > 0
            && config('privacy.backup_retention_days') > 0;
    }
}
