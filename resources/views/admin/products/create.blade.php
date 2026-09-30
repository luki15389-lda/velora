<x-layouts.admin title="New product">
    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="{{ route('admin.products.index') }}" class="text-xs text-ink-400 hover:text-ink-900">← Products</a>
            <h1 class="font-editorial text-2xl mt-1">New product</h1>
        </div>
    </div>

    @include('admin.products._form')
</x-layouts.admin>
