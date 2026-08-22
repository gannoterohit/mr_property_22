<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BrokerListingCredit extends Model
{
    protected $fillable = [
        'broker_id',
        'credits_remaining',
        'credits_purchased',
        'source',
        'type',
        'expires_at',
        'notes',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function broker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'broker_id');
    }

    public function getIsValidAttribute(): bool
    {
        return $this->credits_remaining > 0 && (!$this->expires_at || $this->expires_at->isFuture());
    }
}
