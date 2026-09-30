<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrderPlaced extends Notification
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'order_placed',
            'order_id' => $this->order->id,
            'order_number' => $this->order->number,
            'grand_total' => $this->order->grand_total,
            'title' => "Order {$this->order->number} placed",
            'body' => 'Confirm payment over WhatsApp to start processing.',
            'url' => route('orders.show', $this->order),
        ];
    }
}
