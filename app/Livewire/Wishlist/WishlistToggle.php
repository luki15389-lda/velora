<?php

namespace App\Livewire\Wishlist;

use App\Models\Product;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class WishlistToggle extends Component
{
    public Product $product;

    public bool $saved = false;

    public function mount(): void
    {
        $this->saved = $this->lookup() !== null;
    }

    public function toggle()
    {
        if (! auth()->check()) {
            return $this->redirectRoute('login', navigate: true);
        }

        $existing = $this->lookup();

        if ($existing) {
            $existing->delete();
            $this->saved = false;
            $this->dispatch('wishlist-updated');
            $this->dispatch('notify', message: 'Removed from wishlist.', tone: 'info');

            return null;
        }

        $wishlist = Wishlist::firstOrCreate(['user_id' => auth()->id()], ['name' => 'My wishlist']);

        WishlistItem::firstOrCreate([
            'wishlist_id' => $wishlist->id,
            'product_id' => $this->product->id,
        ]);

        $this->saved = true;
        $this->dispatch('wishlist-updated');
        $this->dispatch('notify', message: 'Saved to wishlist.', tone: 'success');

        return null;
    }

    public function render(): View
    {
        return view('livewire.wishlist.wishlist-toggle');
    }

    private function lookup(): ?WishlistItem
    {
        if (! auth()->check()) {
            return null;
        }

        return WishlistItem::query()
            ->whereHas('wishlist', fn ($q) => $q->where('user_id', auth()->id()))
            ->where('product_id', $this->product->id)
            ->first();
    }
}
