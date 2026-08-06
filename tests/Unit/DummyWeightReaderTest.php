<?php

namespace Tests\Unit;

use App\Services\Weighbridge\DummyWeightReaderService;
use PHPUnit\Framework\TestCase;

class DummyWeightReaderTest extends TestCase
{
    public function test_reader_reports_connected(): void
    {
        $this->assertTrue((new DummyWeightReaderService)->isConnected());
    }

    public function test_reading_is_within_indicator_range(): void
    {
        $reader = new DummyWeightReaderService;

        $weight = $reader->getCurrentWeight();

        $this->assertGreaterThanOrEqual(0, $weight);
        $this->assertLessThanOrEqual(50000, $weight);
    }

    public function test_capture_returns_a_reading_value_object(): void
    {
        $reading = (new DummyWeightReaderService)->captureWeight();

        $this->assertSame('kg', $reading->unit);
        $this->assertNotNull($reading->raw);
        $this->assertIsFloat($reading->weight);
    }
}
