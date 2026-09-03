<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BrokerSubscription extends Model
{
    protected $fillable = [
        'broker_id',
        'plan_id',
        'starts_at',
        'expires_at',
        'status',
        'max_listings',
        'listings_used',
        'amount_paid',
        'payment_id',
        'notes',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function broker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'broker_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(BrokerPlan::class);
    }

    public function getRemainingListingsAttribute(): int
    {
        return max(0, $this->max_listings - $this->listings_used);
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active' && $this->expires_at && $this->expires_at->isFuture();
    }
}
