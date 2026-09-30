<?php

namespace App\Policies;

use App\Models\OrderItem;
use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    public function moderate(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Review $review): bool
    {
        return $review->user_id === $user->id || $user->isAdmin();
    }

    public function delete(User $user, Review $review): bool
    {
        return $review->user_id === $user->id || $user->isAdmin();
    }

    /** A review only exists on the back of a delivered order line. */
    public function createFor(User $user, OrderItem $item): bool
    {
        return $item->order->user_id === $user->id
            && $item->order->status->value === 'delivered'
            && ! $item->is_reviewed;
    }
}
