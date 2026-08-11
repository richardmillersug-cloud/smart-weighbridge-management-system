<?php

namespace Tests\Unit;

use App\Services\Weighbridge\SerialWeightReaderService;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionMethod;
use Tests\TestCase;

class SerialWeightParserTest extends TestCase
{
    #[DataProvider('weightFrameProvider')]
    public function test_parse_weight_frames(string $raw, ?float $expected): void
    {
        $reader = new SerialWeightReaderService('COM1', 9600);
        $method = new ReflectionMethod(SerialWeightReaderService::class, 'parseWeight');
        $method->setAccessible(true);

        $this->assertSame($expected, $method->invoke($reader, $raw));
    }

    public static function weightFrameProvider(): array
    {
        return [
            'ds17 empty scale' => ['=0000000', 0.0],
            'ds17 fixed width kg' => ['=0001234', 1234.0],
            'ds17 reversed fixed width kg' => ['=0412100', 12140.0],
            'd2 reversed net weight' => ['51.0700=', 70.15],
            'd2 prefixed equals frame' => ['=51.0700', 51.07],
            'kg suffix frame' => ['ST,GS,+0016691kg', 16691.0],
        ];
    }
}
