<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_reference',
        'buyer_id',
        'listing_id',
        'quantity',
        'unit_price',
        'original_unit_price',
        'total_discounted_price',
        'original_total_price',
        'reserved_at',
        'expires_at',
        'collected_at',
        'cancelled_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'original_unit_price' => 'decimal:2',
            'total_discounted_price' => 'decimal:2',
            'original_total_price' => 'decimal:2',
            'reserved_at' => 'datetime',
            'expires_at' => 'datetime',
            'collected_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class, 'listing_id');
    }

    public function acknowledgement(): HasOne
    {
        return $this->hasOne(Acknowledgement::class, 'reservation_id');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'reservation_id');
    }

    public function getSavingsAttribute(): float
    {
        return round((float) $this->original_total_price - (float) $this->total_discounted_price, 2);
    }
}
