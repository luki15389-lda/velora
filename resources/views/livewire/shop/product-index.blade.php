<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="font-editorial text-3xl sm:text-4xl">
                {{ $this->activeCategory?->name ?? 'Everything' }}
            </h1>
            @if ($this->activeCategory?->parent)
                <p class="mt-1 text-sm text-ink-400">{{ $this->activeCategory->breadcrumb }}</p>
            @endif
        </div>

        <div class="flex items-center gap-3">
            <label for="sort" class="sr-only">Sort products</label>
            <select id="sort" wire:model.live="sort" class="field py-2 w-auto">
                <option value="newest">Newest</option>
                <option value="price_asc">Price: low to high</option>
                <option value="price_desc">Price: high to low</option>
                <option value="best_selling">Best selling</option>
                <option value="top_rated">Highest rated</option>
            </select>

            <button type="button" wire:click="$toggle('filtersOpen')" class="lg:hidden btn-outline py-2 px-4">
                Categories
            </button>
        </div>
    </div>

    <div class="mt-4">
        <label for="shop-search" class="sr-only">Search</label>
        <input id="shop-search" type="search" wire:model.live.debounce.400ms="search"
               placeholder="Search by product name, SKU or category" class="field">
    </div>

    <div class="mt-8 grid gap-10 lg:grid-cols-[16rem_1fr]">
        {{-- Category browser — expand/collapse and selection are both plain
             Livewire state, so there's no client/server sync to get wrong. --}}
        <aside class="{{ $filtersOpen ? 'block' : 'hidden' }} lg:block text-sm">
            @if ($category)
                <button type="button" wire:click="clearFilters" class="text-ink-400 underline hover:text-ink-900 mb-4 block">
                    Clear filter
                </button>
            @endif

            <p class="font-medium mb-3">Category</p>
            <div class="space-y-1">
                <button type="button" wire:click="$set('category', '')"
                        class="block text-left w-full py-1 {{ ! $category ? 'text-ink-900 font-medium' : 'text-ink-400 hover:text-ink-900' }}">
                    Everything
                </button>

                @foreach ($departments as $department)
                    @php($hasSubLevel = $department->children->contains(fn ($c) => $c->children->isNotEmpty()))

                    <div>
                        <button type="button"
                                wire:click="{{ $hasSubLevel ? 'toggleDepartment('.$department->id.')' : "selectCategory('{$department->slug}')" }}"
                                class="flex items-center justify-between w-full py-1 text-left {{ $category === $department->slug ? 'text-ink-900 font-medium' : 'text-ink-400 hover:text-ink-900' }}">
                            {{ $department->name }}
                            @if ($hasSubLevel)
                                <svg class="h-3.5 w-3.5 shrink-0 transition-transform {{ $expandedDepartment === $department->id ? 'rotate-180' : '' }}"
                                     fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                                </svg>
                            @endif
                        </button>

                        @if ($hasSubLevel && $expandedDepartment === $department->id)
                            <div class="pl-4 space-y-1 mt-1">
                                @foreach ($department->children as $child)
                                    <button type="button" wire:click="selectCategory('{{ $child->slug }}')"
                                            class="block text-left w-full py-1 text-xs {{ $category === $child->slug ? 'text-ink-900 font-medium' : 'text-ink-400 hover:text-ink-900' }}">
                                        {{ $child->name }}
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </aside>

        {{-- Results --}}
        <div>
            <div wire:loading.delay class="mb-4 text-sm text-ink-400">Updating results…</div>

            @if ($products->isEmpty())
                <div class="border border-sand-300 bg-white py-20 text-center">
                    <p class="font-editorial text-2xl">Nothing matches</p>
                    <p class="mt-2 text-sm text-ink-400">Try a different search term or category.</p>
                    <button type="button" wire:click="clearFilters" class="btn-outline mt-6">Clear filters</button>
                </div>
            @else
                <p class="text-sm text-ink-400 mb-6">{{ $products->total() }} {{ \Illuminate\Support\Str::plural('piece', $products->total()) }}</p>

                <div class="grid gap-x-5 gap-y-10 grid-cols-2 lg:grid-cols-3">
                    @foreach ($products as $product)
                        <div wire:key="product-{{ $product->id }}">
                            <x-product-card :product="$product" />
                        </div>
                    @endforeach
                </div>

                <div class="mt-12">{{ $products->onEachSide(1)->links() }}</div>
            @endif
        </div>
    </div>
</div>
