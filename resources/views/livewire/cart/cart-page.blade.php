<div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="font-editorial text-3xl sm:text-4xl">Your bag</h1>

    @if ($this->cart->items->isEmpty())
        <div class="mt-10 border border-sand-300 bg-white py-20 text-center">
            <p class="font-editorial text-2xl">Your bag is empty</p>
            <p class="mt-2 text-sm text-ink-400">Nothing saved here yet. Have a look at what is new.</p>
            <a href="{{ route('shop') }}" wire:navigate class="btn-primary mt-6">Start shopping</a>
        </div>
    @else
        <div class="mt-10 grid gap-10 lg:grid-cols-[1fr_22rem] items-start">
            {{-- Lines --}}
            <div class="divide-y divide-sand-300 border-y border-sand-300">
                @foreach ($this->cart->items as $item)
                    <div wire:key="line-{{ $item->id }}" class="py-6 flex gap-4">
                        <a href="{{ route('products.show', $item->variant->product) }}" wire:navigate
                           class="w-24 sm:w-28 shrink-0 aspect-[4/5] bg-sand-200 overflow-hidden">
                            @if ($item->variant->product->primaryImage)
                                <img src="{{ $item->variant->product->primaryImage->url }}" alt=""
                                     class="h-full w-full object-cover">
                            @endif
                        </a>

                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between gap-4">
                                <div class="min-w-0">
                                    <a href="{{ route('products.show', $item->variant->product) }}" wire:navigate
                                       class="text-sm font-medium hover:underline">{{ $item->variant->product->name }}</a>
                                    <p class="mt-1 text-xs text-ink-400">{{ $item->variant->label }}</p>
                                    <p class="mt-1 text-xs text-ink-400">{{ $item->variant->sku }}</p>

                                    @if ($item->exceedsStock())
                                        <p class="mt-2 text-xs text-red-700">
                                            Only {{ $item->variant->stock }} left — lower the quantity to continue.
                                        </p>
                                    @endif
                                </div>

                                <p class="text-sm whitespace-nowrap">{{ rupiah($item->line_total) }}</p>
                            </div>

                            <div class="mt-4 flex items-center justify-between">
                                <div class="flex items-center border border-sand-300">
                                    <button type="button" wire:click="decrement({{ $item->id }})"
                                            class="px-3 py-2 hover:bg-sand-200" aria-label="Decrease quantity">−</button>
                                    <span class="px-3 text-sm tabular-nums">{{ $item->quantity }}</span>
                                    <button type="button" wire:click="increment({{ $item->id }})"
                                            class="px-3 py-2 hover:bg-sand-200" aria-label="Increase quantity">+</button>
                                </div>

                                <button type="button" wire:click="remove({{ $item->id }})"
                                        class="text-xs text-ink-400 underline hover:text-ink-900">Remove</button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Summary --}}
            <aside class="border border-sand-300 bg-white p-6 space-y-5 lg:sticky lg:top-24">
                <h2 class="font-medium">Order summary</h2>

                @if ($this->cart->coupon)
                    <div class="flex items-center justify-between text-sm bg-sand-100 px-3 py-2">
                        <span>{{ $this->cart->coupon->code }}</span>
                        <button type="button" wire:click="removeCoupon" class="text-xs text-ink-400 underline">Remove</button>
                    </div>
                @else
                    <div>
                        <label for="coupon" class="block text-sm mb-1">Promo code</label>
                        <div class="flex gap-2">
                            <input id="coupon" type="text" wire:model="couponCode" wire:keydown.enter="applyCoupon"
                                   placeholder="VELORA10" class="field py-2">
                            <button type="button" wire:click="applyCoupon" class="btn-outline py-2 px-4 whitespace-nowrap">Apply</button>
                        </div>
                        @if ($couponError)
                            <p class="mt-2 text-xs text-red-700">{{ $couponError }}</p>
                        @endif
                    </div>
                @endif

                <dl class="space-y-2 text-sm border-t border-sand-200 pt-4">
                    <div class="flex justify-between"><dt class="text-ink-400">Subtotal</dt><dd>{{ rupiah($this->subtotal) }}</dd></div>

                    @if ($this->totals['discount'] > 0)
                        <div class="flex justify-between"><dt class="text-ink-400">Discount</dt><dd class="text-clay-500">−{{ rupiah($this->totals['discount']) }}</dd></div>
                    @endif

                    <div class="flex justify-between">
                        <dt class="text-ink-400">Shipping (standard)</dt>
                        <dd>{{ $this->totals['shipping'] > 0 ? rupiah($this->totals['shipping']) : 'Free' }}</dd>
                    </div>

                    <div class="flex justify-between border-t border-sand-200 pt-3 text-base">
                        <dt>Total</dt><dd>{{ rupiah($this->totals['grand_total']) }}</dd>
                    </div>
                </dl>

                <p class="text-xs text-ink-400">Shipping is confirmed at checkout once you pick a method.</p>

                @auth
                    <a href="{{ route('checkout') }}" wire:navigate class="btn-primary w-full">Checkout</a>
                @else
                    <a href="{{ route('login') }}" wire:navigate class="btn-primary w-full">Sign in to check out</a>
                    <p class="text-xs text-ink-400 text-center">Your bag carries over after you sign in.</p>
                @endauth

                <a href="{{ route('shop') }}" wire:navigate class="block text-center text-sm text-ink-400 hover:text-ink-900">Keep shopping</a>
            </aside>
        </div>
    @endif
</div>
