<?php

namespace App\Services\Weighbridge;

use Carbon\CarbonImmutable;

/**
 * Immutable value object representing a single reading from the
 * weighbridge indicator.
 */
final readonly class WeightReading
{
    public function __construct(
        public float $weight,
        public bool $stable,
        public CarbonImmutable $capturedAt,
        public string $unit = 'kg',
        public ?string $raw = null,
    ) {}

    public function toArray(): array
    {
        return [
            'weight' => $this->weight,
            'stable' => $this->stable,
            'unit' => $this->unit,
            'captured_at' => $this->capturedAt->toIso8601String(),
            'raw' => $this->raw,
        ];
    }
}
