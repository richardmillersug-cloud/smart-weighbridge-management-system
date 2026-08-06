<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Weight Reader Driver
    |--------------------------------------------------------------------------
    |
    | Determines which implementation of WeightReaderInterface is bound in
    | the service container.
    |
    | Supported: "dummy", "xk3190", "serial"
    |
    | "dummy"  - Simulated weight readings for development and testing.
    | "xk3190" - XK3190-A12 indicator over RS232/USB serial (COM port).
    | "serial" - Same as xk3190 (alias).
    |
    */

    'driver' => env('WEIGHBRIDGE_DRIVER', 'dummy'),

    /*
    |--------------------------------------------------------------------------
    | Serial Connection (XK3190-A12)
    |--------------------------------------------------------------------------
    |
    | Used when WEIGHBRIDGE_DRIVER=serial or xk3190.
    | PHP must run on the same local computer that has the COM port.
    | Station settings override these defaults when a default station exists.
    |
    */

    'serial' => [
        'port' => env('WEIGHBRIDGE_COM_PORT', 'COM1'),
        'baud_rate' => (int) env('WEIGHBRIDGE_BAUD_RATE', 9600),
        'data_bits' => (int) env('WEIGHBRIDGE_DATA_BITS', 8),
        'stop_bits' => (int) env('WEIGHBRIDGE_STOP_BITS', 1),
        'parity' => env('WEIGHBRIDGE_PARITY', 'none'),
        'flow_control' => env('WEIGHBRIDGE_FLOW_CONTROL', 'none'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Weighing Rules
    |--------------------------------------------------------------------------
    */

    'min_weight' => 100,        // minimum acceptable captured weight (kg)
    'max_weight' => 100000,     // indicator ceiling (kg)
    'stability_required' => true,

];
