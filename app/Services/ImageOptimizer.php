<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageOptimizer
{
    public static function hasImageProcessingSupport(): bool
    {
        return extension_loaded('gd')
            && function_exists('imagecreatefromjpeg')
            && function_exists('imagecreatefrompng')
            && function_exists('imagecreatefromwebp')
            && function_exists('imagecreatefromgif')
            && function_exists('imagecreatetruecolor')
            && function_exists('imagecopyresampled')
            && function_exists('imagewebp')
            && function_exists('imagepng')
            && function_exists('imagejpeg');
    }

    private static array $presets = [
        'room_photo' => [
            'max_width' => 1920,
            'max_height' => 1080,
            'quality' => 80,
            'format' => 'webp',
            'thumbnails' => [
                'thumb' => ['width' => 300, 'height' => 300, 'quality' => 75],
                'medium' => ['width' => 600, 'height' => 400, 'quality' => 80],
            ],
        ],
        'city_hero' => [
            'max_width' => 1920,
            'max_height' => 800,
            'quality' => 75,
            'format' => 'webp',
            'thumbnails' => [
                'thumb' => ['width' => 400, 'height' => 200, 'quality' => 75],
            ],
        ],
        'default_hero' => [
            'max_width' => 1920,
            'max_height' => 800,
            'quality' => 75,
            'format' => 'webp',
            'thumbnails' => [],
        ],
        'logo' => [
            'max_width' => 400,
            'max_height' => 200,
            'quality' => 85,
            'format' => 'webp',
            'thumbnails' => [],
        ],
        'favicon' => [
            'max_width' => 64,
            'max_height' => 64,
            'quality' => 90,
            'format' => 'png',
            'thumbnails' => [],
        ],
        'blog_image' => [
            'max_width' => 1200,
            'max_height' => 800,
            'quality' => 80,
            'format' => 'webp',
            'thumbnails' => [
                'thumb' => ['width' => 400, 'height' => 300, 'quality' => 75],
            ],
        ],
        'offer_image' => [
            'max_width' => 1200,
            'max_height' => 630,
            'quality' => 80,
            'format' => 'webp',
            'thumbnails' => [
                'thumb' => ['width' => 400, 'height' => 210, 'quality' => 75],
            ],
        ],
        'avatar' => [
            'max_width' => 400,
            'max_height' => 400,
            'quality' => 85,
            'format' => 'webp',
            'thumbnails' => [],
        ],
        'banner' => [
            'max_width' => 1920,
            'max_height' => 600,
            'quality' => 75,
            'format' => 'webp',
            'thumbnails' => [],
        ],
        'testimonial_avatar' => [
            'max_width' => 200,
            'max_height' => 200,
            'quality' => 85,
            'format' => 'webp',
            'thumbnails' => [],
        ],
        'default' => [
            'max_width' => 1920,
            'max_height' => 1080,
            'quality' => 75,
            'format' => 'webp',
            'thumbnails' => [],
        ],
    ];

    public static function optimize(UploadedFile $file, string $preset = 'default', string $disk = 'public'): string
    {
        if (!self::hasImageProcessingSupport()) {
            throw new \RuntimeException('Image processing support is unavailable.');
        }

        $config = self::$presets[$preset] ?? self::$presets['default'];
        $maxWidth = $config['max_width'] ?? 1920;
        $maxHeight = $config['max_height'] ?? 1080;
        $quality = $config['quality'] ?? 75;
        $format = strtolower($config['format'] ?? 'webp');
        $thumbnails = $config['thumbnails'] ?? [];

        $mimeType = $file->getMimeType();

        $image = match ($mimeType) {
            'image/jpeg', 'image/jpg' => imagecreatefromjpeg($file->getRealPath()),
            'image/png' => imagecreatefrompng($file->getRealPath()),
            'image/webp' => imagecreatefromwebp($file->getRealPath()),
            'image/gif' => imagecreatefromgif($file->getRealPath()),
            default => null,
        };

        if (!$image) {
            throw new \RuntimeException('Uploaded image could not be decoded.');
        }

        $originalWidth = imagesx($image);
        $originalHeight = imagesy($image);

        if ($originalWidth <= $maxWidth && $originalHeight <= $maxHeight) {
            $mainPath = self::storeAsFormat($file, $preset, $format, $quality, $image, $originalWidth, $originalHeight, $disk);
            imagedestroy($image);
            return $mainPath;
        }

        $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight, 1);
        $newWidth = (int) round($originalWidth * $ratio);
        $newHeight = (int) round($originalHeight * $ratio);

        $resized = imagecreatetruecolor($newWidth, $newHeight);

        if ($mimeType === 'image/png') {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
            imagefill($resized, 0, 0, $transparent);
        }

        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);

        $mainPath = self::storeResizedImage($preset, $format, $quality, $resized, $newWidth, $newHeight, $disk);

        imagedestroy($image);
        imagedestroy($resized);

        if (!empty($thumbnails)) {
            foreach ($thumbnails as $thumbName => $thumbConfig) {
                self::generateThumbnail($file, $preset, $thumbName, $thumbConfig, $format, $quality, $originalWidth, $originalHeight, $disk);
            }
        }

        return $mainPath;
    }

    public static function optimizeToPublicPath(UploadedFile $file, string $preset, string $publicDirectory, int $quality = 75, string $format = 'webp'): string
    {
        if (!self::hasImageProcessingSupport()) {
            throw new \RuntimeException('Image processing support is unavailable.');
        }

        $mimeType = $file->getMimeType();

        $image = match ($mimeType) {
            'image/jpeg', 'image/jpg' => imagecreatefromjpeg($file->getRealPath()),
            'image/png' => imagecreatefrompng($file->getRealPath()),
            'image/webp' => imagecreatefromwebp($file->getRealPath()),
            'image/gif' => imagecreatefromgif($file->getRealPath()),
            default => null,
        };

        if (!$image) {
            throw new \RuntimeException('Uploaded image could not be decoded.');
        }

        $originalWidth = imagesx($image);
        $originalHeight = imagesy($image);

        $maxWidth = 1920;
        $maxHeight = 1080;
        $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight, 1);
        $newWidth = (int) round($originalWidth * $ratio);
        $newHeight = (int) round($originalHeight * $ratio);

        $resized = imagecreatetruecolor($newWidth, $newHeight);

        if ($mimeType === 'image/png') {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
            imagefill($resized, 0, 0, $transparent);
        }

        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);

        $filename = self::generateFilename($preset, $format);
        $destination = public_path($publicDirectory);

        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $fullPath = $destination . '/' . $filename;

        match ($format) {
            'webp' => imagewebp($resized, $fullPath, $quality),
            'png' => imagepng($resized, $fullPath, 8),
            'jpg', 'jpeg' => imagejpeg($resized, $fullPath, $quality),
            default => imagejpeg($resized, $fullPath, $quality),
        };

        imagedestroy($image);
        imagedestroy($resized);

        return $publicDirectory . '/' . $filename;
    }

    private static function storeAsFormat(UploadedFile $file, string $preset, string $format, int $quality, $image, int $width, int $height, string $disk = 'public'): string
    {
        $filename = self::generateFilename($preset, $format);
        $diskInstance = Storage::disk($disk);
        $path = $preset . '/' . $filename;
        $fullPath = $diskInstance->path($path);

        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        match ($format) {
            'webp' => imagewebp($image, $fullPath, $quality),
            'png' => imagepng($image, $fullPath, 8),
            'jpg', 'jpeg' => imagejpeg($image, $fullPath, $quality),
            default => imagejpeg($image, $fullPath, $quality),
        };

        return $path;
    }

    private static function storeResizedImage(string $preset, string $format, int $quality, $resized, int $width, int $height, string $disk = 'public'): string
    {
        $filename = self::generateFilename($preset, $format);
        $diskInstance = Storage::disk($disk);
        $path = $preset . '/' . $filename;
        $fullPath = $diskInstance->path($path);

        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        match ($format) {
            'webp' => imagewebp($resized, $fullPath, $quality),
            'png' => imagepng($resized, $fullPath, 8),
            'jpg', 'jpeg' => imagejpeg($resized, $fullPath, $quality),
            default => imagejpeg($resized, $fullPath, $quality),
        };

        return $path;
    }

    private static function generateThumbnail(UploadedFile $file, string $preset, string $thumbName, array $config, string $format, int $quality, int $originalWidth, int $originalHeight, string $disk = 'public'): void
    {
        if (!self::hasImageProcessingSupport()) {
            return;
        }

        $thumbWidth = $config['width'];
        $thumbHeight = $config['height'];
        $thumbQuality = $config['quality'] ?? $quality;

        $ratio = min($thumbWidth / $originalWidth, $thumbHeight / $originalHeight, 1);
        $newWidth = (int) round($originalWidth * $ratio);
        $newHeight = (int) round($originalHeight * $ratio);

        $image = match ($file->getMimeType()) {
            'image/jpeg', 'image/jpg' => imagecreatefromjpeg($file->getRealPath()),
            'image/png' => imagecreatefrompng($file->getRealPath()),
            'image/webp' => imagecreatefromwebp($file->getRealPath()),
            'image/gif' => imagecreatefromgif($file->getRealPath()),
            default => null,
        };

        if (!$image) {
            return;
        }

        $thumb = imagecreatetruecolor($newWidth, $newHeight);

        if ($file->getMimeType() === 'image/png') {
            imagealphablending($thumb, false);
            imagesavealpha($thumb, true);
            $transparent = imagecolorallocatealpha($thumb, 0, 0, 0, 127);
            imagefill($thumb, 0, 0, $transparent);
        }

        imagecopyresampled($thumb, $image, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);

        $filename = self::generateFilename($preset . '_' . $thumbName, $format);
        $diskInstance = Storage::disk($disk);
        $path = $preset . '/' . $filename;
        $fullPath = $diskInstance->path($path);

        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        match ($format) {
            'webp' => imagewebp($thumb, $fullPath, $thumbQuality),
            'png' => imagepng($thumb, $fullPath, 8),
            'jpg', 'jpeg' => imagejpeg($thumb, $fullPath, $thumbQuality),
            default => imagejpeg($thumb, $fullPath, $thumbQuality),
        };

        imagedestroy($image);
        imagedestroy($thumb);
    }

    private static function generateFilename(string $prefix, string $format): string
    {
        return $prefix . '_' . Str::random(40) . '.' . $format;
    }

    public static function getOptimizedUrl(string $path, string $preset = 'default'): string
    {
        if (empty($path) || !Storage::disk('public')->exists(ltrim($path, '/'))) {
            return asset('assets/images/default-room.svg');
        }

        return asset('storage/' . ltrim($path, '/'));
    }

    public static function getThumbnailUrl(string $basePath, string $thumbName): string
    {
        if (empty($basePath)) {
            return asset('assets/images/default-room.svg');
        }

        $pathInfo = pathinfo($basePath);
        $directory = dirname($pathInfo['filename']);
        $filename = $pathInfo['filename'] . '_' . $thumbName . '.' . $pathInfo['extension'];

        $thumbPath = $directory . '/' . $filename;

        if (Storage::disk('public')->exists(ltrim($thumbPath, '/'))) {
            return asset('storage/' . ltrim($thumbPath, '/'));
        }

        return asset('storage/' . ltrim($basePath, '/'));
    }

    public static function delete(string ...$paths): void
    {
        foreach ($paths as $path) {
            if (!empty($path) && Storage::disk('public')->exists(ltrim($path, '/'))) {
                Storage::disk('public')->delete($path);
            }
        }
    }
}
