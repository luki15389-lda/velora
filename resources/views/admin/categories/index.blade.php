<x-layouts.admin title="Categories">
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <h1 class="font-editorial text-2xl hidden lg:block">Categories</h1>
        <a href="{{ route('admin.categories.create') }}" class="btn-primary">+ New category</a>
    </div>

    <div class="space-y-4">
        @forelse ($departments as $department)
            <div class="bg-white border border-sand-300">
                <div class="flex items-center justify-between px-5 py-4 border-b border-sand-200">
                    <div class="flex items-center gap-3">
                        <span class="font-medium">{{ $department->name }}</span>
                        @unless ($department->is_active)
                            <span class="px-2 py-0.5 text-[11px] bg-sand-200 text-ink-600">Hidden</span>
                        @endunless
                        <span class="text-xs text-ink-400">{{ $department->products_count }} active products</span>
                    </div>
                    <div class="flex items-center gap-3 text-xs">
                        <a href="{{ route('admin.categories.edit', $department) }}" class="text-ink-400 hover:text-ink-900 underline">Edit</a>
                        <form method="POST" action="{{ route('admin.categories.destroy', $department) }}"
                              onsubmit="return confirm('Delete this department?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-700 hover:underline">Delete</button>
                        </form>
                    </div>
                </div>

                @if ($department->children->isNotEmpty())
                    <div class="divide-y divide-sand-200">
                        @foreach ($department->children as $mid)
                            <div class="pl-8 pr-5 py-3 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="text-sm">{{ $mid->name }}</span>
                                    @unless ($mid->is_active)
                                        <span class="px-2 py-0.5 text-[11px] bg-sand-200 text-ink-600">Hidden</span>
                                    @endunless
                                    <span class="text-xs text-ink-400">{{ $mid->products_count }} active</span>
                                </div>
                                <div class="flex items-center gap-3 text-xs">
                                    <a href="{{ route('admin.categories.edit', $mid) }}" class="text-ink-400 hover:text-ink-900 underline">Edit</a>
                                    <form method="POST" action="{{ route('admin.categories.destroy', $mid) }}"
                                          onsubmit="return confirm('Delete this category?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-700 hover:underline">Delete</button>
                                    </form>
                                </div>
                            </div>

                            @foreach ($mid->children as $leaf)
                                <div class="pl-16 pr-5 py-2.5 flex items-center justify-between bg-sand-100/40">
                                    <div class="flex items-center gap-3">
                                        <span class="text-sm text-ink-600">{{ $leaf->name }}</span>
                                        @unless ($leaf->is_active)
                                            <span class="px-2 py-0.5 text-[11px] bg-sand-200 text-ink-600">Hidden</span>
                                        @endunless
                                        <span class="text-xs text-ink-400">{{ $leaf->products_count }} active</span>
                                    </div>
                                    <div class="flex items-center gap-3 text-xs">
                                        <a href="{{ route('admin.categories.edit', $leaf) }}" class="text-ink-400 hover:text-ink-900 underline">Edit</a>
                                        <form method="POST" action="{{ route('admin.categories.destroy', $leaf) }}"
                                              onsubmit="return confirm('Delete this category?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-700 hover:underline">Delete</button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                @endif
            </div>
        @empty
            <div class="bg-white border border-sand-300 py-16 text-center text-ink-400">No categories yet.</div>
        @endforelse
    </div>
</x-layouts.admin>
