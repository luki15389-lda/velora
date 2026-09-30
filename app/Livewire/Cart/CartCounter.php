<?php

namespace App\Livewire\Cart;

use App\Services\CartService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class CartCounter extends Component
{
    public int $count = 0;

    public function mount(CartService $carts): void
    {
        $this->count = $carts->itemCount();
    }

    #[On('cart-updated')]
    public function refreshCount(CartService $carts): void
    {
        $this->count = $carts->itemCount();
    }

    public function render(): View
    {
        return view('livewire.cart.cart-counter');
    }
}
