<?php

namespace App\Services\Weighbridge;

use App\Models\WeighbridgeStation;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use RuntimeException;
use Throwable;

/**
 * Reads live weight from a local RS232/USB serial indicator (XK3190-DS17, XK3190-A12 and compatible).
 *
 * Requirements:
 *  - Laravel/PHP must run on the same Windows PC that has the COM port
 *  - Station or .env must set the COM port (e.g. COM3) and baud (usually 9600)
 *
 * XK3190 continuous mode typically sends frames like "=XXXXXX.X" with reversed digits.
 */
class SerialWeightReaderService implements WeightReaderInterface
{
    private const CACHE_KEY = 'weighbridge.serial.reading';

    private const STABILITY_TOLERANCE_KG = 5.0;

    private const STABILITY_SAMPLES = 3;

    public function __construct(
        private readonly string $port,
        private readonly int $baudRate,
        private readonly int $dataBits = 8,
        private readonly string $parity = 'none',
        private readonly int $stopBits = 1,
        private readonly string $flowControl = 'none',
    ) {}

    public static function fromStation(?WeighbridgeStation $station = null): self
    {
        $station ??= WeighbridgeStation::defaultStation();
        $serial = config('weighbridge.serial');

        return new self(
            port: $station?->com_port ?: ($serial['port'] ?? 'COM1'),
            baudRate: (int) ($station?->baud_rate ?: ($serial['baud_rate'] ?? 9600)),
            dataBits: (int) ($station?->data_bits ?: ($serial['data_bits'] ?? 8)),
            parity: (string) ($station?->parity ?: ($serial['parity'] ?? 'none')),
            stopBits: (int) ($station?->stop_bits ?: ($serial['stop_bits'] ?? 1)),
            flowControl: (string) ($station?->flow_control ?: ($serial['flow_control'] ?? 'none')),
        );
    }

    public function getCurrentWeight(): float
    {
        return $this->freshReading()['weight'];
    }

    public function isStable(): bool
    {
        return $this->freshReading()['stable'];
    }

    public function captureWeight(): WeightReading
    {
        $reading = $this->freshReading();

        if ($reading['weight'] <= 0 && ! $reading['connected']) {
            throw new RuntimeException(
                "No live weight from local scale ({$this->connectionSummary()}). Check cable, COM port, and that PHP runs on this PC."
            );
        }

        return new WeightReading(
            weight: $reading['weight'],
            stable: $reading['stable'],
            capturedAt: CarbonImmutable::now(),
            raw: $reading['raw'],
        );
    }

    public function isConnected(): bool
    {
        return $this->freshReading()['connected'];
    }

    public function connectionSummary(): string
    {
        return sprintf(
            '%s %d-%d-%s-%d flow=%s',
            $this->port,
            $this->baudRate,
            $this->dataBits,
            strtoupper(substr($this->parity, 0, 1)),
            $this->stopBits,
            $this->flowControl,
        );
    }

    /**
     * Probe the local COM port without requiring a valid weight frame.
     */
    public function probe(): array
    {
        $raw = $this->readRawBuffer();

        return [
            'connected' => $raw !== null,
            'port' => $this->connectionSummary(),
            'raw' => $raw,
            'weight' => $raw !== null ? $this->parseWeight($raw) : null,
        ];
    }

    /**
     * @return array{weight: float, stable: bool, connected: bool, raw: ?string}
     */
    private function freshReading(): array
    {
        $raw = $this->readRawBuffer();
        $cached = Cache::get(self::CACHE_KEY, [
            'weight' => 0.0,
            'stable' => false,
            'connected' => false,
            'raw' => null,
            'samples' => [],
        ]);

        if ($raw === null) {
            return [
                'weight' => (float) ($cached['weight'] ?? 0),
                'stable' => false,
                'connected' => false,
                'raw' => $cached['raw'] ?? null,
            ];
        }

        if ($raw === '') {
            return [
                'weight' => (float) ($cached['weight'] ?? 0),
                'stable' => (bool) ($cached['stable'] ?? false),
                'connected' => (bool) ($cached['connected'] ?? true),
                'raw' => $cached['raw'] ?? null,
            ];
        }

        $weight = $this->parseWeight($raw);
        if ($weight === null) {
            return [
                'weight' => 0.0,
                'stable' => false,
                'connected' => true,
                'raw' => $raw,
            ];
        }

        $samples = array_values(array_slice([
            ...($cached['samples'] ?? []),
            $weight,
        ], -self::STABILITY_SAMPLES));

        $stable = count($samples) >= self::STABILITY_SAMPLES
            && (max($samples) - min($samples)) <= self::STABILITY_TOLERANCE_KG;

        $reading = [
            'weight' => $weight,
            'stable' => $stable,
            'connected' => true,
            'raw' => $raw,
            'samples' => $samples,
        ];

        Cache::put(self::CACHE_KEY, $reading, now()->addSeconds(10));

        return $reading;
    }

    private function readRawBuffer(): ?string
    {
        $path = $this->devicePath();

        try {
            $this->configureWindowsPort();

            $handle = @fopen($path, 'r+b');
            if ($handle === false) {
                return null;
            }

            stream_set_blocking($handle, false);
            stream_set_timeout($handle, 0, 200000);

            // Allow a short window for continuous indicator frames.
            $buffer = '';
            $deadline = microtime(true) + 0.25;
            while (microtime(true) < $deadline) {
                $chunk = fread($handle, 256);
                if (is_string($chunk) && $chunk !== '') {
                    $buffer .= $chunk;
                }
                usleep(20000);
            }

            fclose($handle);

            $buffer = trim($buffer);

            return $buffer !== '' ? $buffer : '';
        } catch (Throwable) {
            return null;
        }
    }

    private function configureWindowsPort(): void
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            return;
        }

        if (! preg_match('/^COM\d+$/i', $this->port)) {
            return;
        }

        $parity = match (strtolower($this->parity)) {
            'even' => 'E',
            'odd' => 'O',
            'mark' => 'M',
            'space' => 'S',
            default => 'N',
        };

        $cmd = sprintf(
            'mode %s: BAUD=%d PARITY=%s DATA=%d STOP=%d',
            strtoupper($this->port),
            $this->baudRate,
            $parity,
            $this->dataBits,
            $this->stopBits,
        );

        @exec($cmd);
    }

    private function devicePath(): string
    {
        $port = trim($this->port);

        if (preg_match('/^COM\d+$/i', $port)) {
            return '\\\\.\\'.strtoupper($port);
        }

        return $port;
    }

    private function parseWeight(string $raw): ?float
    {
        $max = (float) config('weighbridge.max_weight', 100000);

        $binary = $this->parseContinuousBinaryFrame($raw, $max);
        if ($binary !== null) {
            return $binary;
        }

        if (preg_match('/=([0-9.]+)/', $raw, $leadingMatch)) {
            $weight = $this->decodeWeightToken($leadingMatch[1], $max, preferDirect: true);
            if ($weight !== null) {
                return $weight;
            }
        }

        if (preg_match('/([0-9.]+)=/', $raw, $trailingMatch)) {
            $weight = $this->decodeWeightToken($trailingMatch[1], $max, preferDirect: false);
            if ($weight !== null) {
                return $weight;
            }
        }

        // Common printable frames: "ST,GS,+0016691kg" / "US,NT,  16691 kg"
        if (preg_match('/([+-]?\d+(?:\.\d+)?)\s*kg/i', $raw, $match)) {
            $weight = abs((float) $match[1]);
            if ($weight <= $max) {
                return round($weight, 2);
            }
        }

        if (preg_match('/[,\s]([+-]?\d{4,7})(?:\.(\d+))?(?![0-9])/', $raw, $match)) {
            $weight = abs((float) ($match[1].(isset($match[2]) ? '.'.$match[2] : '')));
            if ($weight <= $max) {
                return round($weight, 2);
            }
        }

        return null;
    }

    /**
     * XK3190-DS17/DS3 continuous mode: STX, sign, 6 digits, decimal places (0-4), checksum, ETX.
     * 20.000 kg arrives as digits 020000 with decimal-place byte 3 — not as 20000 kg.
     */
    private function parseContinuousBinaryFrame(string $raw, float $max): ?float
    {
        if (! preg_match_all('/\x02([+-])(\d{6})(.)/s', $raw, $matches, PREG_SET_ORDER)) {
            return null;
        }

        $match = $matches[array_key_last($matches)];
        $places = $this->decimalPlacesFromByte($match[3]);
        if ($places === null) {
            return null;
        }

        $weight = abs((float) $match[2]) / (10 ** $places);
        if ($weight <= $max) {
            return round($weight, 2);
        }

        return null;
    }

    private function decimalPlacesFromByte(string $byte): ?int
    {
        if ($byte === '') {
            return null;
        }

        $ord = ord($byte);
        if ($ord >= 0 && $ord <= 4) {
            return $ord;
        }

        if ($byte >= '0' && $byte <= '4') {
            return (int) $byte;
        }

        return null;
    }

    private function decodeWeightToken(string $token, float $max, ?bool $preferDirect = null): ?float
    {
        $token = trim($token);
        if ($token === '') {
            return null;
        }

        $direct = abs((float) $token);
        $reversed = abs((float) strrev($token));

        // 6-digit LSB-first: indicator 20 kg is sent as 020000, which is 20000 if read forwards.
        if (! str_contains($token, '.') && ctype_digit($token) && strlen($token) === 6) {
            $directInt = (int) $token;
            $reversedInt = (int) strrev($token);
            if ($reversedInt > 0 && $reversedInt <= $max && $directInt === $reversedInt * 1000) {
                return round((float) $reversedInt, 2);
            }
        }

        $candidates = match ($preferDirect) {
            true => [$direct, $reversed],
            false => [$reversed, $direct],
            default => str_contains($token, '.') ? [$reversed, $direct] : [$direct, $reversed],
        };

        $places = (int) config('weighbridge.decimal_places', 0);

        foreach ($candidates as $candidate) {
            if ($places > 0 && ! str_contains($token, '.')) {
                $candidate /= (10 ** $places);
            }

            if ($candidate >= 0 && $candidate <= $max) {
                return round($candidate, 2);
            }
        }

        return null;
    }
}
