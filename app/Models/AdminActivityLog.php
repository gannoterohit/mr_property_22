<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminActivityLog extends Model
{
    protected $fillable = ['actor_id','action','description','route_name','method','subject_type','subject_id','ip_address','user_agent','metadata'];
    protected $casts = ['metadata' => 'array'];
    public function actor() { return $this->belongsTo(User::class, 'actor_id')->withTrashed(); }
}
