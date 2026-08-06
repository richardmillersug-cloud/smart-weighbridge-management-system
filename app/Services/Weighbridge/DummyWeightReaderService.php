<?php

namespace App\Services\Weighbridge;

use Carbon\CarbonImmutable;

/**
 * Simulated weight reader used until the XK3190-A12 hardware integration
 * is delivered.
 *
 * The simulation runs a repeating "weighing cycle": a truck rolls onto the
 * deck (weight ramps up with noise, unstable), settles (stable reading with
 * minimal jitter), then departs and the next cycle starts with a new random
 * target weight. The cycle is derived deterministically from the clock, so
 * repeated polling from the UI observes a consistent, realistic sequence.
 */
class DummyWeightReaderService implements WeightReaderInterface
{
    private const CYCLE_SECONDS = 30;

    private const RAMP_SECONDS = 8;

    public function getCurrentWeight(): float
    {
        return $this->reading()['weight'];
    }

    public function isStable(): bool
    {
        return $this->reading()['stable'];
    }

    public function captureWeight(): WeightReading
    {
        $reading = $this->reading();

        return new WeightReading(
            weight: $reading['weight'],
            stable: $reading['stable'],
            capturedAt: CarbonImmutable::now(),
            raw: sprintf('SIM,%s,%09.2f kg', $reading['stable'] ? 'ST' : 'US', $reading['weight']),
        );
    }

    public function isConnected(): bool
    {
        return true;
    }

    /**
     * @return array{weight: float, stable: bool}
     */
    private function reading(): array
    {
        $now = CarbonImmutable::now()->getTimestamp();
        $cycle = intdiv($now, self::CYCLE_SECONDS);
        $elapsed = $now % self::CYCLE_SECONDS;

        // Deterministic pseudo-random target per cycle: 8t .. 45t.
        mt_srand($cycle);
        $target = mt_rand(8000, 45000);
        mt_srand();

        if ($elapsed < self::RAMP_SECONDS) {
            // Truck driving onto the deck - noisy ramp, unstable.
            $progress = $elapsed / self::RAMP_SECONDS;
            $noise = mt_rand(-400, 400);
            $weight = max(0, ($target * $progress) + $noise);

            return ['weight' => round($weight, 2), 'stable' => false];
        }

        // Settled on the deck - stable with millimetric jitter.
        $jitter = mt_rand(-5, 5);

        return ['weight' => round($target + $jitter, 2), 'stable' => true];
    }
}
