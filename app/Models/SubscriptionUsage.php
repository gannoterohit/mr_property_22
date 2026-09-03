<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionUsage extends Model
{
    protected $fillable = ['subscription_id', 'user_id', 'room_id', 'usage_type', 'used_at'];
    protected $casts = ['used_at' => 'datetime'];

    public function subscription() { return $this->belongsTo(Subscription::class); }
    public function room() { return $this->belongsTo(Room::class); }
}
