<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrderStatusChanged extends Notification
{
    use Queueable;

    public function __construct(public Order $order, public string $previousStatus) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'order_status_changed',
            'order_id' => $this->order->id,
            'order_number' => $this->order->number,
            'from' => $this->previousStatus,
            'to' => $this->order->status->value,
            'title' => "Order {$this->order->number} is now {$this->order->status->label()}",
            'body' => 'Open the order to see the full timeline.',
            'url' => route('orders.show', $this->order),
        ];
    }
}
