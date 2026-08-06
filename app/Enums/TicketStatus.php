<?php

namespace App\Enums;

enum TicketStatus: string
{
    case Created = 'CREATED';
    case AwaitingGross = 'AWAITING_GROSS';
    case AwaitingTare = 'AWAITING_TARE';
    case Completed = 'COMPLETED';
    case Invoiced = 'INVOICED';
    case Paid = 'PAID';
    case Closed = 'CLOSED';
    case Cancelled = 'CANCELLED';

    public function label(): string
    {
        return match ($this) {
            self::Created => 'Created',
            self::AwaitingGross => 'Awaiting Gross',
            self::AwaitingTare => 'Awaiting Tare',
            self::Completed => 'Completed',
            self::Invoiced => 'Invoiced',
            self::Paid => 'Paid',
            self::Closed => 'Closed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Created => 'bg-sky-500/10 text-sky-400 ring-sky-500/30',
            self::AwaitingGross => 'bg-amber-500/10 text-amber-400 ring-amber-500/30',
            self::AwaitingTare => 'bg-orange-500/10 text-orange-400 ring-orange-500/30',
            self::Completed => 'bg-emerald-500/10 text-emerald-400 ring-emerald-500/30',
            self::Invoiced => 'bg-indigo-500/10 text-indigo-400 ring-indigo-500/30',
            self::Paid => 'bg-green-500/10 text-green-400 ring-green-500/30',
            self::Closed => 'bg-slate-500/10 text-slate-300 ring-slate-500/30',
            self::Cancelled => 'bg-red-500/10 text-red-400 ring-red-500/30',
        };
    }

    public function canCaptureGross(): bool
    {
        return in_array($this, [self::Created, self::AwaitingGross], true);
    }

    public function canCaptureTare(): bool
    {
        return in_array($this, [self::Created, self::AwaitingTare], true);
    }

    public function canBeInvoiced(): bool
    {
        return $this === self::Completed;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this, [self::Created, self::AwaitingGross, self::AwaitingTare], true);
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::Closed, self::Cancelled], true);
    }

    /**
     * @return array<string>
     */
    public static function openStatuses(): array
    {
        return [
            self::Created->value,
            self::AwaitingGross->value,
            self::AwaitingTare->value,
        ];
    }

    /**
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
