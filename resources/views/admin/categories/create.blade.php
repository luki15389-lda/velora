<x-layouts.admin title="New category">
    <a href="{{ route('admin.categories.index') }}" class="text-xs text-ink-400 hover:text-ink-900">← Categories</a>
    <h1 class="font-editorial text-2xl mt-1 mb-6">New category</h1>

    @include('admin.categories._form')
</x-layouts.admin>
