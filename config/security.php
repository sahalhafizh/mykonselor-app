<?php

return [
    'require_admin_mfa' => (bool) env('ADMIN_MFA_REQUIRED', env('APP_ENV') === 'production'),
    'require_student_verification' => (bool) env('STUDENT_VERIFICATION_REQUIRED', env('APP_ENV') === 'production'),
    'export_max_rows' => (int) env('EXPORT_MAX_ROWS', 1000),
    'export_max_days' => (int) env('EXPORT_MAX_DAYS', 366),
    'trusted_proxies' => array_values(array_filter(array_map('trim', explode(',', (string) env('TRUSTED_PROXIES', ''))))),
];
