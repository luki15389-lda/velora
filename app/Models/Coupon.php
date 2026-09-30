<?php

namespace App\Models;

use App\Enums\CouponType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'description', 'category_id', 'type', 'value', 'minimum_purchase', 'maximum_discount',
        'usage_limit', 'usage_limit_per_user', 'used_count', 'starts_at', 'ends_at', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type' => CouponType::class,
            'value' => 'float',
            'minimum_purchase' => 'integer',
            'maximum_discount' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** True when the coupon has no category restriction, or the cart touches its branch. */
    public function coversCategories(\Illuminate\Support\Collection $cartCategoryIds): bool
    {
        if (! $this->category_id) {
            return true;
        }

        $branch = $this->category?->branchIds() ?? [];

        return $cartCategoryIds->intersect($branch)->isNotEmpty();
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function isWithinWindow(): bool
    {
        $now = now();

        if ($this->starts_at && $now->lt($this->starts_at)) {
            return false;
        }

        if ($this->ends_at && $now->gt($this->ends_at)) {
            return false;
        }

        return true;
    }

    public function hasCapacity(): bool
    {
        return $this->usage_limit === null || $this->used_count < $this->usage_limit;
    }

    public function usageCountFor(User $user): int
    {
        return $this->usages()->where('user_id', $user->id)->count();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
    }
}
