<?php

namespace Tests\Unit;

use App\Exports\AssessmentReportExport;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PHPUnit\Framework\TestCase;

class SpreadsheetSafetyTest extends TestCase
{
    public function test_exported_strings_cannot_be_formulas_and_nim_keeps_leading_zeroes(): void
    {
        $sheet = (new Spreadsheet())->getActiveSheet();
        $export = new AssessmentReportExport();
        foreach (['=HYPERLINK("https://example.test", "klik")', '+123', '@SUM(1)', '000000000012'] as $i => $value) {
            $cell = $sheet->getCell('A'.($i + 1));
            $export->bindValue($cell, $value);
            $this->assertSame(DataType::TYPE_STRING, $cell->getDataType());
            $this->assertSame($value, $cell->getValue());
        }
        $cell = $sheet->getCell('B1');
        $export->bindValue($cell, 20);
        $this->assertSame(DataType::TYPE_NUMERIC, $cell->getDataType());
    }
}
