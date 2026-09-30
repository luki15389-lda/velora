<?php

namespace App\Services;

use App\Enums\InventoryReason;
use App\Models\InventoryLog;
use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * The only place stock is allowed to move. Every call writes an audit row
     * with the before/after values so /admin/inventory can reconstruct history.
     */
    public function adjust(
        ProductVariant $variant,
        int $change,
        InventoryReason $reason,
        ?Order $order = null,
        ?string $note = null,
    ): ProductVariant {
        return DB::transaction(function () use ($variant, $change, $reason, $order, $note) {
            // Lock the row so two concurrent checkouts cannot oversell the same size.
            $locked = ProductVariant::whereKey($variant->id)->lockForUpdate()->firstOrFail();

            $before = $locked->stock;
            $after = $before + $change;

            if ($after < 0) {
                throw new \RuntimeException("Not enough stock for {$locked->sku}. {$before} left.");
            }

            $locked->forceFill(['stock' => $after])->save();

            InventoryLog::create([
                'product_variant_id' => $locked->id,
                'user_id' => auth()->id(),
                'order_id' => $order?->id,
                'reason' => $reason,
                'quantity_change' => $change,
                'stock_before' => $before,
                'stock_after' => $after,
                'note' => $note,
            ]);

            return $locked;
        });
    }

    public function consumeForOrder(ProductVariant $variant, int $quantity, Order $order): void
    {
        $this->adjust($variant, -$quantity, InventoryReason::Sale, $order, "Sold on order {$order->number}");
    }

    public function releaseForOrder(ProductVariant $variant, int $quantity, Order $order): void
    {
        $this->adjust($variant, $quantity, InventoryReason::Cancellation, $order, "Released from order {$order->number}");
    }
}
