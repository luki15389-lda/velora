<?php

namespace App\Models;

use App\Enums\ReviewStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'user_id', 'order_item_id', 'rating', 'comment',
        'photo_path', 'size_purchased', 'color_purchased', 'status', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'status' => ReviewStatus::class,
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_path ? Storage::url($this->photo_path) : null;
    }

    public function scopeApproved($query)
    {
        return $query->where('status', ReviewStatus::Approved->value);
    }

    protected static function booted(): void
    {
        // Any change to a review can move the product's average, so recompute on write.
        static::saved(fn (Review $review) => Product::find($review->product_id)?->recalculateRating());
        static::deleted(fn (Review $review) => Product::find($review->product_id)?->recalculateRating());
    }
}
