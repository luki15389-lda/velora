<x-layouts.admin title="Promotions">
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <h1 class="font-editorial text-2xl hidden lg:block">Promotions</h1>
        <a href="{{ route('admin.promotions.create') }}" class="btn-primary">+ New coupon</a>
    </div>

    <div class="bg-white border border-sand-300 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-sand-100 text-left text-xs text-ink-400">
                    <tr>
                        <th class="px-5 py-3 font-medium">Code</th>
                        <th class="px-5 py-3 font-medium">Type</th>
                        <th class="px-5 py-3 font-medium">Value</th>
                        <th class="px-5 py-3 font-medium">Used</th>
                        <th class="px-5 py-3 font-medium">Window</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-sand-200">
                    @forelse ($coupons as $coupon)
                        <tr class="hover:bg-sand-100/60">
                            <td class="px-5 py-3">
                                <a href="{{ route('admin.promotions.edit', $coupon) }}" class="font-medium hover:underline">{{ $coupon->code }}</a>
                                @if ($coupon->description)
                                    <p class="text-xs text-ink-400">{{ $coupon->description }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-ink-400">
                                {{ $coupon->type->label() }}
                                @if ($coupon->category)
                                    <span class="block text-xs">{{ $coupon->category->name }} only</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                @if ($coupon->type->value === 'percentage')
                                    {{ rtrim(rtrim(number_format($coupon->value, 2), '0'), '.') }}%
                                @elseif ($coupon->type->value === 'fixed')
                                    {{ rupiah($coupon->value) }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-5 py-3 text-ink-400">
                                {{ $coupon->used_count }}{{ $coupon->usage_limit ? " / {$coupon->usage_limit}" : '' }}
                            </td>
                            <td class="px-5 py-3 text-xs text-ink-400">
                                @if ($coupon->starts_at || $coupon->ends_at)
                                    {{ $coupon->starts_at?->format('d M Y') ?? '—' }} – {{ $coupon->ends_at?->format('d M Y') ?? '—' }}
                                @else
                                    No expiry
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                <form method="POST" action="{{ route('admin.promotions.toggle', $coupon) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="px-2 py-1 text-xs {{ $coupon->is_active ? 'bg-ink-900 text-white' : 'bg-sand-200 text-ink-600' }}">
                                        {{ $coupon->is_active ? 'Active' : 'Paused' }}
                                    </button>
                                </form>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('admin.promotions.edit', $coupon) }}" class="text-xs text-ink-400 hover:text-ink-900 underline">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-16 text-center text-ink-400">No coupons yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">{{ $coupons->onEachSide(1)->links() }}</div>
</x-layouts.admin>
