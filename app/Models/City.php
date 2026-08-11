<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

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
            Cache::forget('cities.selector.active');
        });

        static::deleted(function () {
            Cache::forget('cities.selector.active');
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
            return static::resolveImageUrl($city->image_url);
        }

        return asset('assets/images/indore-hero-v2.png');
    }

    public static function resolveImageUrl(?string $value): string
    {
        if (empty($value)) {
            return asset('assets/images/indore-hero-v2.png');
        }

        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }

        $normalized = ltrim($value, '/');
        $candidates = [$normalized];

        if (str_starts_with($normalized, 'storage/')) {
            $candidates[] = substr($normalized, 8);
        } else {
            $candidates[] = 'storage/' . $normalized;
        }

        foreach ($candidates as $candidate) {
            $publicPath = public_path($candidate);
            if (file_exists($publicPath)) {
                return asset($candidate);
            }
        }

        $storagePath = storage_path('app/public/' . ltrim($normalized, '/'));
        if (file_exists($storagePath)) {
            $target = public_path('storage/' . ltrim($normalized, '/'));
            $targetDir = dirname($target);

            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            copy($storagePath, $target);

            return asset('storage/' . ltrim($normalized, '/'));
        }

        return asset('assets/images/indore-hero-v2.png');
    }
}
