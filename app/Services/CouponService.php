<?php

namespace App\Services;

use App\Enums\CouponType;
use App\Models\Coupon;
use App\Models\User;

class CouponService
{
    /**
     * Validates a code against the cart subtotal, the categories actually in
     * the cart, and the shopper's own usage history.
     *
     * @throws \RuntimeException with a message safe to show the customer.
     */
    public function validate(string $code, int $subtotal, ?User $user, ?\Illuminate\Support\Collection $cartCategoryIds = null): Coupon
    {
        $coupon = Coupon::whereRaw('LOWER(code) = ?', [mb_strtolower(trim($code))])->first();

        if (! $coupon || ! $coupon->is_active) {
            throw new \RuntimeException('That code is not valid.');
        }

        if (! $coupon->isWithinWindow()) {
            throw new \RuntimeException('That code has expired.');
        }

        if (! $coupon->hasCapacity()) {
            throw new \RuntimeException('That code has been fully claimed.');
        }

        if ($subtotal < $coupon->minimum_purchase) {
            throw new \RuntimeException('Spend at least '.rupiah($coupon->minimum_purchase).' to use this code.');
        }

        if ($user && $coupon->usageCountFor($user) >= $coupon->usage_limit_per_user) {
            throw new \RuntimeException('You have already used this code.');
        }

        if (! $coupon->coversCategories($cartCategoryIds ?? collect())) {
            throw new \RuntimeException("This code only applies to items from {$coupon->category?->name}.");
        }

        return $coupon;
    }

    /** Discount applied to the goods total. Free-shipping codes return 0 here. */
    public function discountFor(Coupon $coupon, int $subtotal): int
    {
        $discount = match ($coupon->type) {
            CouponType::Percentage => (int) round($subtotal * $coupon->value / 100),
            CouponType::Fixed => (int) $coupon->value,
            CouponType::FreeShipping => 0,
        };

        if ($coupon->maximum_discount) {
            $discount = min($discount, $coupon->maximum_discount);
        }

        return min($discount, $subtotal);
    }

    public function waivesShipping(Coupon $coupon): bool
    {
        return $coupon->type === CouponType::FreeShipping;
    }
}
