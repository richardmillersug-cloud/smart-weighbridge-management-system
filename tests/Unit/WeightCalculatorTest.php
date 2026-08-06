<?php

namespace Tests\Unit;

use App\Services\WeightCalculator;
use PHPUnit\Framework\TestCase;

class WeightCalculatorTest extends TestCase
{
    public function test_calculates_net_deduction_actual_and_total(): void
    {
        $calc = new WeightCalculator();
        $result = $calc->calculate(20000, 8000, 10, 5000);

        $this->assertSame(12000.0, $result['net_weight']);
        $this->assertSame(1200.0, $result['deduction_weight']);
        $this->assertSame(10800.0, $result['actual_weight']);
        $this->assertSame(54000.0, $result['total_amount']);
    }

    public function test_simple_mode_assigns_max_gross_min_tare(): void
    {
        $calc = new WeightCalculator();
        $assigned = $calc->assignSimpleWeights(9000, 22000);

        $this->assertSame(22000.0, $assigned['gross']);
        $this->assertSame(9000.0, $assigned['tare']);
    }
}
