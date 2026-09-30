<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-10"
     x-data="productGallery(@js($this->variantData))">

    <button type="button" onclick="history.length > 1 ? history.back() : (window.location = '{{ route('shop') }}')"
            class="inline-flex items-center gap-1.5 text-sm text-ink-400 hover:text-ink-900 mb-8">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
        </svg>
        Back
    </button>

    <div class="grid gap-10 lg:grid-cols-2">
        {{-- Gallery --}}
        <div class="space-y-4">
            <div class="aspect-[4/5] bg-sand-200 overflow-hidden">
                <template x-if="activeImage">
                    <img :src="activeImage.url" :alt="activeImage.alt" class="h-full w-full object-cover">
                </template>
            </div>

            <div class="grid grid-cols-4 gap-3">
                <template x-for="image in images" :key="image.id">
                    <button type="button" @click="image.color_id && selectColor(image.color_id)"
                            class="aspect-square overflow-hidden border-2 transition-colors"
                            :class="activeImage?.id === image.id ? 'border-ink-900' : 'border-transparent hover:border-sand-300'">
                        <img :src="image.url" :alt="image.alt" class="h-full w-full object-cover">
                    </button>
                </template>
            </div>
        </div>

        {{-- Buy box --}}
        <div>
            <p class="text-xs text-ink-400">{{ $product->sku }}</p>
            <h1 class="mt-2 font-editorial text-3xl sm:text-4xl">{{ $product->name }}</h1>

            @if ($product->rating_count > 0)
                <p class="mt-3 text-sm">
                    <span class="text-clay-500">{{ str_repeat('★', (int) round($product->rating_average)) }}</span>
                    <span class="text-ink-400 ml-2">{{ number_format($product->rating_average, 1) }} from {{ $product->rating_count }} reviews</span>
                </p>
            @endif

            <p class="mt-6 text-2xl" x-show="selectedVariant">
                <span x-text="selectedVariant ? formatRupiah(selectedVariant.price) : ''"></span>
                <template x-if="selectedVariant && selectedVariant.compare_at">
                    <span class="ml-3 text-base text-ink-400 line-through" x-text="formatRupiah(selectedVariant.compare_at)"></span>
                </template>
            </p>

            {{-- Colour --}}
            <template x-if="colors.length">
                <div class="mt-8">
                    <p class="text-sm mb-3">
                        Colour: <span class="text-ink-400" x-text="colorName(selectedColorId)"></span>
                    </p>
                    <div class="flex flex-wrap gap-3">
                        <template x-for="color in colors" :key="color.id">
                            <button type="button" @click="selectColor(color.id)"
                                    :title="color.name" :aria-label="color.name"
                                    class="h-9 w-9 rounded-full border-2 transition-all"
                                    :class="selectedColorId === color.id ? 'border-ink-900 scale-110' : 'border-sand-300'"
                                    :style="`background-color: ${color.hex}`"></button>
                        </template>
                    </div>
                </div>
            </template>

            {{-- Size --}}
            <template x-if="sizes.length">
                <div class="mt-8">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-sm">Size</p>
                        <button type="button" @click="$dispatch('open-size-guide')" class="text-xs text-ink-400 underline">Size guide</button>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="size in sizes" :key="size.id">
                            <button type="button" @click="selectSize(size.id)"
                                    :disabled="stockForSize(size.id) < 1"
                                    class="min-w-12 px-3 py-2 text-sm border transition-colors"
                                    :class="[
                                        selectedSizeId === size.id ? 'border-ink-900 bg-ink-900 text-white' : 'border-sand-300 hover:border-ink-900',
                                        stockForSize(size.id) < 1 ? 'opacity-35 line-through cursor-not-allowed' : '',
                                    ]"
                                    x-text="size.name"></button>
                        </template>
                    </div>
                </div>
            </template>

            {{-- Stock --}}
            <p class="mt-5 text-sm">
                <template x-if="!selectedVariant">
                    <span class="text-ink-400">Choose a colour and size.</span>
                </template>
                <template x-if="selectedVariant && selectedVariant.stock < 1">
                    <span class="text-red-700">Sold out in this combination.</span>
                </template>
                <template x-if="selectedVariant && selectedVariant.stock >= 1 && selectedVariant.stock <= 5">
                    <span class="text-clay-500" x-text="`Only ${selectedVariant.stock} left.`"></span>
                </template>
                <template x-if="selectedVariant && selectedVariant.stock > 5">
                    <span class="text-ink-400">In stock, ships within 1 business day.</span>
                </template>
            </p>

            {{-- Quantity + actions --}}
            <div class="mt-6 flex items-center gap-4">
                <div class="flex items-center border border-sand-300">
                    <button type="button" @click="decrementQuantity" class="px-4 py-3 hover:bg-sand-200" aria-label="Decrease quantity">−</button>
                    <span class="px-4 text-sm tabular-nums" x-text="quantity"></span>
                    <button type="button" @click="incrementQuantity" class="px-4 py-3 hover:bg-sand-200" aria-label="Increase quantity">+</button>
                </div>

                <livewire:wishlist.wishlist-toggle :product="$product" :key="'wish-'.$product->id" />
            </div>

            <div class="mt-4 flex flex-col sm:flex-row gap-3">
                <button type="button" @click="addToCart" :disabled="!selectedVariant || selectedVariant.stock < 1"
                        class="btn-primary flex-1">
                    Add to bag
                </button>

                <button type="button" @click="buyNow" :disabled="!selectedVariant || selectedVariant.stock < 1"
                        class="btn-outline flex-1">Buy now</button>
            </div>

            {{-- Detail accordions --}}
            <div class="mt-10 divide-y divide-sand-300 border-t border-sand-300" x-data="{ open: 'description' }">
                @foreach ([
                    'description' => ['Description', $product->description],
                    'material' => ['Material and care', trim(($product->material ?? '').'. '.($product->care_instructions ?? ''), '. ')],
                    'shipping' => ['Shipping', 'Standard 3–5 business days, Express 1–2, Same day within Jakarta. Free over '.rupiah((int) setting('shipping.free_threshold', 500000)).'.'],
                    'returns' => ['Returns', 'Send it back within 30 days, unworn with tags on. Underwear is excluded for hygiene reasons.'],
                ] as $key => [$label, $body])
                    <div class="py-4">
                        <button type="button" class="flex w-full items-center justify-between text-left text-sm font-medium"
                                @click="open = open === '{{ $key }}' ? null : '{{ $key }}'">
                            {{ $label }}
                            <span class="text-ink-400" x-text="open === '{{ $key }}' ? '−' : '+'"></span>
                        </button>
                        <p x-show="open === '{{ $key }}'" x-collapse class="mt-3 text-sm text-ink-400 leading-relaxed">{{ $body }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Reviews --}}
    <section class="mt-20">
        <h2 class="font-editorial text-2xl">Reviews</h2>

        @if ($reviews->isEmpty())
            <p class="mt-4 text-sm text-ink-400">No reviews yet. Only verified buyers can leave one.</p>
        @else
            <div class="mt-6 grid gap-6 md:grid-cols-2">
                @foreach ($reviews as $review)
                    <article class="border border-sand-300 bg-white p-5">
                        <p class="text-clay-500 text-sm">{{ str_repeat('★', $review->rating) }}</p>
                        <p class="mt-2 text-sm leading-relaxed">{{ $review->comment }}</p>
                        <p class="mt-3 text-xs text-ink-400">
                            {{ $review->user->name }}
                            @if ($review->size_purchased) · bought size {{ $review->size_purchased }} @endif
                            @if ($review->color_purchased) in {{ $review->color_purchased }} @endif
                        </p>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    {{-- Related --}}
    @if ($relatedProducts->isNotEmpty())
        <section class="mt-20">
            <h2 class="font-editorial text-2xl mb-8">You may also like</h2>
            <div class="grid gap-x-5 gap-y-10 grid-cols-2 lg:grid-cols-4">
                @foreach ($relatedProducts as $related)
                    <x-product-card :product="$related" />
                @endforeach
            </div>
        </section>
    @endif

    {{-- Size guide --}}
    <div x-data="{ open: false }" x-on:open-size-guide.window="open = true" x-show="open" x-cloak
         class="fixed inset-0 z-[70] flex items-center justify-center bg-ink-900/50 p-4">
        <div @click.outside="open = false" class="bg-white max-w-lg w-full p-6">
            <div class="flex items-start justify-between">
                <h3 class="font-editorial text-xl">Size guide</h3>
                <button type="button" @click="open = false" class="text-ink-400 hover:text-ink-900">Close</button>
            </div>
            <table class="mt-5 w-full text-sm">
                <thead class="text-left text-ink-400">
                    <tr><th class="py-2">Size</th><th class="py-2">Chest (cm)</th><th class="py-2">Length (cm)</th></tr>
                </thead>
                <tbody>
                    @foreach ([['XS', '92', '65'], ['S', '98', '68'], ['M', '104', '71'], ['L', '110', '74'], ['XL', '116', '76'], ['XXL', '122', '78']] as [$s, $c, $l])
                        <tr class="border-t border-sand-200"><td class="py-2">{{ $s }}</td><td>{{ $c }}</td><td>{{ $l }}</td></tr>
                    @endforeach
                </tbody>
            </table>
            <p class="mt-4 text-xs text-ink-400">Measured flat, garment not body. Allow 1–2cm tolerance.</p>
        </div>
    </div>
</div>
