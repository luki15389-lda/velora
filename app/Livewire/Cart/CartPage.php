<?php

namespace App\Livewire\Cart;

use App\Enums\ShippingMethod;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\CouponService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class CartPage extends Component
{
    public string $couponCode = '';

    public ?string $couponError = null;

    #[Computed]
    public function cart()
    {
        return app(CartService::class)->current()->load([
            'items.variant.product.primaryImage',
            'items.variant.color',
            'items.variant.size',
            'coupon',
        ]);
    }

    #[Computed]
    public function subtotal(): int
    {
        return (int) $this->cart()->items->sum(fn (CartItem $item) => $item->line_total);
    }

    #[Computed]
    public function totals(): array
    {
        return app(CheckoutService::class)->totals(
            $this->subtotal(),
            ShippingMethod::Standard,
            $this->cart()->coupon,
        );
    }

    public function increment(int $itemId, CartService $carts): void
    {
        $this->changeQuantity($itemId, 1, $carts);
    }

    public function decrement(int $itemId, CartService $carts): void
    {
        $this->changeQuantity($itemId, -1, $carts);
    }

    public function remove(int $itemId): void
    {
        $item = $this->findItem($itemId);

        if (! $item) {
            return;
        }

        $name = $item->variant->product->name;
        $item->delete();

        unset($this->cart, $this->subtotal, $this->totals);

        $this->dispatch('cart-updated');
        $this->dispatch('notify', message: "{$name} removed.", tone: 'info');
    }

    public function applyCoupon(CouponService $coupons): void
    {
        $this->couponError = null;

        $cartCategoryIds = $this->cart()->items->pluck('variant.product.category_id')->filter()->unique()->values();

        try {
            $coupon = $coupons->validate($this->couponCode, $this->subtotal(), auth()->user(), $cartCategoryIds);
        } catch (\RuntimeException $e) {
            $this->couponError = $e->getMessage();

            return;
        }

        $this->cart()->forceFill(['coupon_id' => $coupon->id])->save();

        unset($this->cart, $this->totals);

        $this->couponCode = '';
        $this->dispatch('notify', message: "Code {$coupon->code} applied.", tone: 'success');
    }

    public function removeCoupon(): void
    {
        $this->cart()->forceFill(['coupon_id' => null])->save();

        unset($this->cart, $this->totals);

        $this->dispatch('notify', message: 'Code removed.', tone: 'info');
    }

    public function render(): View
    {
        return view('livewire.cart.cart-page')->title('Your bag');
    }

    private function changeQuantity(int $itemId, int $delta, CartService $carts): void
    {
        $item = $this->findItem($itemId);

        if (! $item) {
            return;
        }

        try {
            $carts->updateQuantity($item, $item->quantity + $delta);
        } catch (\RuntimeException $e) {
            $this->dispatch('notify', message: $e->getMessage(), tone: 'error');

            return;
        }

        unset($this->cart, $this->subtotal, $this->totals);

        $this->dispatch('cart-updated');
    }

    /** Scoped to the current cart so an id from another session cannot be touched. */
    private function findItem(int $itemId): ?CartItem
    {
        return $this->cart()->items->firstWhere('id', $itemId);
    }
}
