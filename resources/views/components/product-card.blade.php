@props(['product'])

<article class="group">
    <a href="{{ route('products.show', $product) }}" wire:navigate class="block">
        <div class="relative overflow-hidden bg-sand-200 aspect-[4/5]">
            @if ($product->primaryImage)
                <img src="{{ $product->primaryImage->url }}" alt="{{ $product->primaryImage->alt }}"
                     loading="lazy"
                     class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-[1.03]">
            @else
                <div class="flex h-full items-center justify-center text-ink-400 text-xs tracking-widest">VELORA</div>
            @endif

            @if ($product->is_on_sale)
                <span class="absolute left-3 top-3 bg-ink-900 text-white text-[11px] px-2 py-1">
                    −{{ $product->discount_percent }}%
                </span>
            @endif
        </div>

        <div class="pt-3 space-y-1">
            <p class="text-xs text-ink-400">{{ $product->category->name }}</p>
            <h3 class="text-sm font-medium leading-snug">{{ $product->name }}</h3>

            <p class="text-sm">
                <span class="{{ $product->is_on_sale ? 'text-clay-500' : '' }}">{{ rupiah($product->effective_price) }}</span>
                @if ($product->is_on_sale)
                    <span class="ml-2 text-ink-400 line-through text-xs">{{ rupiah($product->price) }}</span>
                @endif
            </p>

            @if ($product->rating_count > 0)
                <p class="text-xs text-ink-400">{{ number_format($product->rating_average, 1) }} ★ ({{ $product->rating_count }})</p>
            @endif
        </div>
    </a>
</article>
