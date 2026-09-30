<?php

namespace App\Services;

use App\Models\Order;

/**
 * Checkout does not call a payment gateway. The order is written to the database
 * first, then the customer is handed a pre-filled wa.me link so confirmation
 * happens in the conversation the store already runs on.
 */
class WhatsAppService
{
    public function storeNumber(): string
    {
        // wa.me wants digits only, country code first, no plus sign.
        return preg_replace('/\D+/', '', (string) setting('store.whatsapp', '6281120009000'));
    }

    public function linkForOrder(Order $order): string
    {
        return 'https://wa.me/'.$this->storeNumber().'?text='.rawurlencode($this->messageForOrder($order));
    }

    /**
     * The admin-side "contact this customer" link — opposite direction from
     * linkForOrder(), written as the store speaking to the customer rather
     * than the other way around, and opened to the customer's own number.
     */
    public function linkToCustomer(Order $order): string
    {
        $phone = preg_replace('/\D+/', '', $order->recipient_phone);

        return 'https://wa.me/'.$phone.'?text='.rawurlencode($this->messageToCustomer($order));
    }

    public function messageToCustomer(Order $order): string
    {
        $statusLine = match ($order->status->value) {
            'pending' => 'saat ini menunggu konfirmasi pembayaran',
            'paid' => 'pembayarannya sudah kami terima',
            'processing' => 'sedang kami proses',
            'packed' => 'sudah dikemas dan siap dikirim',
            'shipped' => 'sudah dalam perjalanan'.($order->tracking_number ? " (resi: {$order->tracking_number})" : ''),
            'delivered' => 'sudah sampai tujuan',
            'cancelled' => 'telah dibatalkan',
            'refunded' => 'sudah kami proses pengembalian dananya',
            default => "berstatus {$order->status->label()}",
        };

        return "Halo {$order->recipient_name}, ini dari VELORA mengenai pesanan {$order->number} — {$statusLine}. Ada yang bisa kami bantu?";
    }

    public function messageForOrder(Order $order): string
    {
        $order->loadMissing('items');

        $lines = [
            'Halo VELORA, saya ingin konfirmasi pesanan.',
            '',
            "No. Pesanan: {$order->number}",
            "Nama: {$order->recipient_name}",
            "No. HP: {$order->recipient_phone}",
            '',
            'Rincian pesanan:',
        ];

        foreach ($order->items as $index => $item) {
            $number = $index + 1;
            $variant = $item->variant_label ? " ({$item->variant_label})" : '';
            $lines[] = "{$number}. {$item->product_name}{$variant} x{$item->quantity} — ".rupiah($item->line_total);
        }

        $lines[] = '';
        $lines[] = 'Subtotal: '.rupiah($order->subtotal);

        if ($order->discount_total > 0) {
            $lines[] = 'Diskon'.($order->coupon_code ? " ({$order->coupon_code})" : '').': -'.rupiah($order->discount_total);
        }

        $lines[] = 'Ongkir ('.$order->shipping_method->label().'): '
            .($order->shipping_total > 0 ? rupiah($order->shipping_total) : 'Gratis');
        $lines[] = 'TOTAL: '.rupiah($order->grand_total);
        $lines[] = '';
        $lines[] = 'Alamat pengiriman:';
        $lines[] = $order->shipping_summary;

        if ($order->customer_note) {
            $lines[] = '';
            $lines[] = "Catatan: {$order->customer_note}";
        }

        $lines[] = '';
        $lines[] = 'Mohon informasi pembayarannya. Terima kasih.';

        return implode("\n", $lines);
    }
}
