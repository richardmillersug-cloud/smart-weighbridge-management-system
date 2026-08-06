<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'CASH';
    case MobileMoney = 'MOBILE_MONEY';
    case BankTransfer = 'BANK_TRANSFER';
    case Cheque = 'CHEQUE';
    case Card = 'CARD';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Cash',
            self::MobileMoney => 'Mobile Money',
            self::BankTransfer => 'Bank Transfer',
            self::Cheque => 'Cheque',
            self::Card => 'Card',
        };
    }
}
