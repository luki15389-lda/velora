<x-layouts.admin :title="$product->name">
    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="{{ route('admin.products.index') }}" class="text-xs text-ink-400 hover:text-ink-900">← Products</a>
            <h1 class="font-editorial text-2xl mt-1">{{ $product->name }}</h1>
        </div>
        <a href="{{ route('products.show', $product) }}" target="_blank" class="text-sm text-ink-400 hover:text-ink-900">View on site ↗</a>
    </div>

    @include('admin.products._form')
</x-layouts.admin>
