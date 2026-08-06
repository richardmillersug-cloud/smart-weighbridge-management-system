<?php

namespace App\Services\Weighbridge;

/**
 * XK3190-A12 indicator over RS232/USB serial (COM port).
 *
 * Thin alias of SerialWeightReaderService for the A12 continuous frame format
 * (=XXXXXX.X with reversed digits). Prefer binding via WEIGHBRIDGE_DRIVER=xk3190.
 */
class XK3190RS232ReaderService extends SerialWeightReaderService
{
}
