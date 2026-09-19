<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ResearchReportWorkbook implements WithMultipleSheets
{
    public function __construct(private Collection $rows, private bool $anonymized, private array $metadata, private array $summary) {}

    public function sheets(): array
    {
        return [new AssessmentReportExport($this->rows, $this->anonymized), new ReportSummaryExport($this->metadata, $this->summary)];
    }
}
