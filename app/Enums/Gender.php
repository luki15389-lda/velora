<?php

namespace App\Enums;

enum Gender: string
{
    case Men = 'men';
    case Women = 'women';
    case Unisex = 'unisex';

    public function label(): string
    {
        return match ($this) {
            self::Men => "Men's",
            self::Women => "Women's",
            self::Unisex => 'Unisex',
        };
    }
}
