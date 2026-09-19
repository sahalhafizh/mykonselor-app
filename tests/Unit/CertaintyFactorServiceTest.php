<?php

namespace Tests\Unit;

use App\Services\CertaintyFactorService;
use PHPUnit\Framework\TestCase;

class CertaintyFactorServiceTest extends TestCase
{
    protected CertaintyFactorService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CertaintyFactorService;
    }

    public function test_calculate_expert_cf_dengan_mb_1_md_0(): void
    {
        $this->assertEquals(1.0, $this->service->calculateExpertCf(1, 0));
    }

    public function test_calculate_expert_cf_dengan_mb_0_md_1(): void
    {
        $this->assertEquals(-1.0, $this->service->calculateExpertCf(0, 1));
    }

    public function test_calculate_symptom_cf(): void
    {
        $this->assertEquals(0.8, $this->service->calculateSymptomCf(1.0, 0.8));
        $this->assertEquals(-0.4, $this->service->calculateSymptomCf(-1.0, 0.4));
        $this->assertEquals(0.0, $this->service->calculateSymptomCf(1.0, 0.0));
    }

    public function test_combine_positive(): void
    {
        $this->assertEqualsWithDelta(0.88, $this->service->combinePositive(0.8, 0.4), 0.0001);
    }

    public function test_combine_negative(): void
    {
        $this->assertEqualsWithDelta(-0.88, $this->service->combineNegative(-0.8, -0.4), 0.0001);
    }

    public function test_combine_mixed(): void
    {
        $this->assertEqualsWithDelta(0.6667, $this->service->combineMixed(0.8, -0.4), 0.001);
    }

    public function test_combine_memilih_rumus_secara_otomatis(): void
    {
        $this->assertEqualsWithDelta(0.88, $this->service->combine(0.8, 0.4), 0.0001);
        $this->assertEqualsWithDelta(-0.88, $this->service->combine(-0.8, -0.4), 0.0001);
        $this->assertEqualsWithDelta(0.6667, $this->service->combine(0.8, -0.4), 0.001);
    }

    public function test_combine_positive_tidak_melebihi_batas_atas(): void
    {
        $result = $this->service->combine(1.0, 1.0);
        $this->assertLessThanOrEqual(1.0, $result);
    }

    public function test_combine_mixed_menangani_pembagi_nol(): void
    {
        $result = $this->service->combineMixed(1.0, -1.0);
        $this->assertEquals(0.0, $result);
    }
}
