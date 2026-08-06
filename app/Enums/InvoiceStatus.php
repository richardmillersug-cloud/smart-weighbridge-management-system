<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case Pending = 'PENDING';
    case Paid = 'PAID';
    case Cancelled = 'CANCELLED';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Paid => 'Paid',
            self::Cancelled => 'Cancelled',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Pending => 'bg-amber-500/10 text-amber-400 ring-amber-500/30',
            self::Paid => 'bg-emerald-500/10 text-emerald-400 ring-emerald-500/30',
            self::Cancelled => 'bg-red-500/10 text-red-400 ring-red-500/30',
        };
    }
}
