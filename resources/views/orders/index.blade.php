<x-layouts.app title="My orders">
<section class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="font-editorial text-3xl sm:text-4xl">My orders</h1>

    @if ($orders->isEmpty())
        <div class="mt-10 border border-sand-300 bg-white py-20 text-center">
            <p class="font-editorial text-2xl">No orders yet</p>
            <a href="{{ route('shop') }}" class="btn-primary mt-6">Browse the shop</a>
        </div>
    @else
        <div class="mt-8 space-y-4">
            @foreach ($orders as $order)
                <a href="{{ route('orders.show', $order) }}"
                   class="block border border-sand-300 bg-white p-5 hover:border-ink-900 transition-colors">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-medium">{{ $order->number }}</p>
                            <p class="mt-1 text-xs text-ink-400">
                                {{ $order->created_at->translatedFormat('d M Y') }} ·
                                {{ $order->item_count }} {{ \Illuminate\Support\Str::plural('item', $order->item_count) }}
                            </p>
                        </div>

                        <div class="flex items-center gap-4">
                            <span class="px-2 py-1 text-xs {{ $order->status->badgeClasses() }}">{{ $order->status->label() }}</span>
                            <span class="text-sm">{{ rupiah($order->grand_total) }}</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-10">{{ $orders->links() }}</div>
    @endif
</section>
</x-layouts.app>
