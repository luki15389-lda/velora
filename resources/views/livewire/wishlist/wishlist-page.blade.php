<div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="font-editorial text-3xl sm:text-4xl">Wishlist</h1>

    @if ($this->items->isEmpty())
        <div class="mt-10 border border-sand-300 bg-white py-20 text-center">
            <p class="font-editorial text-2xl">Nothing saved yet</p>
            <p class="mt-2 text-sm text-ink-400">Tap the heart on any product to keep it here.</p>
            <a href="{{ route('shop') }}" wire:navigate class="btn-primary mt-6">Browse the shop</a>
        </div>
    @else
        <div class="mt-10 grid gap-x-5 gap-y-10 grid-cols-2 lg:grid-cols-4">
            @foreach ($this->items as $item)
                <div wire:key="wish-{{ $item->id }}">
                    <x-product-card :product="$item->product" />

                    <div class="mt-3 flex gap-2">
                        <button type="button" wire:click="moveToCart({{ $item->id }})"
                                class="btn-outline py-2 px-3 text-xs flex-1">Move to bag</button>
                        <button type="button" wire:click="remove({{ $item->id }})"
                                class="text-xs text-ink-400 underline px-2">Remove</button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
