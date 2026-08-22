<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BrokerReview extends Model
{
    protected $fillable = [
        'broker_id',
        'user_id',
        'room_id',
        'rating',
        'comment',
        'status',
        'admin_reply',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    public function broker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'broker_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
