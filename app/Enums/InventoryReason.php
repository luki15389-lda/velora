<?php

namespace App\Enums;

enum InventoryReason: string
{
    case Purchase = 'purchase';       /* stock in from supplier */
    case Sale = 'sale';               /* checkout */
    case Adjustment = 'adjustment';   /* manual correction */
    case Return_ = 'return';
    case Cancellation = 'cancellation';
    case Seed = 'seed';

    public function label(): string
    {
        return match ($this) {
            self::Purchase => 'Stock in',
            self::Sale => 'Sold',
            self::Adjustment => 'Manual adjustment',
            self::Return_ => 'Returned',
            self::Cancellation => 'Order cancelled',
            self::Seed => 'Opening balance',
        };
    }
}
