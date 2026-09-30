<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Services\InventoryService;
use App\Services\WhatsAppService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(): View
    {
        return view('orders.index', [
            'orders' => Order::where('user_id', auth()->id())
                ->with('items')
                ->latest()
                ->paginate(10),
        ]);
    }

    public function show(Order $order, WhatsAppService $whatsapp): View
    {
        $this->authorize('view', $order);

        return view('orders.show', [
            'order' => $order->load(['items', 'payment', 'statusHistories']),
            'whatsappLink' => $whatsapp->linkForOrder($order),
        ]);
    }

    /** Post-checkout confirmation, with the WhatsApp hand-off front and centre. */
    public function success(Order $order, WhatsAppService $whatsapp): View
    {
        $this->authorize('view', $order);

        return view('orders.success', [
            'order' => $order->load('items'),
            'whatsappLink' => $whatsapp->linkForOrder($order),
        ]);
    }

    public function cancel(Order $order, InventoryService $inventory): RedirectResponse
    {
        $this->authorize('cancel', $order);

        if (! $order->isCancellable()) {
            return back()->with('error', 'This order has already moved past the point where it can be cancelled.');
        }

        DB::transaction(function () use ($order, $inventory) {
            $order->load('items.variant');

            foreach ($order->items as $item) {
                if ($item->variant) {
                    $inventory->releaseForOrder($item->variant, $item->quantity, $order);
                }
            }

            $order->forceFill([
                'status' => OrderStatus::Cancelled,
                'cancelled_at' => now(),
            ])->save();

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'user_id' => auth()->id(),
                'status' => OrderStatus::Cancelled,
                'note' => 'Cancelled by the customer.',
            ]);
        });

        return redirect()
            ->route('orders.show', $order)
            ->with('status', "Order {$order->number} cancelled and the stock released.");
    }
}
