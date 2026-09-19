<?php

namespace App\Exports;

class ReportSummaryExport extends AssessmentReportExport
{
    public function __construct(private array $metadata, private array $summary)
    {
        parent::__construct();
    }

    public function title(): string
    {
        return 'Ringkasan dan Metode';
    }

    public function headings(): array
    {
        return ['Keterangan', 'Nilai', 'Persentase responden'];
    }

    public function collection()
    {
        $rows = collect([
            ['Mode', $this->metadata['mode'], ''], ['Kode penelitian', $this->metadata['study_code'], ''],
            ['Mulai laporan (WIB)', $this->metadata['from'] ?? 'Semua', ''], ['Akhir laporan (WIB)', $this->metadata['to'] ?? 'Semua', ''],
            ['Aturan pemilihan', $this->metadata['selection_label'].' dalam rentang laporan; waktu sama dipilih berdasarkan ID.', ''],
            ['Responden unik', $this->summary['respondents'], ''], ['Pengisian selesai yang memenuhi syarat', $this->summary['responses'], ''],
            ['Batas interpretasi', $this->metadata['scope'], ''],
            ['CF', 'Tingkat keyakinan sistem; bukan persentase mahasiswa atau probabilitas diagnosis.', ''],
            ['Periode penelitian (WIB)', ($this->metadata['study_starts_on'] ?? 'Demo').' — '.($this->metadata['study_ends_on'] ?? 'Demo'), ''],
        ]);
        foreach (['stress' => 'Stres', 'anxiety' => 'Kecemasan', 'depression' => 'Depresi'] as $key => $label) {
            foreach (['normal', 'ringan', 'sedang', 'berat'] as $severity) {
                $count = (int) ($this->summary['distribution'][$key][$severity] ?? 0);
                $rows->push([$label.' — '.ucfirst($severity), $count,
                    $this->summary['respondents'] ? round($count / $this->summary['respondents'] * 100, 2) : 0]);
            }
        }
        foreach ([7, 8] as $semester) {
            $rows->push(['Semester '.$semester, (int) ($this->summary['semesters'][$semester] ?? 0), '']);
        }

        return $rows;
    }

    public function map($row): array
    {
        return $row;
    }
}
