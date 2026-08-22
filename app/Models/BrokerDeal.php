<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BrokerDeal extends Model
{
    protected $fillable = [
        'broker_id',
        'room_id',
        'user_id',
        'deal_value',
        'commission_rate',
        'commission_amount',
        'status',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'deal_value' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'completed_at' => 'datetime',
    ];

    public function broker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'broker_id');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
