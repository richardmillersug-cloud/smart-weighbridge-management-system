<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\DB;

/**
 * Generates sequential, date-scoped document numbers,
 * e.g. WB-20260804-0001, INV-20260804-0001, RCP-20260804-0001.
 *
 * Prefixes are configurable from system settings (invoice numbering).
 */
class ReferenceGenerator
{
    public static function ticketNumber(): string
    {
        return static::next(Setting::get('ticket_prefix', 'WB'), 'weighbridge_tickets', 'ticket_number');
    }

    public static function invoiceNumber(): string
    {
        return static::next(Setting::get('invoice_prefix', 'INV'), 'weight_invoices', 'invoice_number');
    }

    public static function receiptNumber(): string
    {
        return static::next(Setting::get('receipt_prefix', 'RCP'), 'payments', 'receipt_number');
    }

    protected static function next(string $prefix, string $table, string $column): string
    {
        $base = sprintf('%s-%s-', $prefix, now()->format('Ymd'));

        $last = DB::table($table)
            ->where($column, 'like', $base.'%')
            ->lockForUpdate()
            ->orderByDesc($column)
            ->value($column);

        $sequence = $last !== null
            ? ((int) substr((string) $last, strlen($base))) + 1
            : 1;

        return $base.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}
