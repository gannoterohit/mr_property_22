<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enquiry extends Model
{
    protected $fillable = [
        'user_id',
        'room_id',
        'payment_id',
        'unlocked',
        'unlocked_at',
    ];

    protected $casts = [
        'unlocked' => 'boolean',
        'unlocked_at' => 'datetime',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function room() {
        return $this->belongsTo(Room::class);
    }

    public function payment() {
        return $this->belongsTo(Payment::class);
    }
}

