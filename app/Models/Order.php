<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ShippingMethod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'number', 'user_id', 'coupon_id', 'status', 'payment_status',
        'recipient_name', 'recipient_phone', 'province', 'city', 'district',
        'postal_code', 'street_address', 'customer_note',
        'shipping_method', 'tracking_number', 'courier',
        'subtotal', 'discount_total', 'shipping_total', 'grand_total', 'coupon_code',
        'paid_at', 'shipped_at', 'delivered_at', 'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'payment_status' => PaymentStatus::class,
            'shipping_method' => ShippingMethod::class,
            'subtotal' => 'integer',
            'discount_total' => 'integer',
            'shipping_total' => 'integer',
            'grand_total' => 'integer',
            'paid_at' => 'datetime',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'number';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->orderBy('created_at');
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function inventoryLogs(): HasMany
    {
        return $this->hasMany(InventoryLog::class);
    }

    /** Skips blank parts — checkout only collects a single free-text address now. */
    public function getShippingSummaryAttribute(): string
    {
        return collect([$this->street_address, $this->district, $this->city, trim("{$this->province} {$this->postal_code}")])
            ->map(fn ($part) => trim((string) $part))
            ->filter()
            ->implode(', ');
    }

    public function getItemCountAttribute(): int
    {
        return (int) $this->items->sum('quantity');
    }

    public function isCancellable(): bool
    {
        return in_array($this->status, [OrderStatus::Pending, OrderStatus::Paid], true);
    }

    /** Sequential, human-readable order number: VLR-20260101-0042. */
    public static function generateNumber(): string
    {
        $prefix = 'VLR-'.now()->format('Ymd').'-';
        $sequence = static::whereDate('created_at', today())->count() + 1;

        // Two checkouts in the same tick would land on the same sequence, so walk
        // forward until the unique index is satisfied.
        do {
            $number = $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
            $sequence++;
        } while (static::where('number', $number)->exists());

        return $number;
    }

    public function scopeStatus($query, ?string $status)
    {
        return $status ? $query->where('status', $status) : $query;
    }
}
