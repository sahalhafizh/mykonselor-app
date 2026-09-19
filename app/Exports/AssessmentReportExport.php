<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

class AssessmentReportExport extends DefaultValueBinder implements FromCollection, WithCustomValueBinder, WithHeadings, WithMapping, WithTitle
{
    public function __construct(
        protected ?Collection $rows = null,
        protected bool $anonymized = false,
    ) {}

    public function title(): string
    {
        return 'Data Responden';
    }

    public static function respondentCode($userId): string
    {
        return 'R-'.substr(hash_hmac('sha256', (string) $userId, (string) config('app.key')), 0, 16);
    }

    public function bindValue(Cell $cell, mixed $value): bool
    {
        if (is_string($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function collection()
    {
        return $this->rows ?? collect();
    }

    public function headings(): array
    {
        return [
            $this->anonymized ? 'Kode Responden' : 'NIM', 'Nama', 'Fakultas', 'Tanggal Skrining',
            'Stres (Skor)', 'Stres (Tingkat)', 'Stres (CF %)',
            'Kecemasan (Skor)', 'Kecemasan (Tingkat)', 'Kecemasan (CF %)',
            'Depresi (Skor)', 'Depresi (Tingkat)', 'Depresi (CF %)',
            'Tingkat Keparahan Tertinggi', 'Semester Saat Pengisian', 'Konteks Data', 'Kode Penelitian',
        ];
    }

    public function map($assessment): array
    {
        $r = $assessment->result;

        return [
            $this->anonymized ? self::respondentCode($assessment->user_id) : ($assessment->user?->nim ?? '-'), $this->anonymized ? 'Dirahasiakan' : ($assessment->user?->name ?? 'Akun tidak tersedia'), $assessment->user?->fakultas ?? '-',
            optional($assessment->completed_at?->copy()->timezone(config('app.display_timezone')))->format('d/m/Y H:i'),
            $r?->stress_score ?? '-', $r ? ucfirst($r->stress_severity) : '-', $r ? round($r->stress_cf * 100, 1) : '-',
            $r?->anxiety_score ?? '-', $r ? ucfirst($r->anxiety_severity) : '-', $r ? round($r->anxiety_cf * 100, 1) : '-',
            $r?->depression_score ?? '-', $r ? ucfirst($r->depression_severity) : '-', $r ? round($r->depression_cf * 100, 1) : '-',
            $r ? ucfirst($r->highest_severity) : '-',
            $assessment->research_semester ?? '-', $assessment->data_context, $assessment->study_code ?? '-',
        ];
    }
}
