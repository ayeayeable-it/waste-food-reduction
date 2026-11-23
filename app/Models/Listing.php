<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Listing extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'category_id',
        'food_name',
        'description',
        'image_path',
        'original_price',
        'discounted_price',
        'available_quantity',
        'expiry_label',
        'storage_instructions',
        'allergen_info',
        'collection_deadline',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'original_price' => 'decimal:2',
            'discounted_price' => 'decimal:2',
            'available_quantity' => 'integer',
            'collection_deadline' => 'datetime',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'listing_id');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'listing_id');
    }

    public function scopeEligible(Builder $query): Builder
    {
        return $query->where('status', 'active')
            ->where('available_quantity', '>', 0)
            ->where('collection_deadline', '>', now())
            ->whereHas('store', fn (Builder $q) => $q->where('is_active', true));
    }

    public function isEligibleForReservation(): bool
    {
        return $this->status === 'active'
            && $this->available_quantity > 0
            && $this->collection_deadline->isFuture()
            && $this->store?->is_active;
    }

    public function getDiscountPercentageAttribute(): int
    {
        if ((float) $this->original_price <= 0) {
            return 0;
        }

        return (int) round((($this->original_price - $this->discounted_price) / $this->original_price) * 100);
    }
}
