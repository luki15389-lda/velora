<x-layouts.admin :title="$coupon->code">
    <a href="{{ route('admin.promotions.index') }}" class="text-xs text-ink-400 hover:text-ink-900">← Promotions</a>
    <h1 class="font-editorial text-2xl mt-1 mb-6">{{ $coupon->code }}</h1>

    @include('admin.promotions._form')

    <form method="POST" action="{{ route('admin.promotions.destroy', $coupon) }}" class="max-w-2xl mt-3"
          onsubmit="return confirm('Delete this coupon? Past orders that used it keep their record.')">
        @csrf @method('DELETE')
        <button type="submit" class="text-sm text-red-700 hover:underline">Delete coupon</button>
    </form>
</x-layouts.admin>
