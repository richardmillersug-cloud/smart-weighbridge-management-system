<?php

use App\Models\Setting;

if (! function_exists('setting')) {
    function setting(string $key, ?string $default = null): ?string
    {
        return Setting::get($key, $default);
    }
}

if (! function_exists('money')) {
    /**
     * Format an amount with the configured currency, e.g. "TZS 1,250.00".
     */
    function money(float|int|string|null $amount): string
    {
        return trim(setting('currency', 'USD').' '.number_format((float) $amount, 2));
    }
}

if (! function_exists('kg')) {
    /**
     * Format a weight in kilograms, e.g. "12,500.00 kg".
     */
    function kg(float|int|string|null $weight, int $decimals = 2): string
    {
        return number_format((float) $weight, $decimals).' kg';
    }
}
