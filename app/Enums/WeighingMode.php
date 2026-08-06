<?php

namespace App\Enums;

enum WeighingMode: string
{
    case Standard = 'standard';
    case Simple = 'simple';
    case NetWeight = 'net_weight';

    public function label(): string
    {
        return match ($this) {
            self::Standard => 'Standard',
            self::Simple => 'Simple',
            self::NetWeight => 'Net Weight',
        };
    }

    /**
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
