<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BrokerLeadCharge extends Model
{
    protected $fillable = [
        'broker_id',
        'room_id',
        'user_id',
        'charge_amount',
        'status',
        'payment_id',
        'notes',
    ];

    protected $casts = [
        'charge_amount' => 'decimal:2',
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
