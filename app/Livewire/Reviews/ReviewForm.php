<?php

namespace App\Livewire\Reviews;

use App\Enums\ReviewStatus;
use App\Models\OrderItem;
use App\Models\Review;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class ReviewForm extends Component
{
    use WithFileUploads;

    public OrderItem $item;

    public int $rating = 5;

    public string $comment = '';

    public $photo;

    public bool $submitted = false;

    public function mount(): void
    {
        $this->submitted = $this->item->is_reviewed;
    }

    public function save(): void
    {
        // The policy is the gate: only the buyer, only on a delivered line, only once.
        $this->authorize('createFor', [Review::class, $this->item]);

        $this->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:10|max:1000',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $path = $this->photo?->store('reviews', 'public');

        Review::create([
            'product_id' => $this->item->product_id,
            'user_id' => auth()->id(),
            'order_item_id' => $this->item->id,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'photo_path' => $path,
            'size_purchased' => $this->item->size_name,
            'color_purchased' => $this->item->color_name,
            'status' => setting('reviews.auto_approve') ? ReviewStatus::Approved : ReviewStatus::Pending,
        ]);

        $this->item->forceFill(['is_reviewed' => true])->save();

        $this->submitted = true;
        $this->dispatch('notify', message: 'Thanks. Your review is queued for moderation.', tone: 'success');
    }

    public function render(): View
    {
        return view('livewire.reviews.review-form');
    }
}
