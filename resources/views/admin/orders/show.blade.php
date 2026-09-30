<x-layouts.admin :title="$order->number">
    <a href="{{ route('admin.orders.index') }}" class="text-xs text-ink-400 hover:text-ink-900">← Orders</a>

    <div class="mt-2 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="font-editorial text-2xl">{{ $order->number }}</h1>
            <p class="text-sm text-ink-400">Placed {{ $order->created_at->translatedFormat('d F Y, H:i') }} by {{ $order->user->name }}</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="px-3 py-1 text-sm {{ $order->status->badgeClasses() }}">{{ $order->status->label() }}</span>
            <x-whatsapp-button :link="$whatsappLink" label="Contact customer" class="py-2 px-4 text-sm" />
        </div>
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-[1fr_20rem] items-start">
        {{-- Items + status update --}}
        <div class="space-y-6">
            <div class="bg-white border border-sand-300">
                <div class="px-5 py-4 border-b border-sand-200"><h2 class="font-medium text-sm">Items</h2></div>
                <div class="divide-y divide-sand-200">
                    @foreach ($order->items as $item)
                        <div class="px-5 py-3 flex justify-between gap-4 text-sm">
                            <div>
                                <p>{{ $item->product_name }}</p>
                                <p class="text-xs text-ink-400">{{ $item->variant_label }} · {{ $item->variant_sku }} · × {{ $item->quantity }}</p>
                            </div>
                            <p class="whitespace-nowrap">{{ rupiah($item->line_total) }}</p>
                        </div>
                    @endforeach
                </div>
                <div class="px-5 py-4 border-t border-sand-200 space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-ink-400">Subtotal</span><span>{{ rupiah($order->subtotal) }}</span></div>
                    @if ($order->discount_total > 0)
                        <div class="flex justify-between"><span class="text-ink-400">Discount{{ $order->coupon_code ? " ({$order->coupon_code})" : '' }}</span><span class="text-clay-500">−{{ rupiah($order->discount_total) }}</span></div>
                    @endif
                    <div class="flex justify-between"><span class="text-ink-400">Shipping</span><span>{{ $order->shipping_total > 0 ? rupiah($order->shipping_total) : 'Free' }}</span></div>
                    <div class="flex justify-between border-t border-sand-200 pt-2 font-medium"><span>Total</span><span>{{ rupiah($order->grand_total) }}</span></div>
                </div>
            </div>

            {{-- Status update --}}
            @if (! $order->status->isFinished())
                <div class="bg-white border border-sand-300 p-5">
                    <h2 class="font-medium text-sm mb-4">Update status</h2>

                    @if (session('error'))
                        <div class="mb-4 border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-800">{{ session('error') }}</div>
                    @endif

                    <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="space-y-4">
                        @csrf
                        <div class="flex flex-wrap gap-2">
                            @foreach ($order->status->allowedTransitions() as $next)
                                <label class="chip cursor-pointer has-[:checked]:border-ink-900 has-[:checked]:text-ink-900 has-[:checked]:bg-white">
                                    <input type="radio" name="status" value="{{ $next->value }}" class="sr-only" required>
                                    Mark as {{ $next->label() }}
                                </label>
                            @endforeach
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <div>
                                <label for="tracking_number" class="block text-xs text-ink-400 mb-1">Tracking number (optional)</label>
                                <input id="tracking_number" name="tracking_number" type="text" value="{{ $order->tracking_number }}" class="field py-2">
                            </div>
                            <div>
                                <label for="courier" class="block text-xs text-ink-400 mb-1">Courier (optional)</label>
                                <input id="courier" name="courier" type="text" value="{{ $order->courier }}" class="field py-2">
                            </div>
                        </div>

                        <div>
                            <label for="note" class="block text-xs text-ink-400 mb-1">Note (optional, kept in the order's history)</label>
                            <input id="note" name="note" type="text" class="field py-2">
                        </div>

                        <button type="submit" class="btn-primary">Update status</button>
                    </form>
                </div>
            @endif

            {{-- History --}}
            <div class="bg-white border border-sand-300">
                <div class="px-5 py-4 border-b border-sand-200"><h2 class="font-medium text-sm">History</h2></div>
                <div class="divide-y divide-sand-200">
                    @forelse ($order->statusHistories as $history)
                        <div class="px-5 py-3 text-sm">
                            <p>{{ $history->status->label() }}
                                <span class="text-xs text-ink-400">— {{ $history->created_at->translatedFormat('d M Y, H:i') }}
                                    @if ($history->user) by {{ $history->user->name }} @endif
                                </span>
                            </p>
                            @if ($history->note)
                                <p class="text-xs text-ink-400 mt-0.5">{{ $history->note }}</p>
                            @endif
                        </div>
                    @empty
                        <p class="px-5 py-6 text-sm text-ink-400">No history yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Side info --}}
        <aside class="space-y-6">
            <div class="bg-white border border-sand-300 p-5 text-sm">
                <h2 class="font-medium mb-3">Customer</h2>
                <p>{{ $order->recipient_name }}</p>
                <p class="text-ink-400">{{ $order->recipient_phone }}</p>
                <a href="{{ $whatsappLink }}" target="_blank"
                   class="inline-block mt-2 text-xs text-ink-900 underline">Message on WhatsApp ↗</a>
            </div>

            <div class="bg-white border border-sand-300 p-5 text-sm">
                <h2 class="font-medium mb-3">Delivery address</h2>
                <p class="text-ink-400 leading-relaxed">{{ $order->shipping_summary }}</p>
                @if ($order->customer_note)
                    <p class="mt-3 text-xs text-ink-400">Note: {{ $order->customer_note }}</p>
                @endif
            </div>

            @if ($order->payment)
                <div class="bg-white border border-sand-300 p-5 text-sm">
                    <h2 class="font-medium mb-3">Payment</h2>
                    <p>{{ $order->payment->method->label() }}</p>
                    <p class="text-ink-400">{{ ucfirst($order->payment_status->value) }}</p>
                </div>
            @endif
        </aside>
    </div>
</x-layouts.admin>
