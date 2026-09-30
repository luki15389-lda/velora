<x-layouts.admin title="Orders">
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <h1 class="font-editorial text-2xl hidden lg:block">Orders</h1>
    </div>

    {{-- Status tabs --}}
    <div class="flex flex-wrap gap-2 mb-6">
        <a href="{{ route('admin.orders.index') }}"
           class="chip {{ ! $statusFilter ? 'border-ink-900 text-ink-900 bg-white' : '' }}">
            All ({{ $statusCounts->sum() }})
        </a>
        @foreach (\App\Enums\OrderStatus::cases() as $status)
            <a href="{{ route('admin.orders.index', ['status' => $status->value]) }}"
               class="chip {{ $statusFilter === $status->value ? 'border-ink-900 text-ink-900 bg-white' : '' }}">
                {{ $status->label() }} ({{ $statusCounts[$status->value] ?? 0 }})
            </a>
        @endforeach
    </div>

    <div class="bg-white border border-sand-300 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-sand-100 text-left text-xs text-ink-400">
                    <tr>
                        <th class="px-5 py-3 font-medium">Order</th>
                        <th class="px-5 py-3 font-medium">Customer</th>
                        <th class="px-5 py-3 font-medium">Items</th>
                        <th class="px-5 py-3 font-medium">Total</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-sand-200">
                    @forelse ($orders as $order)
                        <tr class="hover:bg-sand-100/60">
                            <td class="px-5 py-3">
                                <a href="{{ route('admin.orders.show', $order) }}" class="font-medium hover:underline">{{ $order->number }}</a>
                                <p class="text-xs text-ink-400">{{ $order->created_at->diffForHumans() }}</p>
                            </td>
                            <td class="px-5 py-3">
                                {{ $order->user->name }}
                                <p class="text-xs text-ink-400">{{ $order->recipient_phone }}</p>
                            </td>
                            <td class="px-5 py-3 text-ink-400">{{ $order->items_count }}</td>
                            <td class="px-5 py-3">{{ rupiah($order->grand_total) }}</td>
                            <td class="px-5 py-3">
                                <span class="px-2 py-1 text-xs {{ $order->status->badgeClasses() }}">{{ $order->status->label() }}</span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('admin.orders.show', $order) }}" class="text-xs text-ink-400 hover:text-ink-900 underline">Manage</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-16 text-center text-ink-400">No orders yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">{{ $orders->onEachSide(1)->links() }}</div>
</x-layouts.admin>
