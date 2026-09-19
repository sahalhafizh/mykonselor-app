<?php

namespace Tests\Unit;

use App\Services\Dass21Service;
use PHPUnit\Framework\TestCase;

class Dass21ServiceTest extends TestCase
{
    protected Dass21Service $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new Dass21Service();
    }

    public function test_cluster_of_mengembalikan_klaster_yang_benar(): void
    {
        $this->assertEquals('stress', $this->service->clusterOf('G01'));
        $this->assertEquals('anxiety', $this->service->clusterOf('G02'));
        $this->assertEquals('depression', $this->service->clusterOf('G03'));
        $this->assertNull($this->service->clusterOf('G99'));
    }

    public function test_setiap_klaster_punya_tepat_7_gejala(): void
    {
        foreach ($this->service->clusterKeys() as $cluster) {
            $this->assertCount(7, $this->service->symptomCodesFor($cluster));
        }
    }

    public function test_score_for_mengalikan_dua(): void
    {
        $answers = collect(['G01' => 1, 'G06' => 1, 'G08' => 1, 'G11' => 1, 'G12' => 1, 'G14' => 1, 'G18' => 1]);
        $this->assertEquals(14, $this->service->scoreFor('stress', $answers));
    }

    public function test_score_for_gejala_tidak_dijawab_dianggap_nol(): void
    {
        $answers = collect(['G01' => 3]);
        $this->assertEquals(6, $this->service->scoreFor('stress', $answers));
    }

    public function test_severity_for_stress_di_titik_batas(): void
    {
        $this->assertEquals('normal', $this->service->severityFor('stress', 14));
        $this->assertEquals('ringan', $this->service->severityFor('stress', 15));
        $this->assertEquals('ringan', $this->service->severityFor('stress', 18));
        $this->assertEquals('sedang', $this->service->severityFor('stress', 19));
        $this->assertEquals('sedang', $this->service->severityFor('stress', 25));
        $this->assertEquals('berat', $this->service->severityFor('stress', 26));
        $this->assertEquals('berat', $this->service->severityFor('stress', 33));
        $this->assertEquals('berat', $this->service->severityFor('stress', 999));
    }

    public function test_severity_for_anxiety_di_titik_batas(): void
    {
        $this->assertEquals('normal', $this->service->severityFor('anxiety', 7));
        $this->assertEquals('ringan', $this->service->severityFor('anxiety', 8));
        $this->assertEquals('ringan', $this->service->severityFor('anxiety', 9));
        $this->assertEquals('sedang', $this->service->severityFor('anxiety', 10));
        $this->assertEquals('sedang', $this->service->severityFor('anxiety', 14));
        $this->assertEquals('berat', $this->service->severityFor('anxiety', 15));
    }

    public function test_severity_for_depression_di_titik_batas(): void
    {
        $this->assertEquals('normal', $this->service->severityFor('depression', 9));
        $this->assertEquals('ringan', $this->service->severityFor('depression', 10));
        $this->assertEquals('ringan', $this->service->severityFor('depression', 13));
        $this->assertEquals('sedang', $this->service->severityFor('depression', 14));
        $this->assertEquals('sedang', $this->service->severityFor('depression', 20));
        $this->assertEquals('berat', $this->service->severityFor('depression', 21));
    }

    public function test_highest_severity_memilih_yang_paling_berat(): void
    {
        $this->assertEquals('berat', $this->service->highestSeverity(['normal', 'ringan', 'berat']));
        $this->assertEquals('sedang', $this->service->highestSeverity(['normal', 'sedang', 'ringan']));
        $this->assertEquals('normal', $this->service->highestSeverity(['normal', 'normal', 'normal']));
    }
}
