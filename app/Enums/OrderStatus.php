<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Processing = 'processing';
    case Packed = 'packed';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    /** Customer-facing progress timeline, in order. */
    public static function timeline(): array
    {
        return [
            self::Pending,
            self::Paid,
            self::Processing,
            self::Packed,
            self::Shipped,
            self::Delivered,
        ];
    }

    public function timelineIndex(): int
    {
        return array_search($this, self::timeline(), true) ?: 0;
    }

    /** Statuses the order may legally move to next. */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Pending => [self::Paid, self::Cancelled],
            self::Paid => [self::Processing, self::Refunded, self::Cancelled],
            self::Processing => [self::Packed, self::Cancelled],
            self::Packed => [self::Shipped, self::Cancelled],
            self::Shipped => [self::Delivered],
            self::Delivered => [self::Refunded],
            self::Cancelled, self::Refunded => [],
        };
    }

    public function isFinished(): bool
    {
        return in_array($this, [self::Delivered, self::Cancelled, self::Refunded], true);
    }

    /** Tailwind classes used by the status badge component. */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::Pending => 'bg-sand-200 text-ink-600',
            self::Paid => 'bg-clay-400/40 text-ink-900',
            self::Processing, self::Packed => 'bg-clay-400 text-ink-900',
            self::Shipped => 'bg-ink-600 text-white',
            self::Delivered => 'bg-ink-900 text-white',
            self::Cancelled => 'bg-red-100 text-red-800',
            self::Refunded => 'bg-amber-100 text-amber-800',
        };
    }
}
