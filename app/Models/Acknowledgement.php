<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Acknowledgement extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_id',
        'buyer_id',
        'acknowledged_product_info',
        'acknowledged_expiry',
        'acknowledged_allergens',
        'acknowledged_storage',
        'acknowledged_collection',
        'disclaimer_accepted',
        'acknowledged_at',
    ];

    protected function casts(): array
    {
        return [
            'acknowledged_product_info' => 'boolean',
            'acknowledged_expiry' => 'boolean',
            'acknowledged_allergens' => 'boolean',
            'acknowledged_storage' => 'boolean',
            'acknowledged_collection' => 'boolean',
            'disclaimer_accepted' => 'boolean',
            'acknowledged_at' => 'datetime',
        ];
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class, 'reservation_id');
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }
}
