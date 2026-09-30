<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case BankTransfer = 'bank_transfer';
    case Qris = 'qris';
    case EWallet = 'e_wallet';
    case Cod = 'cod';

    public function label(): string
    {
        return match ($this) {
            self::BankTransfer => 'Bank transfer',
            self::Qris => 'QRIS',
            self::EWallet => 'E-wallet',
            self::Cod => 'Cash on delivery',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::BankTransfer => 'Transfer to a VELORA virtual account. We confirm within minutes.',
            self::Qris => 'Scan one code with any Indonesian banking or wallet app.',
            self::EWallet => 'Pay with GoPay, OVO, DANA or ShopeePay.',
            self::Cod => 'Pay the courier when the parcel arrives.',
        };
    }
}
