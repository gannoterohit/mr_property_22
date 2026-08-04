<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class City extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'state',
        'image_url',
        'is_active',
        'is_default',
        'latitude',
        'longitude',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    protected static function booted(): void
    {
        static::saving(function (City $city) {
            if (!$city->slug) {
                $city->slug = Str::slug($city->name);
            }
        });

        static::saved(function (City $city) {
            if ($city->is_default) {
                static::whereKeyNot($city->getKey())->update(['is_default' => false]);
            }
        });
    }

    public static function findByName(?string $name): ?self
    {
        $name = trim((string) $name);
        if ($name === '') {
            return null;
        }

        return static::whereRaw('LOWER(name) = ?', [Str::lower($name)])->first();
    }

    public static function defaultCity(): ?self
    {
        return static::where('is_default', true)->first()
            ?: static::where('is_active', true)->orderBy('sort_order')->orderBy('name')->first();
    }

    public static function resolveHeroImage(?string $cityName): string
    {
        $normalizedName = trim((string) $cityName);

        if ($normalizedName === '') {
            return asset('assets/images/indore-hero-v2.png');
        }

        $city = static::whereRaw('LOWER(name) = ?', [Str::lower($normalizedName)])
            ->first() ?? static::whereRaw('LOWER(slug) = ?', [Str::slug($normalizedName)])
            ->first();

        if ($city && !empty($city->image_url)) {
            return filter_var($city->image_url, FILTER_VALIDATE_URL)
                ? $city->image_url
                : asset('storage/' . ltrim($city->image_url, '/'));
        }

        return asset('assets/images/indore-hero-v2.png');
    }
}
