<x-layouts.admin :title="$category->name">
    <a href="{{ route('admin.categories.index') }}" class="text-xs text-ink-400 hover:text-ink-900">← Categories</a>
    <h1 class="font-editorial text-2xl mt-1 mb-6">{{ $category->name }}</h1>

    @include('admin.categories._form')
</x-layouts.admin>
