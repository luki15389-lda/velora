<?php

namespace App\Livewire\Checkout;

use App\Enums\PaymentMethod;
use App\Enums\ShippingMethod;
use App\Models\Address;
use App\Models\CartItem;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Deliberately one screen: name, phone, address, a look at what's in the bag,
 * then "Place order & message on WhatsApp." Shipping method and payment
 * method aren't asked here — those get sorted out in the WhatsApp
 * conversation that follows, which is how this store actually operates.
 */
#[Layout('components.layouts.app')]
class CheckoutPage extends Component
{
    public string $recipient_name = '';

    public string $recipient_phone = '';

    public string $street_address = '';

    public string $customer_note = '';

    public bool $saveAddress = true;

    public function mount(): void
    {
        $this->recipient_name = auth()->user()->name;
        $this->recipient_phone = auth()->user()->phone ?? '';

        $default = Address::where('user_id', auth()->id())->orderByDesc('is_default')->first();

        if ($default) {
            $this->recipient_name = $default->recipient_name;
            $this->recipient_phone = $default->phone;
            $this->street_address = $default->street_address;
        }
    }

    #[Computed]
    public function cart()
    {
        return app(CartService::class)->current()->load([
            'items.variant.product.primaryImage',
            'items.variant.color',
            'items.variant.size',
            'coupon',
        ]);
    }

    #[Computed]
    public function subtotal(): int
    {
        return (int) $this->cart()->items->sum(fn (CartItem $item) => $item->line_total);
    }

    #[Computed]
    public function totals(): array
    {
        return app(CheckoutService::class)->totals(
            $this->subtotal(),
            ShippingMethod::Standard,
            $this->cart()->coupon,
        );
    }

    public function placeOrder(CheckoutService $checkout, CartService $carts)
    {
        $this->validate([
            'recipient_name' => 'required|string|max:120',
            'recipient_phone' => ['required', 'string', 'regex:/^(?:\+?62|0)8[0-9]{7,11}$/'],
            'street_address' => 'required|string|max:1000',
            'customer_note' => 'nullable|string|max:500',
        ], [
            'recipient_phone.regex' => 'Enter a valid Indonesian mobile number — start with 08 (e.g. 081234567890) or 62 (e.g. 6281234567890), not a mix of both.',
        ]);

        // Store one canonical shape (62-prefixed digits) regardless of
        // whether the customer typed 08... or 62.../+62... — this is also
        // what makes the WhatsApp links work correctly later.
        $this->recipient_phone = normalize_id_phone($this->recipient_phone);

        $cart = $carts->current();

        if ($cart->items()->doesntExist()) {
            $this->dispatch('notify', message: 'Your bag is empty.', tone: 'error');

            return null;
        }

        $address = [
            'recipient_name' => $this->recipient_name,
            'recipient_phone' => $this->recipient_phone,
            'province' => '',
            'city' => '',
            'district' => '',
            'postal_code' => '',
            'street_address' => $this->street_address,
            'customer_note' => $this->customer_note ?: null,
        ];

        if ($this->saveAddress) {
            // $address above is shaped for Order::create() (recipient_phone,
            // customer_note). The addresses table uses different column
            // names (phone, notes), so it's mapped separately here.
            Address::updateOrCreate(
                ['user_id' => auth()->id(), 'label' => 'Home'],
                [
                    'recipient_name' => $this->recipient_name,
                    'phone' => $this->recipient_phone,
                    'province' => '',
                    'city' => '',
                    'district' => '',
                    'postal_code' => '',
                    'street_address' => $this->street_address,
                    'notes' => $this->customer_note ?: null,
                    'is_default' => true,
                ],
            );
        }

        try {
            $order = $checkout->place(
                auth()->user(),
                $cart,
                $address,
                ShippingMethod::Standard,
                PaymentMethod::Cod,
                $cart->coupon,
            );
        } catch (\RuntimeException $e) {
            $this->dispatch('notify', message: $e->getMessage(), tone: 'error');

            return null;
        }

        return $this->redirectRoute('orders.success', $order, navigate: false);
    }

    public function render(): View
    {
        return view('livewire.checkout.checkout-page')->title('Checkout');
    }
}