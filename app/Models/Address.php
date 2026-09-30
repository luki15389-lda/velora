<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'label', 'recipient_name', 'phone', 'province', 'city',
        'district', 'postal_code', 'street_address', 'notes', 'is_default',
    ];

    protected function casts(): array
    {
        return ['is_default' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Skips blank parts — checkout only collects a single free-text address now. */
    public function getSummaryAttribute(): string
    {
        return collect([$this->street_address, $this->district, $this->city, trim("{$this->province} {$this->postal_code}")])
            ->map(fn ($part) => trim((string) $part))
            ->filter()
            ->implode(', ');
    }

    /** Promotes this address and demotes the rest for the same owner. */
    public function makeDefault(): void
    {
        static::where('user_id', $this->user_id)->update(['is_default' => false]);

        $this->forceFill(['is_default' => true])->save();
    }
}
