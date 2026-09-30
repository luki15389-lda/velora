<?php

namespace App\Livewire\Shop;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ProductShow extends Component
{
    public Product $product;

    /**
     * Related products and reviews are loaded once here rather than recomputed
     * on every request — colour/size selection is handled entirely client-side
     * (see the productGallery Alpine component in resources/js/app.js), so the
     * only server round trips left on this page are Add to Cart, Buy Now, and
     * the wishlist toggle. Recomputing these two queries on every one of those
     * would be wasted work since neither ever changes on this page view.
     */
    public Collection $relatedProducts;

    public Collection $reviews;

    public function mount(Product $product): void
    {
        abort_unless($product->status->value === 'active', 404);

        $this->product = $product->load([
            'category.parent', 'images.color',
            'variants.color', 'variants.size',
        ]);

        $product->increment('views');

        $this->relatedProducts = Product::active()
            ->where('category_id', $product->category_id)
            ->whereKeyNot($product->id)
            ->with(['primaryImage', 'category'])
            ->inRandomOrder()
            ->take(4)
            ->get();

        $this->reviews = $product->approvedReviews()->with('user')->latest()->take(8)->get();
    }

    /** Payload the Alpine component uses to do colour/size/stock logic in the browser. */
    #[Computed]
    public function variantData(): array
    {
        return [
            'variants' => $this->product->variants->map(fn (ProductVariant $v) => [
                'id' => $v->id,
                'color_id' => $v->color_id,
                'size_id' => $v->size_id,
                'stock' => $v->stock,
                'price' => $v->effective_price,
                'compare_at' => $v->compare_at_price,
            ])->values(),
            'colors' => $this->product->variants->pluck('color')->filter()->unique('id')->sortBy('position')
                ->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'hex' => $c->hex])->values(),
            'sizes' => $this->product->variants->pluck('size')->filter()->unique('id')->sortBy('position')
                ->map(fn ($s) => ['id' => $s->id, 'name' => $s->name])->values(),
            'images' => $this->product->images->sortBy('position')
                ->map(fn ($i) => ['id' => $i->id, 'url' => $i->url, 'alt' => $i->alt, 'color_id' => $i->color_id])->values(),
        ];
    }

    public function addToCart(int $variantId, int $quantity, CartService $carts): void
    {
        $variant = $this->product->variants->find($variantId);

        if (! $variant) {
            $this->dispatch('notify', message: 'Pick a colour and size first.', tone: 'error');

            return;
        }

        try {
            $carts->add($variant, max(1, $quantity));
        } catch (\RuntimeException $e) {
            $this->dispatch('notify', message: $e->getMessage(), tone: 'error');

            return;
        }

        $this->dispatch('cart-updated');
        $this->dispatch('notify', message: "{$this->product->name} added to your bag.", tone: 'success');
    }

    public function buyNow(int $variantId, int $quantity, CartService $carts)
    {
        $variant = $this->product->variants->find($variantId);

        if (! $variant || $variant->stock < 1) {
            $this->dispatch('notify', message: 'Pick a colour and size first.', tone: 'error');

            return null;
        }

        $this->addToCart($variantId, $quantity, $carts);

        return $this->redirectRoute('checkout', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.shop.product-show')->title($this->product->name);
    }
}
