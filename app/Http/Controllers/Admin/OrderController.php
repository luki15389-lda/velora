<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Services\InventoryService;
use App\Services\WhatsAppService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('manage-orders');

        $status = $request->get('status');

        $orders = Order::query()
            ->with('user')
            ->withCount('items')
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'statusFilter' => $status,
            'statusCounts' => Order::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    public function show(Order $order, WhatsAppService $whatsapp): View
    {
        $this->authorize('manage-orders');

        $order->load(['items.variant', 'payment', 'statusHistories.user', 'user', 'coupon']);

        return view('admin.orders.show', [
            'order' => $order,
            'whatsappLink' => $whatsapp->linkToCustomer($order),
        ]);
    }

    public function updateStatus(Request $request, Order $order, InventoryService $inventory): RedirectResponse
    {
        $this->authorize('manage-orders');

        $data = $request->validate([
            'status' => ['required', 'string'],
            'tracking_number' => ['nullable', 'string', 'max:64'],
            'courier' => ['nullable', 'string', 'max:40'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $newStatus = OrderStatus::from($data['status']);

        if (! in_array($newStatus, $order->status->allowedTransitions(), true)) {
            return back()->with('error', "Can't move an order from {$order->status->label()} to {$newStatus->label()} directly.");
        }

        DB::transaction(function () use ($order, $newStatus, $data, $inventory) {
            // Cancelling or refunding puts the stock back on the shelf.
            if (in_array($newStatus, [OrderStatus::Cancelled, OrderStatus::Refunded], true)
                && ! in_array($order->status, [OrderStatus::Cancelled, OrderStatus::Refunded], true)) {
                $order->load('items.variant');

                foreach ($order->items as $item) {
                    if ($item->variant) {
                        $inventory->releaseForOrder($item->variant, $item->quantity, $order);
                    }
                }
            }

            $order->forceFill(array_filter([
                'status' => $newStatus,
                'tracking_number' => $data['tracking_number'] ?? $order->tracking_number,
                'courier' => $data['courier'] ?? $order->courier,
                'paid_at' => $newStatus === OrderStatus::Paid && ! $order->paid_at ? now() : $order->paid_at,
                'shipped_at' => $newStatus === OrderStatus::Shipped && ! $order->shipped_at ? now() : $order->shipped_at,
                'delivered_at' => $newStatus === OrderStatus::Delivered && ! $order->delivered_at ? now() : $order->delivered_at,
                'cancelled_at' => $newStatus === OrderStatus::Cancelled ? now() : $order->cancelled_at,
                'payment_status' => in_array($newStatus, [OrderStatus::Paid, OrderStatus::Processing, OrderStatus::Packed, OrderStatus::Shipped, OrderStatus::Delivered], true)
                    ? 'paid' : $order->payment_status->value,
            ], fn ($v) => $v !== null))->save();

            if ($order->payment) {
                $order->payment->update(['status' => $order->payment_status]);
            }

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'user_id' => auth()->id(),
                'status' => $newStatus,
                'note' => $data['note'] ?: "Updated by ".auth()->user()->name,
            ]);
        });

        return back()->with('status', "Order {$order->number} is now {$newStatus->label()}.");
    }
}
