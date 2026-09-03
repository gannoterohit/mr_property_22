<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComplaintActivity extends Model
{
    protected $fillable = ['complaint_id', 'actor_id', 'type', 'status_from', 'status_to', 'description', 'is_internal'];
    protected $casts = ['is_internal' => 'boolean'];
    public function complaint() { return $this->belongsTo(Complaint::class); }
    public function actor() { return $this->belongsTo(User::class, 'actor_id'); }
}
