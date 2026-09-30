<?php

namespace App\Livewire\Wishlist;

use App\Models\Wishlist;
use App\Models\WishlistItem;
use App\Services\CartService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class WishlistPage extends Component
{
    #[Computed]
    public function items()
    {
        $wishlist = Wishlist::firstOrCreate(['user_id' => auth()->id()], ['name' => 'My wishlist']);

        return $wishlist->items()
            ->with(['product.primaryImage', 'product.category', 'product.variants'])
            ->latest()
            ->get();
    }

    public function remove(int $itemId): void
    {
        $item = $this->items()->firstWhere('id', $itemId);

        $item?->delete();

        unset($this->items);

        $this->dispatch('wishlist-updated');
        $this->dispatch('notify', message: 'Removed from wishlist.', tone: 'info');
    }

    /** Adds the first in-stock variant, since the wishlist stores products not variants. */
    public function moveToCart(int $itemId, CartService $carts): void
    {
        $item = $this->items()->firstWhere('id', $itemId);

        if (! $item) {
            return;
        }

        $variant = $item->product->variants->firstWhere('stock', '>', 0);

        if (! $variant) {
            $this->dispatch('notify', message: 'That piece is sold out in every size.', tone: 'error');

            return;
        }

        try {
            $carts->add($variant, 1);
        } catch (\RuntimeException $e) {
            $this->dispatch('notify', message: $e->getMessage(), tone: 'error');

            return;
        }

        $item->delete();
        unset($this->items);

        $this->dispatch('cart-updated');
        $this->dispatch('wishlist-updated');
        $this->dispatch('notify', message: 'Moved to your bag.', tone: 'success');
    }

    public function render(): View
    {
        return view('livewire.wishlist.wishlist-page')->title('Wishlist');
    }
}
