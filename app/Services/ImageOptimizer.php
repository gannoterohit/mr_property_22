<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageOptimizer
{
    public static function src(string $path, ?array $sizes = null): string
    {
        if (empty($path) || !Storage::disk('public')->exists(ltrim($path, '/'))) {
            return asset('storage/default-room.jpg');
        }

        return asset('storage/' . ltrim($path, '/'));
    }

    public static function srcset(string $path): string
    {
        if (empty($path) || !Storage::disk('public')->exists(ltrim($path, '/'))) {
            return asset('storage/default-room.jpg') . ' 1x';
        }

        $base = asset('storage/' . ltrim($path, '/'));

        return implode(', ', [
            $base . '?w=400&q=75 400w',
            $base . '?w=800&q=80 800w',
            $base . '?w=1200&q=85 1200w',
        ]);
    }

    public static function sizes(string $context = 'card'): string
    {
        return match ($context) {
            'hero'   => '(max-width: 768px) 100vw, 800px',
            'card'   => '(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw',
            'mobile' => '(max-width: 400px) 150px, 250px',
            'thumb'  => '100px',
            default  => '100vw',
        };
    }

    public static function attrs(string $path, string $context = 'card', bool $lazy = true, ?string $fallback = null): array
    {
        $src = self::src($path, fallback: $fallback);

        return [
            'src'        => $src,
            'srcset'     => self::srcset($path),
            'sizes'      => self::sizes($context),
            'loading'    => $lazy ? 'lazy' : 'eager',
            'decoding'   => 'async',
            'fetchpriority' => $lazy ? 'auto' : 'high',
            'onerror'    => "this.onerror=null;this.src='" . asset('storage/' . ($fallback ?: 'default-room.jpg')) . "'",
        ];
    }

    public static function placeholder(string $path): string
    {
        return 'data:image/svg+xml;base64,' . base64_encode(self::svgPlaceholder());
    }

    private static function svgPlaceholder(): string
    {
        return '<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 40 40">
            <rect width="40" height="40" fill="#f1f5f9"/>
            <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="#cbd5e1" font-size="10" font-family="sans-serif">Loading</text>
        </svg>';
    }
}
