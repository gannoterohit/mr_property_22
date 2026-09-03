<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminRole extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'permissions', 'is_system'];
    protected $casts = ['permissions' => 'array', 'is_system' => 'boolean'];
    public function staff() { return $this->hasMany(User::class); }
}
