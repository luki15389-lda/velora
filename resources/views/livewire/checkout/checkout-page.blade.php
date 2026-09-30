<div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-12">
    <a href="{{ route('cart') }}" wire:navigate class="inline-flex items-center gap-1.5 text-sm text-ink-400 hover:text-ink-900 mb-4">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
        </svg>
        Back to bag
    </a>

    <h1 class="font-editorial text-3xl sm:text-4xl">Checkout</h1>

    @if ($this->cart->items->isEmpty())
        <div class="mt-10 border border-sand-300 bg-white py-20 text-center">
            <p class="font-editorial text-2xl">There is nothing to check out</p>
            <a href="{{ route('shop') }}" wire:navigate class="btn-primary mt-6">Browse the shop</a>
        </div>
    @else
        <div class="mt-8 grid gap-10 lg:grid-cols-[1fr_22rem] items-start">
            {{-- Delivery details --}}
            <div class="border border-sand-300 bg-white p-6">
                <h2 class="font-medium">Delivery details</h2>
                <p class="mt-1 text-sm text-ink-400">
                    We'll confirm everything with you on WhatsApp right after this — shipping and payment
                    get sorted out there.
                </p>

                <div class="mt-6 space-y-4">
                    <div>
                        <label for="recipient_name" class="block text-sm mb-1">Full name</label>
                        <input id="recipient_name" type="text" wire:model.blur="recipient_name" class="field">
                        @error('recipient_name')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="recipient_phone" class="block text-sm mb-1">WhatsApp / phone number</label>
                        <input id="recipient_phone" type="text" inputmode="numeric" wire:model.blur="recipient_phone" class="field" placeholder="081234567890 or 6281234567890"
                               oninput="this.value = this.value.replace(/[^0-9+]/g, '')">
                        @error('recipient_phone')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                    </div>

                    <div x-data="addressLocator">
                        <div class="flex items-center justify-between mb-1">
                            <label for="street_address" class="block text-sm">Delivery address</label>
                            <button type="button" @click="locate" :disabled="locating"
                                    class="inline-flex items-center gap-1 text-xs text-ink-600 hover:text-ink-900 disabled:opacity-50">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21c-4-4-7-7.5-7-11a7 7 0 1 1 14 0c0 3.5-3 7-7 11Z" />
                                    <circle cx="12" cy="10" r="2.5" stroke-linecap="round" />
                                </svg>
                                <span x-text="locating ? 'Locating…' : 'Use my current location'"></span>
                            </button>
                        </div>
                        <textarea id="street_address" wire:model.blur="street_address" rows="4" class="field"
                                  placeholder="Street, number, building, city, postal code — everything a courier needs"></textarea>
                        <p x-show="error" x-text="error" class="mt-1 text-xs text-red-700"></p>
                        @error('street_address')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="customer_note" class="block text-sm mb-1">Note (optional)</label>
                        <textarea id="customer_note" wire:model.blur="customer_note" rows="2" class="field"
                                  placeholder="Gate code, landmark, preferred delivery time…"></textarea>
                    </div>

                    <label class="flex items-center gap-2 text-sm text-ink-400">
                        <input type="checkbox" wire:model="saveAddress" class="border-sand-300 text-ink-900 focus:ring-0">
                        Save this address to my account
                    </label>
                </div>

                {{-- Order lines --}}
                <div class="mt-8 pt-6 border-t border-sand-200">
                    <h3 class="text-sm font-medium mb-4">Your order</h3>
                    <div class="divide-y divide-sand-200">
                        @foreach ($this->cart->items as $item)
                            <div class="py-3 flex gap-3">
                                <div class="w-14 shrink-0 aspect-[4/5] bg-sand-200 overflow-hidden">
                                    @if ($item->variant->product->primaryImage)
                                        <img src="{{ $item->variant->product->primaryImage->url }}" alt="" class="h-full w-full object-cover">
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0 flex justify-between gap-3 text-sm">
                                    <div>
                                        <p>{{ $item->variant->product->name }}</p>
                                        <p class="text-xs text-ink-400">{{ $item->variant->label }} × {{ $item->quantity }}</p>
                                    </div>
                                    <p class="whitespace-nowrap">{{ rupiah($item->line_total) }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <button type="button" wire:click="placeOrder" wire:loading.attr="disabled" class="btn-primary w-full mt-6">
                    <span wire:loading.remove wire:target="placeOrder">Place order & message on WhatsApp</span>
                    <span wire:loading wire:target="placeOrder">Placing order…</span>
                </button>
            </div>

            {{-- Summary --}}
            <aside class="border border-sand-300 bg-white p-6 space-y-4 lg:sticky lg:top-24">
                <h2 class="font-medium">Summary</h2>

                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-ink-400">Subtotal</dt><dd>{{ rupiah($this->subtotal) }}</dd></div>

                    @if ($this->cart->coupon)
                        <div class="flex justify-between">
                            <dt class="text-ink-400">{{ $this->cart->coupon->code }}</dt>
                            <dd class="text-clay-500">−{{ rupiah($this->totals['discount']) }}</dd>
                        </div>
                    @endif

                    <div class="flex justify-between">
                        <dt class="text-ink-400">Shipping</dt>
                        <dd>{{ $this->totals['shipping'] > 0 ? rupiah($this->totals['shipping']) : 'Discussed on WhatsApp' }}</dd>
                    </div>

                    <div class="flex justify-between border-t border-sand-200 pt-3 text-base">
                        <dt>Total</dt><dd>{{ rupiah($this->totals['grand_total']) }}</dd>
                    </div>
                </dl>

                <p class="text-xs text-ink-400">
                    Stock is checked again the moment you place the order, so nothing oversells.
                </p>
            </aside>
        </div>
    @endif
</div>