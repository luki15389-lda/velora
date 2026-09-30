<x-layouts.admin title="Products">
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <h1 class="font-editorial text-2xl hidden lg:block">Products</h1>
        <a href="{{ route('admin.products.create') }}" class="btn-primary">+ New product</a>
    </div>

    <div class="lg:flex lg:gap-6 lg:items-start">
        {{-- Category picker --}}
        @php
            $expandedDepartmentId = null;
            if ($filters['category']) {
                $expandedDepartmentId = $departments->first(function ($d) use ($filters) {
                    return $d->id == $filters['category']
                        || $d->children->contains('id', $filters['category'])
                        || $d->children->contains(fn ($c) => $c->children->contains('id', $filters['category']));
                })?->id;
            }
        @endphp

        <aside class="text-sm mb-6 lg:mb-0 lg:w-52 lg:shrink-0" x-data="{ expanded: {{ $expandedDepartmentId ?? 'null' }} }">
            <p class="font-medium mb-3">Category</p>

            <form method="GET" id="category-form">
                <input type="hidden" name="q" value="{{ $filters['q'] }}">
                <input type="hidden" name="status" value="{{ $filters['status'] }}">
                <input type="hidden" name="category" id="category-input" value="{{ $filters['category'] }}">
            </form>

            <div class="space-y-1">
                <button type="button" onclick="document.getElementById('category-input').value=''; document.getElementById('category-form').submit();"
                        class="block text-left w-full py-1 {{ ! $filters['category'] ? 'text-ink-900 font-medium' : 'text-ink-400 hover:text-ink-900' }}">
                    All products
                </button>

                @foreach ($departments as $department)
                    @php($hasSubLevel = $department->children->contains(fn ($c) => $c->children->isNotEmpty()))

                    <div>
                        <button type="button"
                                @if ($hasSubLevel)
                                    @click="expanded = expanded === {{ $department->id }} ? null : {{ $department->id }}"
                                @else
                                    onclick="document.getElementById('category-input').value='{{ $department->id }}'; document.getElementById('category-form').submit();"
                                @endif
                                class="flex items-center justify-between w-full py-1 text-left {{ $filters['category'] == $department->id ? 'text-ink-900 font-medium' : 'text-ink-400 hover:text-ink-900' }}">
                            {{ $department->name }}
                            @if ($hasSubLevel)
                                <svg class="h-3.5 w-3.5 shrink-0 transition-transform" :class="expanded === {{ $department->id }} ? 'rotate-180' : ''"
                                     fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                                </svg>
                            @endif
                        </button>

                        @if ($hasSubLevel)
                            <div x-show="expanded === {{ $department->id }}" x-collapse class="pl-4 space-y-1 mt-1">
                                @foreach ($department->children as $child)
                                    <button type="button" onclick="document.getElementById('category-input').value='{{ $child->id }}'; document.getElementById('category-form').submit();"
                                            class="block text-left w-full py-1 text-xs {{ $filters['category'] == $child->id ? 'text-ink-900 font-medium' : 'text-ink-400 hover:text-ink-900' }}">
                                        {{ $child->name }}
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </aside>

        <div class="lg:flex-1 min-w-0">
            {{-- Search + status: both auto-apply, no Filter button --}}
            <form method="GET" class="bg-white border border-sand-300 p-4 mb-6 flex flex-wrap gap-3 items-end">
                <input type="hidden" name="category" value="{{ $filters['category'] }}">

                <div class="w-full sm:w-80">
                    <label for="q" class="block text-xs text-ink-400 mb-1">Search</label>
                    <input id="q" name="q" type="search" value="{{ $filters['q'] }}"
                           placeholder="Name, SKU, material…" class="field py-2"
                           onkeyup="clearTimeout(window.__searchTimer); window.__searchTimer = setTimeout(() => this.form.submit(), 500)">
                </div>

                <div class="w-40">
                    <label for="status" class="block text-xs text-ink-400 mb-1">Status</label>
                    <select id="status" name="status" class="field py-2" onchange="this.form.submit()">
                        <option value="">All</option>
                        @foreach (['draft' => 'Draft', 'active' => 'Active', 'archived' => 'Archived'] as $value => $label)
                            <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                @if ($filters['q'] || $filters['status'] || $filters['category'])
                    <a href="{{ route('admin.products.index') }}" class="text-sm text-ink-400 underline pb-2">Clear</a>
                @endif
            </form>

            <div class="bg-white border border-sand-300 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-sand-100 text-left text-xs text-ink-400">
                            <tr>
                                <th class="px-5 py-3 font-medium">Product</th>
                                <th class="px-5 py-3 font-medium">Category</th>
                                <th class="px-5 py-3 font-medium">Price</th>
                                <th class="px-5 py-3 font-medium">Variants</th>
                                <th class="px-5 py-3 font-medium">Stock</th>
                                <th class="px-5 py-3 font-medium">Status</th>
                                <th class="px-5 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-sand-200">
                            @forelse ($products as $product)
                                <tr class="hover:bg-sand-100/60">
                                    <td class="px-5 py-3">
                                        <a href="{{ route('admin.products.edit', $product) }}" class="flex items-center gap-3">
                                            <div class="h-12 w-9 bg-sand-200 shrink-0 overflow-hidden">
                                                @if ($product->primaryImage)
                                                    <img src="{{ $product->primaryImage->url }}" class="h-full w-full object-cover" alt="">
                                                @endif
                                            </div>
                                            <div class="min-w-0">
                                                <p class="font-medium truncate">{{ $product->name }}</p>
                                                <p class="text-xs text-ink-400">{{ $product->sku }}</p>
                                            </div>
                                        </a>
                                    </td>
                                    <td class="px-5 py-3 text-ink-400">{{ $product->category->name }}</td>
                                    <td class="px-5 py-3">
                                        {{ rupiah($product->effective_price) }}
                                        @if ($product->is_on_sale)
                                            <span class="block text-xs text-ink-400 line-through">{{ rupiah($product->price) }}</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-ink-400">{{ $product->variants_count }}</td>
                                    <td class="px-5 py-3">
                                        <span class="{{ $product->total_stock === 0 ? 'text-red-700' : '' }}">
                                            {{ $product->total_stock }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3">
                                        <span class="px-2 py-1 text-xs
                                            {{ match($product->status->value) {
                                                'active' => 'bg-ink-900 text-white',
                                                'draft' => 'bg-sand-200 text-ink-600',
                                                default => 'bg-red-100 text-red-800',
                                            } }}">
                                            {{ ucfirst($product->status->value) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-right">
                                        <a href="{{ route('admin.products.edit', $product) }}" class="text-xs text-ink-400 hover:text-ink-900 underline">Edit</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-5 py-16 text-center text-ink-400">No products match those filters.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-6">{{ $products->onEachSide(1)->links() }}</div>
        </div>
    </div>
</x-layouts.admin>
