<?php

namespace App\Services;

/**
 * Pure weight / amount calculations for WOMS tickets.
 */
class WeightCalculator
{
    /**
     * @return array{net_weight: float, deduction_weight: float, actual_weight: float, total_amount: float|null}
     */
    public function calculate(
        ?float $gross,
        ?float $tare,
        float $deductionPercentage = 0.0,
        ?float $unitPrice = null,
    ): array {
        if ($gross === null || $tare === null) {
            return [
                'net_weight' => 0.0,
                'deduction_weight' => 0.0,
                'actual_weight' => 0.0,
                'total_amount' => null,
            ];
        }

        $net = round(max(0, $gross - $tare), 2);
        $deductionWeight = round($net * ($deductionPercentage / 100), 2);
        $actual = round(max(0, $net - $deductionWeight), 2);
        $total = $unitPrice !== null
            ? round(($actual / 1000) * $unitPrice, 2)
            : null;

        return [
            'net_weight' => $net,
            'deduction_weight' => $deductionWeight,
            'actual_weight' => $actual,
            'total_amount' => $total,
        ];
    }

    /**
     * Simple mode: largest = gross, smallest = tare.
     *
     * @return array{gross: float, tare: float}
     */
    public function assignSimpleWeights(float $weightOne, float $weightTwo): array
    {
        return [
            'gross' => max($weightOne, $weightTwo),
            'tare' => min($weightOne, $weightTwo),
        ];
    }
}
