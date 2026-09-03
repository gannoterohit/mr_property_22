<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BrokerWallet extends Model
{
    protected $fillable = [
        'broker_id',
        'balance',
        'frozen_amount',
        'total_earned',
        'total_withdrawn',
        'bank_details',
        'is_verified',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'frozen_amount' => 'decimal:2',
        'total_earned' => 'decimal:2',
        'total_withdrawn' => 'decimal:2',
        'bank_details' => 'array',
        'is_verified' => 'boolean',
    ];

    public function broker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'broker_id');
    }
}
