<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'gateway',
        'gateway_order_id',
        'transaction_id',
        'reference_id',
        'status',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
