<?php

namespace App\Enums;

enum Role: string
{
    case Admin = 'admin';
    case Customer = 'customer';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrator',
            self::Customer => 'Customer',
        };
    }

    /** Roles allowed into the /admin area. Admin-only, by design — see README. */
    public static function backoffice(): array
    {
        return [self::Admin->value];
    }
}
