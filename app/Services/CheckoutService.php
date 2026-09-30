<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ShippingMethod;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Notifications\OrderPlaced;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class CheckoutService
{
    public function __construct(
        private CartService $carts,
        private CouponService $coupons,
        private InventoryService $inventory,
    ) {}

    /**
     * Turns a cart into an order. Everything below runs inside one transaction:
     * if any line runs out of stock mid-checkout the whole thing rolls back and
     * the cart is left untouched.
     *
     * @param  array{recipient_name:string,recipient_phone:string,province:string,city:string,district:string,postal_code:string,street_address:string,customer_note:?string}  $address
     */
    public function place(
        User $user,
        Cart $cart,
        array $address,
        ShippingMethod $shipping,
        PaymentMethod $paymentMethod,
        ?Coupon $coupon = null,
    ): Order {
        $cart->load('items.variant.product.primaryImage', 'items.variant.color', 'items.variant.size');

        if ($cart->items->isEmpty()) {
            throw new \RuntimeException('Your cart is empty.');
        }

        return DB::transaction(function () use ($user, $cart, $address, $shipping, $paymentMethod, $coupon) {
            $order = Order::create([
                'number' => Order::generateNumber(),
                'user_id' => $user->id,
                'status' => OrderStatus::Pending,
                'payment_status' => PaymentStatus::Unpaid,
                'recipient_name' => $address['recipient_name'],
                'recipient_phone' => $address['recipient_phone'],
                'province' => $address['province'],
                'city' => $address['city'],
                'district' => $address['district'],
                'postal_code' => $address['postal_code'],
                'street_address' => $address['street_address'],
                'customer_note' => $address['customer_note'] ?? null,
                'shipping_method' => $shipping,
            ]);

            $subtotal = 0;

            foreach ($cart->items as $item) {
                // Re-read the variant under a lock; the cart may be minutes old.
                $variant = ProductVariant::whereKey($item->product_variant_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($variant->stock < $item->quantity) {
                    throw new \RuntimeException(
                        "{$item->variant->product->name} ({$item->variant->label}) only has {$variant->stock} left."
                    );
                }

                $unitPrice = $item->variant->effective_price;
                $lineTotal = $unitPrice * $item->quantity;
                $subtotal += $lineTotal;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $variant->product_id,
                    'product_variant_id' => $variant->id,
                    'product_name' => $item->variant->product->name,
                    'variant_sku' => $variant->sku,
                    'color_name' => $item->variant->color?->name,
                    'size_name' => $item->variant->size?->name,
                    'image_path' => $item->variant->product->primaryImage?->path,
                    'unit_price' => $unitPrice,
                    'quantity' => $item->quantity,
                    'line_total' => $lineTotal,
                ]);

                $this->inventory->consumeForOrder($variant, $item->quantity, $order);

                Product::whereKey($variant->product_id)->increment('sold_count', $item->quantity);
            }

            $totals = $this->totals($subtotal, $shipping, $coupon);

            $order->forceFill([
                'subtotal' => $subtotal,
                'discount_total' => $totals['discount'],
                'shipping_total' => $totals['shipping'],
                'grand_total' => $totals['grand_total'],
                'coupon_id' => $coupon?->id,
                'coupon_code' => $coupon?->code,
            ])->save();

            if ($coupon) {
                CouponUsage::create([
                    'coupon_id' => $coupon->id,
                    'user_id' => $user->id,
                    'order_id' => $order->id,
                    'discount_amount' => $totals['discount'] + ($this->coupons->waivesShipping($coupon) ? $shipping->cost() : 0),
                ]);

                $coupon->increment('used_count');
            }

            Payment::create([
                'order_id' => $order->id,
                'method' => $paymentMethod,
                'status' => PaymentStatus::Unpaid,
                'amount' => $totals['grand_total'],
                'provider' => 'WhatsApp confirmation',
            ]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'user_id' => $user->id,
                'status' => OrderStatus::Pending,
                'note' => 'Order placed, waiting for payment confirmation over WhatsApp.',
            ]);

            $this->carts->clear($cart);

            $user->notify(new OrderPlaced($order));

            $backoffice = User::where('role', 'admin')->get();
            Notification::send($backoffice, new OrderPlaced($order));

            return $order;
        });
    }

    /**
     * Shared by the cart summary, the checkout summary and the order itself, so
     * the number the customer sees never drifts from the number that is saved.
     *
     * @return array{discount:int,shipping:int,grand_total:int}
     */
    public function totals(int $subtotal, ShippingMethod $shipping, ?Coupon $coupon = null): array
    {
        $discount = $coupon ? $this->coupons->discountFor($coupon, $subtotal) : 0;

        $freeThreshold = (int) setting('shipping.free_threshold', 500_000);
        $shippingCost = $shipping->cost();

        if ($subtotal >= $freeThreshold) {
            $shippingCost = 0;
        }

        if ($coupon && $this->coupons->waivesShipping($coupon)) {
            $shippingCost = 0;
        }

        return [
            'discount' => $discount,
            'shipping' => $shippingCost,
            'grand_total' => max(0, $subtotal - $discount + $shippingCost),
        ];
    }
}
