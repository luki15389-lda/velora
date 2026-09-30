<x-layouts.app title="Order placed">
<section class="mx-auto max-w-2xl px-4 py-16" x-data x-init="setTimeout(() => window.open(@js($whatsappLink), '_blank'), 400)">
    <div class="text-center">
        <div class="mx-auto h-14 w-14 rounded-full bg-ink-900 text-white grid place-items-center text-2xl">✓</div>
        <h1 class="mt-6 font-editorial text-3xl">Order {{ $order->number }} is in</h1>
        <p class="mt-3 text-ink-400">
            We've reserved your stock and opened WhatsApp in a new tab with everything filled in —
            just hit send. If it didn't open, use the button below.
        </p>
    </div>

    <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
        <x-whatsapp-button :link="$whatsappLink" label="Open WhatsApp" class="flex-1 justify-center" />
        <a href="{{ route('orders.show', $order) }}" class="btn-outline flex-1 justify-center">View order</a>
    </div>

    <div class="mt-10 border border-sand-300 bg-white p-6">
        <h2 class="font-medium text-sm">What you ordered</h2>

        <div class="mt-4 divide-y divide-sand-200 border-t border-sand-200">
            @foreach ($order->items as $item)
                <div class="py-3 flex justify-between gap-4 text-sm">
                    <span>
                        {{ $item->product_name }}
                        <span class="text-ink-400">{{ $item->variant_label ? "({$item->variant_label})" : '' }} × {{ $item->quantity }}</span>
                    </span>
                    <span class="whitespace-nowrap">{{ rupiah($item->line_total) }}</span>
                </div>
            @endforeach
        </div>

        <dl class="mt-4 space-y-2 text-sm border-t border-sand-200 pt-4">
            <div class="flex justify-between"><dt class="text-ink-400">Subtotal</dt><dd>{{ rupiah($order->subtotal) }}</dd></div>
            @if ($order->discount_total > 0)
                <div class="flex justify-between"><dt class="text-ink-400">Discount ({{ $order->coupon_code }})</dt><dd class="text-clay-500">−{{ rupiah($order->discount_total) }}</dd></div>
            @endif
            <div class="flex justify-between"><dt class="text-ink-400">Shipping</dt><dd>{{ $order->shipping_total > 0 ? rupiah($order->shipping_total) : 'Free' }}</dd></div>
            <div class="flex justify-between border-t border-sand-200 pt-3 text-base"><dt>Total</dt><dd>{{ rupiah($order->grand_total) }}</dd></div>
        </dl>

        <p class="mt-5 text-xs text-ink-400">Delivering to {{ $order->recipient_name }} — {{ $order->shipping_summary }}</p>
    </div>
</section>
</x-layouts.app>
