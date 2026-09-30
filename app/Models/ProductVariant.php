<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'color_id', 'size_id', 'sku', 'price', 'sale_price',
        'stock', 'low_stock_threshold', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'sale_price' => 'integer',
            'stock' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class);
    }

    public function size(): BelongsTo
    {
        return $this->belongsTo(Size::class);
    }

    public function inventoryLogs(): HasMany
    {
        return $this->hasMany(InventoryLog::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /** Variant price wins when set, otherwise the product's own price applies. */
    public function getEffectivePriceAttribute(): int
    {
        if ($this->sale_price) {
            return $this->sale_price;
        }

        if ($this->price) {
            return $this->price;
        }

        return $this->product->effective_price;
    }

    public function getCompareAtPriceAttribute(): ?int
    {
        $base = $this->price ?: $this->product->price;

        return $this->effective_price < $base ? $base : null;
    }

    public function getLabelAttribute(): string
    {
        return collect([$this->color?->name, $this->size?->name])->filter()->implode(' / ') ?: 'Default';
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->stock > 0 && $this->stock <= $this->low_stock_threshold;
    }

    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock', '<=', 'low_stock_threshold');
    }
}
