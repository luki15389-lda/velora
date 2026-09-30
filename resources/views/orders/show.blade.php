<x-layouts.app :title="'Order '.$order->number">
<section class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-12">
    <a href="{{ route('orders.index') }}" class="text-xs text-ink-400 hover:text-ink-900">← All orders</a>

    <div class="mt-4 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="font-editorial text-3xl">{{ $order->number }}</h1>
            <p class="mt-1 text-sm text-ink-400">Placed {{ $order->created_at->translatedFormat('d F Y, H:i') }}</p>
        </div>

        <span class="px-3 py-1 text-sm {{ $order->status->badgeClasses() }}">{{ $order->status->label() }}</span>
    </div>

    @if ($order->payment_status->value !== 'paid' && ! $order->status->isFinished())
        <div class="mt-6 border border-clay-400 bg-clay-400/20 p-5 sm:flex sm:items-center sm:justify-between gap-4">
            <div>
                <p class="text-sm font-medium">Payment not confirmed yet</p>
                <p class="mt-1 text-sm text-ink-600">Send us the order on WhatsApp and we will reply with the payment details.</p>
            </div>
            <x-whatsapp-button :link="$whatsappLink" class="mt-4 sm:mt-0 whitespace-nowrap" />
        </div>
    @endif

    {{-- Timeline --}}
    <div class="mt-10">
        <h2 class="font-medium text-sm">Progress</h2>

        @if (in_array($order->status->value, ['cancelled', 'refunded']))
            <p class="mt-4 text-sm text-ink-400">
                This order was {{ $order->status->label() }}
                @if ($order->cancelled_at) on {{ $order->cancelled_at->translatedFormat('d F Y') }} @endif.
                Any reserved stock has been released.
            </p>
        @else
            @php($currentIndex = $order->status->timelineIndex())
            <ol class="mt-5 space-y-0 sm:flex sm:space-y-0 sm:gap-2">
                @foreach (\App\Enums\OrderStatus::timeline() as $index => $step)
                    <li class="flex sm:flex-col sm:flex-1 gap-3 sm:gap-2 pb-5 sm:pb-0">
                        <div class="flex sm:w-full items-center">
                            <span class="h-6 w-6 shrink-0 rounded-full grid place-items-center text-[10px]
                                         {{ $index <= $currentIndex ? 'bg-ink-900 text-white' : 'border border-sand-300 text-ink-400' }}">
                                {{ $index < $currentIndex ? '✓' : $index + 1 }}
                            </span>
                            @if (! $loop->last)
                                <span class="hidden sm:block h-px flex-1 {{ $index < $currentIndex ? 'bg-ink-900' : 'bg-sand-300' }}"></span>
                            @endif
                        </div>
                        <div>
                            <p class="text-xs {{ $index <= $currentIndex ? 'text-ink-900' : 'text-ink-400' }}">{{ $step->label() }}</p>
                            @php($history = $order->statusHistories->firstWhere('status', $step))
                            @if ($history)
                                <p class="text-[11px] text-ink-400 mt-0.5">{{ $history->created_at->translatedFormat('d M, H:i') }}</p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
        @endif

        @if ($order->tracking_number)
            <p class="mt-6 text-sm">
                <span class="text-ink-400">Tracking:</span> {{ $order->courier }} · {{ $order->tracking_number }}
            </p>
        @endif
    </div>

    <div class="mt-12 grid gap-8 lg:grid-cols-[1fr_20rem] items-start">
        {{-- Items --}}
        <div class="border border-sand-300 bg-white">
            <div class="divide-y divide-sand-200">
                @foreach ($order->items as $item)
                    <div class="p-5 flex gap-4">
                        <div class="w-20 shrink-0 aspect-[4/5] bg-sand-200 overflow-hidden">
                            @if ($item->image_url)
                                <img src="{{ $item->image_url }}" alt="" class="h-full w-full object-cover">
                            @endif
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between gap-4">
                                <div>
                                    <p class="text-sm font-medium">{{ $item->product_name }}</p>
                                    <p class="mt-1 text-xs text-ink-400">{{ $item->variant_label }}</p>
                                    <p class="mt-1 text-xs text-ink-400">{{ $item->variant_sku }} · × {{ $item->quantity }}</p>
                                </div>
                                <p class="text-sm whitespace-nowrap">{{ rupiah($item->line_total) }}</p>
                            </div>

                            @if ($order->status->value === 'delivered' && ! $item->is_reviewed)
                                <div class="mt-4">
                                    <livewire:reviews.review-form :item="$item" :key="'review-'.$item->id" />
                                </div>
                            @elseif ($item->is_reviewed)
                                <p class="mt-3 text-xs text-ink-400">You reviewed this piece.</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Side --}}
        <aside class="space-y-6">
            <div class="border border-sand-300 bg-white p-5">
                <h2 class="font-medium text-sm">Totals</h2>
                <dl class="mt-4 space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-ink-400">Subtotal</dt><dd>{{ rupiah($order->subtotal) }}</dd></div>
                    @if ($order->discount_total > 0)
                        <div class="flex justify-between"><dt class="text-ink-400">Discount ({{ $order->coupon_code }})</dt><dd class="text-clay-500">−{{ rupiah($order->discount_total) }}</dd></div>
                    @endif
                    <div class="flex justify-between"><dt class="text-ink-400">{{ $order->shipping_method->label() }}</dt><dd>{{ $order->shipping_total > 0 ? rupiah($order->shipping_total) : 'Free' }}</dd></div>
                    <div class="flex justify-between border-t border-sand-200 pt-3 text-base"><dt>Total</dt><dd>{{ rupiah($order->grand_total) }}</dd></div>
                </dl>

                @if ($order->payment)
                    <p class="mt-4 text-xs text-ink-400">
                        {{ $order->payment->method->label() }} · {{ ucfirst($order->payment->status->value) }}
                    </p>
                @endif
            </div>

            <div class="border border-sand-300 bg-white p-5 text-sm">
                <h2 class="font-medium">Delivering to</h2>
                <p class="mt-3">{{ $order->recipient_name }}</p>
                <p class="text-ink-400">{{ $order->recipient_phone }}</p>
                <p class="mt-2 text-ink-400 leading-relaxed">{{ $order->shipping_summary }}</p>
                @if ($order->customer_note)
                    <p class="mt-3 text-xs text-ink-400">Note: {{ $order->customer_note }}</p>
                @endif
            </div>

            @can('cancel', $order)
                @if ($order->isCancellable())
                    <form method="POST" action="{{ route('orders.cancel', $order) }}"
                          onsubmit="return confirm('Cancel this order? The stock goes back on the shelf.')">
                        @csrf
                        <button type="submit" class="btn-outline w-full">Cancel order</button>
                    </form>
                @endif
            @endcan
        </aside>
    </div>
</section>
</x-layouts.app>
