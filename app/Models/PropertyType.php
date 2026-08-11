<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PropertyType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function categories()
    {
        return $this->hasMany(PropertyCategory::class, 'property_type_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public static function cachedActive()
    {
        return Cache::remember('property-types.active', 3600, function () {
            return self::active()->orderBy('name')->get(['id', 'name', 'slug']);
        });
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('property-types.active'));
        static::deleted(fn () => Cache::forget('property-types.active'));
    }
}
