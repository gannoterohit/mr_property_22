<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BrokerRanking extends Model
{
    protected $fillable = [
        'broker_id',
        'score',
        'total_deals',
        'total_deal_value',
        'total_reviews',
        'average_rating',
        'response_time_minutes',
        'rank',
        'badges',
        'calculated_at',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'total_deal_value' => 'decimal:2',
        'average_rating' => 'decimal:2',
        'badges' => 'array',
        'calculated_at' => 'datetime',
    ];

    public function broker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'broker_id');
    }
}
