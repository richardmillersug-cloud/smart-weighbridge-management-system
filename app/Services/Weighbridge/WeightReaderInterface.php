<?php

namespace App\Services\Weighbridge;

/**
 * Contract for every weighbridge indicator driver.
 *
 * Current implementations:
 *  - DummyWeightReaderService     (simulated readings, development)
 *  - XK3190RS232ReaderService     (XK3190-A12 over RS232/USB serial, future)
 */
interface WeightReaderInterface
{
    /**
     * The live weight currently shown on the indicator, in kilograms.
     */
    public function getCurrentWeight(): float;

    /**
     * Whether the indicator reports a stable (settled) weight.
     */
    public function isStable(): bool;

    /**
     * Take an authoritative reading for persisting on a ticket.
     */
    public function captureWeight(): WeightReading;

    /**
     * Whether the indicator link is currently available.
     */
    public function isConnected(): bool;
}
