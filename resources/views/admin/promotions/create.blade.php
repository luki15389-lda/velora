<x-layouts.admin title="New coupon">
    <a href="{{ route('admin.promotions.index') }}" class="text-xs text-ink-400 hover:text-ink-900">← Promotions</a>
    <h1 class="font-editorial text-2xl mt-1 mb-6">New coupon</h1>

    @include('admin.promotions._form')
</x-layouts.admin>
