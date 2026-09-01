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

    public function test_implied_decimal_places_scale_integer_frames(): void
    {
        config(['weighbridge.decimal_places' => 3]);

        $reader = new SerialWeightReaderService('COM1', 9600);
        $method = new ReflectionMethod(SerialWeightReaderService::class, 'parseWeight');
        $method->setAccessible(true);

        $this->assertSame(20.0, $method->invoke($reader, '=00020000'));
    }

    public static function weightFrameProvider(): array
    {
        return [
            'ds17 empty scale' => ['=0000000', 0.0],
            'ds17 fixed width kg' => ['=0001234', 1234.0],
            'ds17 reversed fixed width kg' => ['=0412100', 12140.0],
            'ds17 20kg display order' => ['=0000020', 20.0],
            'ds17 20t truck stays kilograms' => ['=0020000', 20000.0],
            'a12 reversed 20kg leading equals' => ['=020000', 20.0],
            'a12 reversed 20kg trailing equals' => ['020000=', 20.0],
            'd2 reversed 20kg integer' => ['0200000=', 20.0],
            'd2 reversed net weight' => ['51.0700=', 70.15],
            'd2 prefixed equals frame' => ['=51.0700', 51.07],
            'kg suffix frame' => ['ST,GS,+0016691kg', 16691.0],
            '12-byte 20.000kg with decimal byte' => ["\x02+0200003\x00\x00\x03", 20.0],
            '12-byte 20t with zero decimal places' => ["\x02+020000\x00\x00\x00\x03", 20000.0],
        ];
    }
}
