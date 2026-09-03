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
        'hero_images',
        'is_active',
        'is_default',
        'latitude',
        'longitude',
        'sort_order',
    ];

    protected $casts = [
        'hero_images' => 'array',
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

    public function getHeroImagesListAttribute(): array
    {
        $images = $this->hero_images;
        if (is_string($images)) {
            $images = json_decode($images, true);
        }
        if (is_array($images) && !empty($images)) {
            return array_values(array_filter($images));
        }
        if (!empty($this->image_url)) {
            return [$this->image_url];
        }
        return [];
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
        $images = static::resolveHeroImages($cityName);
<<<<<<< HEAD
        return $images[0] ?? '';
=======
        return $images[0] ?? asset('assets/images/hero-bg.webp');
>>>>>>> 98b94930f294609982bf4ef143712b3784a5d50a
    }

    public static function resolveHeroImages(?string $cityName): array
    {
        $images = [];
        $normalizedName = trim((string) $cityName);

        $city = null;
        if ($normalizedName !== '') {
            $city = static::whereRaw('LOWER(name) = ?', [Str::lower($normalizedName)])
                ->first() ?? static::whereRaw('LOWER(slug) = ?', [Str::slug($normalizedName)])
                ->first();
        }

        if (!$city) {
            $city = static::defaultCity();
        }

        if ($city) {
            if (!empty($city->hero_images) && is_array($city->hero_images)) {
                foreach ($city->hero_images as $img) {
                    if (!empty($img)) {
                        $url = static::resolveImageUrl($img);
                        if (!in_array($url, $images, true)) {
                            $images[] = $url;
                        }
                    }
                }
            }
            if (empty($images) && !empty($city->image_url)) {
                $images[] = static::resolveImageUrl($city->image_url);
            }
        }

<<<<<<< HEAD
=======
        if (empty($images)) {
            $images[] = asset('assets/images/hero-bg.webp');
        }

>>>>>>> 98b94930f294609982bf4ef143712b3784a5d50a
        return array_values(array_unique($images));
    }

    public static function resolveImageUrl(?string $value): string
    {
        if (empty($value)) {
<<<<<<< HEAD
            return '';
=======
            return asset('assets/images/hero-bg.webp');
>>>>>>> 98b94930f294609982bf4ef143712b3784a5d50a
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

<<<<<<< HEAD
        return '';
=======
        return asset('assets/images/hero-bg.webp');
>>>>>>> 98b94930f294609982bf4ef143712b3784a5d50a
    }
}
