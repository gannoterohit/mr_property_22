<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class HowItWorksItem extends Model
{
    public const GROUPS = [
        'hero_feature' => 'Hero Cards',
        'seeker_step' => 'Seeker Steps',
        'owner_step' => 'Owner Steps',
    ];

    protected $fillable = [
        'group',
        'title',
        'description',
        'icon',
        'badge',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
