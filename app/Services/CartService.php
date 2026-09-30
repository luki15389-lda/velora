<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CartService
{
    /**
     * Returns the cart for whoever is browsing. Signed-in shoppers get the cart
     * bound to their user id; guests get one keyed by a token kept in the session.
     */
    public function current(): Cart
    {
        if ($user = auth()->user()) {
            return Cart::firstOrCreate(['user_id' => $user->id]);
        }

        $token = session('cart_token');

        if (! $token) {
            $token = (string) Str::uuid();
            session(['cart_token' => $token]);
        }

        return Cart::firstOrCreate(['session_token' => $token], ['user_id' => null]);
    }

    public function itemCount(): int
    {
        return (int) CartItem::whereBelongsTo($this->current())->sum('quantity');
    }

    /**
     * @throws \RuntimeException when the requested quantity is not on the shelf.
     */
    public function add(ProductVariant $variant, int $quantity = 1): CartItem
    {
        $cart = $this->current();

        return DB::transaction(function () use ($cart, $variant, $quantity) {
            $item = CartItem::firstOrNew([
                'cart_id' => $cart->id,
                'product_variant_id' => $variant->id,
            ]);

            $requested = ($item->quantity ?? 0) + $quantity;

            $this->guardStock($variant, $requested);

            $item->quantity = $requested;
            $item->save();

            return $item;
        });
    }

    public function updateQuantity(CartItem $item, int $quantity): void
    {
        if ($quantity < 1) {
            $this->remove($item);

            return;
        }

        $this->guardStock($item->variant, $quantity);

        $item->forceFill(['quantity' => $quantity])->save();
    }

    public function remove(CartItem $item): void
    {
        $item->delete();
    }

    public function clear(Cart $cart): void
    {
        $cart->items()->delete();
        $cart->forceFill(['coupon_id' => null])->save();
    }

    /**
     * Called right after login: folds the guest cart into the account's cart so
     * nothing added before signing in disappears.
     */
    public function mergeGuestCartInto(User $user): void
    {
        $token = session('cart_token');

        if (! $token) {
            return;
        }

        $guestCart = Cart::where('session_token', $token)->with('items')->first();

        if (! $guestCart || $guestCart->items->isEmpty()) {
            session()->forget('cart_token');

            return;
        }

        $userCart = Cart::firstOrCreate(['user_id' => $user->id]);

        DB::transaction(function () use ($guestCart, $userCart) {
            foreach ($guestCart->items as $guestItem) {
                $item = CartItem::firstOrNew([
                    'cart_id' => $userCart->id,
                    'product_variant_id' => $guestItem->product_variant_id,
                ]);

                $variant = ProductVariant::find($guestItem->product_variant_id);
                $merged = ($item->quantity ?? 0) + $guestItem->quantity;

                // Never merge past available stock; cap instead of throwing.
                $item->quantity = $variant ? min($merged, max(1, $variant->stock)) : $merged;
                $item->save();
            }

            $guestCart->delete();
        });

        session()->forget('cart_token');
    }

    private function guardStock(ProductVariant $variant, int $requested): void
    {
        if ($variant->stock < 1) {
            throw new \RuntimeException('That size is sold out.');
        }

        if ($requested > $variant->stock) {
            throw new \RuntimeException("Only {$variant->stock} left in that size.");
        }
    }
}
