<?php

namespace App\Enums;

enum ShippingMethod: string
{
    case Standard = 'standard';
    case Express = 'express';
    case SameDay = 'same_day';

    public function label(): string
    {
        return match ($this) {
            self::Standard => 'Standard',
            self::Express => 'Express',
            self::SameDay => 'Same day',
        };
    }

    public function cost(): int
    {
        return match ($this) {
            self::Standard => 20_000,
            self::Express => 35_000,
            self::SameDay => 50_000,
        };
    }

    public function estimate(): string
    {
        return match ($this) {
            self::Standard => '3–5 business days',
            self::Express => '1–2 business days',
            self::SameDay => 'Arrives today, Jakarta area only',
        };
    }
}
