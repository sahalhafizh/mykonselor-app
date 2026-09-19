<?php

return [
    'version' => '2026-09-18-research',
    'updated_at' => '18 September 2026',
    'operator' => env('PRIVACY_OPERATOR'),
    'contact_email' => env('PRIVACY_CONTACT_EMAIL'),
    'retention_days' => (int) env('DATA_RETENTION_DAYS', 0),
    'backup_retention_days' => (int) env('BACKUP_RETENTION_DAYS', 0),
    'approved' => (bool) env('PRIVACY_POLICY_APPROVED', false),
];
