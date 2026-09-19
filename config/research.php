<?php

return [
    // Demo is permitted only on local/testing. Every other environment is research.
    'mode' => env('MYKONSELOR_MODE', env('APP_ENV') === 'production' ? 'research' : 'demo'),
    'study_code' => env('RESEARCH_STUDY_CODE', ''),
    'starts_on' => env('RESEARCH_STARTS_ON', ''),
    'ends_on' => env('RESEARCH_ENDS_ON', ''),
    'selection' => env('RESEARCH_RESULT_SELECTION', 'first'),
    'approved' => (bool) env('RESEARCH_PROTOCOL_APPROVED', false),
    'consent_version' => '2026-09-18-research-v1',
    'university' => 'Universitas Pamulang',
    'program' => 'Teknik Informatika',
    'semesters' => [7, 8],
];
