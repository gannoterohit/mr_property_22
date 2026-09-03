<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RejectionReason extends Model
{
    use HasFactory;
    
    protected $fillable = ['reason', 'is_active'];
    
    public function rooms()
    {
        return $this->belongsToMany(Room::class, 'room_rejection_reason');
    }
}