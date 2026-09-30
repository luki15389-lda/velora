<x-layouts.admin title="Dashboard">
    <div class="flex items-center justify-between lg:hidden mb-6">
        <h1 class="font-editorial text-2xl">Dashboard</h1>
    </div>

    {{-- Stat cards --}}
    <div class="grid gap-4 grid-cols-2 lg:grid-cols-4">
        @foreach ([
            ['Revenue (paid)', rupiah($revenue), 'default'],
            ['Orders', number_format($orderCount), 'default'],
            ['Customers', number_format($customerCount), 'default'],
            ['Active products', number_format($productCount), 'default'],
            ['Pending orders', number_format($pendingOrders), $pendingOrders > 0 ? 'warn' : 'default'],
            ['Low stock variants', number_format($lowStockCount), $lowStockCount > 0 ? 'alert' : 'default'],
        ] as [$label, $value, $tone])
            <div class="bg-white border border-sand-300 p-5">
                <p class="text-xs text-ink-400">{{ $label }}</p>
                <p class="mt-2 text-2xl font-editorial
                          {{ $tone === 'alert' ? 'text-red-700' : ($tone === 'warn' ? 'text-clay-500' : '') }}">
                    {{ $value }}
                </p>
            </div>
        @endforeach
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-3">
        {{-- Recent orders --}}
        <div class="lg:col-span-2 bg-white border border-sand-300">
            <div class="flex items-center justify-between px-5 py-4 border-b border-sand-200">
                <h2 class="font-medium text-sm">Recent orders</h2>
                <span class="text-xs text-ink-400">Live from the database</span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-sand-100 text-left text-xs text-ink-400">
                        <tr>
                            <th class="px-5 py-3 font-medium">Order</th>
                            <th class="px-5 py-3 font-medium">Customer</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            <th class="px-5 py-3 font-medium text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-sand-200">
                        @forelse ($recentOrders as $order)
                            <tr class="hover:bg-sand-100/60">
                                <td class="px-5 py-3">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="font-medium hover:underline">{{ $order->number }}</a>
                                    <span class="block text-xs text-ink-400">{{ $order->created_at->diffForHumans() }}</span>
                                </td>
                                <td class="px-5 py-3">{{ $order->user->name }}</td>
                                <td class="px-5 py-3">
                                    <span class="px-2 py-1 text-xs {{ $order->status->badgeClasses() }}">{{ $order->status->label() }}</span>
                                </td>
                                <td class="px-5 py-3 text-right">{{ rupiah($order->grand_total) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-10 text-center text-ink-400">No orders yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Side column --}}
        <div class="space-y-6">
            <div class="bg-white border border-sand-300">
                <div class="px-5 py-4 border-b border-sand-200">
                    <h2 class="font-medium text-sm">Best sellers</h2>
                </div>
                <div class="divide-y divide-sand-200">
                    @forelse ($topProducts as $product)
                        <a href="{{ route('admin.products.edit', $product) }}" class="flex items-center gap-3 px-5 py-3 hover:bg-sand-100/60">
                            <div class="h-10 w-8 bg-sand-200 shrink-0 overflow-hidden">
                                @if ($product->primaryImage)
                                    <img src="{{ $product->primaryImage->url }}" class="h-full w-full object-cover" alt="">
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm truncate">{{ $product->name }}</p>
                                <p class="text-xs text-ink-400">{{ $product->sold_count }} sold</p>
                            </div>
                        </a>
                    @empty
                        <p class="px-5 py-6 text-sm text-ink-400">No sales yet.</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-white border border-sand-300">
                <div class="flex items-center justify-between px-5 py-4 border-b border-sand-200">
                    <h2 class="font-medium text-sm">Low stock</h2>
                    @if ($lowStockCount > 0)
                        <span class="text-xs text-red-700">{{ $lowStockCount }} total</span>
                    @endif
                </div>
                <div class="divide-y divide-sand-200">
                    @forelse ($lowStockVariants as $variant)
                        <a href="{{ route('admin.products.edit', $variant->product) }}" class="flex items-center justify-between gap-3 px-5 py-3 hover:bg-sand-100/60">
                            <div class="min-w-0">
                                <p class="text-sm truncate">{{ $variant->product->name }}</p>
                                <p class="text-xs text-ink-400">{{ $variant->label }}</p>
                            </div>
                            <span class="text-xs text-clay-500 whitespace-nowrap">{{ $variant->stock }} left</span>
                        </a>
                    @empty
                        <p class="px-5 py-6 text-sm text-ink-400">Everything is well stocked.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>
