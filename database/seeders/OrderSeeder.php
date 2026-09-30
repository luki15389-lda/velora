<?php

namespace Database\Seeders;

use App\Enums\InventoryReason;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ShippingMethod;
use App\Models\Address;
use App\Models\InventoryLog;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    private const ORDER_COUNT = 60;

    public function run(): void
    {
        $customers = User::customers()->with('addresses')->get();
        $variantIds = ProductVariant::inStock()->pluck('id');

        if ($customers->isEmpty() || $variantIds->isEmpty()) {
            $this->command->warn('No customers or stocked variants — skipping orders.');

            return;
        }

        // Weighted so the dashboard shows a believable funnel rather than an even split.
        $statusPool = [
            OrderStatus::Delivered, OrderStatus::Delivered, OrderStatus::Delivered,
            OrderStatus::Shipped, OrderStatus::Shipped,
            OrderStatus::Packed, OrderStatus::Processing,
            OrderStatus::Paid, OrderStatus::Pending, OrderStatus::Pending,
            OrderStatus::Cancelled, OrderStatus::Refunded,
        ];

        for ($i = 0; $i < self::ORDER_COUNT; $i++) {
            $customer = $customers->random();
            $status = $statusPool[array_rand($statusPool)];
            $placedAt = Carbon::now()->subDays(random_int(0, 120))->setTime(random_int(8, 21), random_int(0, 59));

            DB::transaction(function () use ($customer, $status, $placedAt, $variantIds) {
                $this->createOrder($customer, $status, $placedAt, $variantIds);
            });
        }

        $this->command->info('Seeded '.Order::count().' orders with '.OrderItem::count().' line items.');
    }

    private function createOrder(User $customer, OrderStatus $status, Carbon $placedAt, $variantIds): void
    {
        $address = $customer->addresses->first() ?? Address::factory()->for($customer)->create();
        $shipping = collect(ShippingMethod::cases())->random();

        $order = new Order([
            'number' => $this->number($placedAt),
            'user_id' => $customer->id,
            'status' => $status,
            'payment_status' => $this->paymentStatus($status),
            'recipient_name' => $address->recipient_name,
            'recipient_phone' => $address->phone,
            'province' => $address->province,
            'city' => $address->city,
            'district' => $address->district,
            'postal_code' => $address->postal_code,
            'street_address' => $address->street_address,
            'shipping_method' => $shipping,
            'shipping_total' => $shipping->cost(),
        ]);

        $order->created_at = $placedAt;
        $order->updated_at = $placedAt;
        $order->save();

        $subtotal = 0;
        $chosen = $variantIds->random(random_int(1, 4));
        $chosen = $chosen instanceof \Illuminate\Support\Collection ? $chosen : collect([$chosen]);

        foreach ($chosen->unique() as $variantId) {
            $variant = ProductVariant::with(['product.primaryImage', 'color', 'size'])->find($variantId);

            if (! $variant || $variant->stock < 1) {
                continue;
            }

            $quantity = min($variant->stock, random_int(1, 2));
            $unitPrice = $variant->effective_price;
            $lineTotal = $unitPrice * $quantity;
            $subtotal += $lineTotal;

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $variant->product_id,
                'product_variant_id' => $variant->id,
                'product_name' => $variant->product->name,
                'variant_sku' => $variant->sku,
                'color_name' => $variant->color?->name,
                'size_name' => $variant->size?->name,
                'image_path' => $variant->product->primaryImage?->path,
                'unit_price' => $unitPrice,
                'quantity' => $quantity,
                'line_total' => $lineTotal,
            ]);

            // Cancelled and refunded orders release their stock again, so only the
            // live ones actually consume inventory.
            if (! in_array($status, [OrderStatus::Cancelled, OrderStatus::Refunded], true)) {
                $before = $variant->stock;
                $variant->decrement('stock', $quantity);

                InventoryLog::create([
                    'product_variant_id' => $variant->id,
                    'order_id' => $order->id,
                    'reason' => InventoryReason::Sale,
                    'quantity_change' => -$quantity,
                    'stock_before' => $before,
                    'stock_after' => $before - $quantity,
                    'note' => "Sold on order {$order->number}",
                ]);

                Product::whereKey($variant->product_id)->increment('sold_count', $quantity);
            }
        }

        if ($order->items()->doesntExist()) {
            $order->delete();

            return;
        }

        $discount = $this->maybeDiscount($subtotal);
        $shippingTotal = $subtotal >= 500_000 ? 0 : $shipping->cost();

        $order->forceFill([
            'subtotal' => $subtotal,
            'discount_total' => $discount,
            'shipping_total' => $shippingTotal,
            'grand_total' => max(0, $subtotal - $discount + $shippingTotal),
            'paid_at' => $this->timestampFor($status, OrderStatus::Paid, $placedAt),
            'shipped_at' => $this->timestampFor($status, OrderStatus::Shipped, $placedAt),
            'delivered_at' => $this->timestampFor($status, OrderStatus::Delivered, $placedAt),
            'cancelled_at' => $status === OrderStatus::Cancelled ? $placedAt->copy()->addHours(6) : null,
            'tracking_number' => $status->timelineIndex() >= 4 ? 'JNE'.Str::upper(Str::random(10)) : null,
            'courier' => $status->timelineIndex() >= 4 ? 'JNE Reguler' : null,
        ])->save();

        Payment::create([
            'order_id' => $order->id,
            'method' => collect(PaymentMethod::cases())->random(),
            'status' => $order->payment_status,
            'amount' => $order->grand_total,
            'reference' => 'PAY-'.Str::upper(Str::random(12)),
            'provider' => 'VELORA Pay Sandbox',
            'paid_at' => $order->paid_at,
            'created_at' => $placedAt,
            'updated_at' => $placedAt,
        ]);

        $this->writeHistory($order, $status, $placedAt);
    }

    private function writeHistory(Order $order, OrderStatus $status, Carbon $placedAt): void
    {
        $reached = match ($status) {
            OrderStatus::Cancelled => [OrderStatus::Pending, OrderStatus::Cancelled],
            OrderStatus::Refunded => [OrderStatus::Pending, OrderStatus::Paid, OrderStatus::Refunded],
            default => array_slice(OrderStatus::timeline(), 0, $status->timelineIndex() + 1),
        };

        foreach ($reached as $index => $step) {
            $at = $placedAt->copy()->addHours($index * random_int(6, 30));

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => $step,
                'note' => 'Seeded demo history',
                'created_at' => $at,
                'updated_at' => $at,
            ]);
        }
    }

    private function timestampFor(OrderStatus $status, OrderStatus $step, Carbon $placedAt): ?Carbon
    {
        if (in_array($status, [OrderStatus::Cancelled], true)) {
            return null;
        }

        if ($status === OrderStatus::Refunded) {
            return $step === OrderStatus::Paid ? $placedAt->copy()->addHours(2) : null;
        }

        return $status->timelineIndex() >= $step->timelineIndex()
            ? $placedAt->copy()->addHours($step->timelineIndex() * random_int(6, 30))
            : null;
    }

    private function paymentStatus(OrderStatus $status): PaymentStatus
    {
        return match ($status) {
            OrderStatus::Pending => PaymentStatus::Pending,
            OrderStatus::Cancelled => PaymentStatus::Failed,
            OrderStatus::Refunded => PaymentStatus::Refunded,
            default => PaymentStatus::Paid,
        };
    }

    private function maybeDiscount(int $subtotal): int
    {
        if (random_int(1, 100) > 30 || $subtotal < 250_000) {
            return 0;
        }

        return (int) (round(min($subtotal * 0.1, 100_000) / 1000) * 1000);
    }

    private function number(Carbon $placedAt): string
    {
        do {
            $number = 'VLR-'.$placedAt->format('Ymd').'-'.Str::upper(Str::random(4));
        } while (Order::where('number', $number)->exists());

        return $number;
    }
}
